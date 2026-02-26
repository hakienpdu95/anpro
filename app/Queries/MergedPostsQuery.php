<?php
namespace App\Queries;

use WP_Query;
use App\Helpers\DataCache;
use App\Helpers\CacheHelper;
use App\Database\CustomTableManager;
use App\Helpers\QueryHelper;

class MergedPostsQuery
{
    private static bool $initialized = false;
    private static array $configs = [];
    private static array $requestCache = [];

    /** ====================== INIT ====================== */
    public static function initHomepage(array $config = []): void
    {
        self::init('homepage', $config + ['post_types' => ['post', 'event']]);
    }

    public static function initArchive(string $post_type, array $config = []): void
    {
        self::init("archive_{$post_type}", $config + ['post_types' => [$post_type]]);
    }

    private static function init(string $context, array $config): void
    {
        if (isset(self::$configs[$context])) return;

        self::$configs[$context] = wp_parse_args($config, [
            'post_types'     => ['post'],
            'posts_per_page' => ($context === 'homepage') ? 1 : 12,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        add_action('pre_get_posts', fn($q) => self::modifyMainQuery($q, $context), 2);
        add_filter('redirect_canonical', fn($r, $u) => self::blockCanonical($r, $u, $context), 10, 2);

        add_action('save_post', [self::class, 'flushCache'], 999, 2);
        add_action('delete_post', [self::class, 'flushCache'], 999);

        self::$initialized = true;
    }

    private static function modifyMainQuery(WP_Query $query, string $context): void
    {
        $cfg = self::$configs[$context] ?? null;
        if (!$cfg || is_admin() || !$query->is_main_query()) return;

        $is_home = ($context === 'homepage');
        if ($is_home && !(is_home() || is_front_page())) return;
        if (!$is_home && !is_post_type_archive($cfg['post_types'][0] ?? '')) return;

        $query->set('post_type', $cfg['post_types']);
        $query->set('posts_per_page', $cfg['posts_per_page']);
        $query->set('orderby', $cfg['orderby']);
        $query->set('order', $cfg['order']);
        $query->set('post_status', 'publish');
        $query->set('no_found_rows', false);
        $query->set('suppress_filters', false);
        $query->set('update_post_meta_cache', false);
        $query->set('update_post_term_cache', false);
    }

    private static function blockCanonical($redirect_url, $requested_url, string $context)
    {
        if (strpos($requested_url, '/page/') === false) return $redirect_url;
        if ($context === 'homepage' && (is_home() || is_front_page())) return false;
        if (is_post_type_archive(self::$configs[$context]['post_types'][0] ?? '')) return false;
        return $redirect_url;
    }

    /** ====================== QUERY CHÍNH ====================== */
    public static function get(array $config = []): WP_Query
    {
        $cacheKey = md5(serialize($config));
        if (isset(self::$requestCache[$cacheKey])) {
            return self::$requestCache[$cacheKey];
        }

        $default = [
            'post_types'     => ['post', 'event'],
            'posts_per_page' => 6,
            'paged'          => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => [],
            'meta_query'     => [],
            'no_found_rows'  => true,
            'use_cache'      => true,
            'cache_duration' => 10 * MINUTE_IN_SECONDS,
        ];

        $config = wp_parse_args($config, $default);

        $query = self::hasFlagsMetaQuery($config['meta_query'])
            ? self::getWithFlags($config)
            : ($config['use_cache']
                ? DataCache::remember(self::generateCacheKey($config), $config['cache_duration'], fn() => self::executeRawQuery($config))
                : self::executeRawQuery($config));

        if ($query->have_posts()) {
            CustomTableManager::preloadThePostsMeta($query->posts, $query);
        }

        return self::$requestCache[$cacheKey] = $query;
    }

    /** ====================== FLAGS – SQL UNION + FIX WARNING ====================== */
    private static function getWithFlags(array $config): WP_Query
    {
        $post_types = (array)$config['post_types'];
        $flags      = [];
        foreach ((array)$config['meta_query'] as $q) {
            if (isset($q['key']) && $q['key'] === 'flags') {
                $flags = (array)($q['value'] ?? []);
                break;
            }
        }
        if (empty($flags)) return new WP_Query();

        global $wpdb;
        $limit = (int)$config['posts_per_page'];
        $sqls  = [];

        foreach ($post_types as $pt) {
            if (!in_array($pt, CustomTableManager::$registered ?? [])) continue;
            $table = CustomTableManager::getTableName($pt);
            $placeholders = str_repeat('%s,', count($flags) - 1) . '%s';
            $sqls[] = $wpdb->prepare(
                "SELECT p.* FROM {$wpdb->posts} p 
                 INNER JOIN {$table} m ON p.ID = m.post_id 
                 WHERE p.post_type = %s 
                   AND p.post_status = 'publish' 
                   AND m.meta_key = 'flags' 
                   AND m.meta_value IN ($placeholders)
                 GROUP BY p.ID",
                array_merge([$pt], $flags)
            );
        }

        if (empty($sqls)) return new WP_Query();

        $union = implode(' UNION ALL ', $sqls);
        $sql   = "SELECT * FROM ($union) AS u ORDER BY post_date DESC LIMIT %d";
        $posts = $wpdb->get_results($wpdb->prepare($sql, $limit));

        // ==================== FIX WARNING ====================
        $query = new WP_Query();
        $query->query_vars = [
            'post_type'              => $post_types,
            'posts_per_page'         => $config['posts_per_page'],
            'no_found_rows'          => true,
            'suppress_filters'       => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'fields'                 => 'all',
        ];
        $query->query         = $query->query_vars;
        $query->posts         = $posts;
        $query->post_count    = count($posts);
        $query->found_posts   = count($posts);
        $query->max_num_pages = 1;
        $query->is_home       = false;
        $query->is_archive    = false;
        // ====================================================

        if ($posts) {
            CustomTableManager::preloadThePostsMeta($posts, $query);
        }

        return $query;
    }

    private static function executeRawQuery(array $config): WP_Query
    {
        $args = [
            'post_type'              => $config['post_types'],
            'posts_per_page'         => $config['posts_per_page'],
            'paged'                  => $config['paged'],
            'orderby'                => $config['orderby'],
            'order'                  => $config['order'],
            'post_status'            => 'publish',
            'no_found_rows'          => $config['no_found_rows'],
            'suppress_filters'       => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'fields'                 => 'all',               // ← Fix warning
        ];

        if (!empty($config['tax_query']))  $args['tax_query']  = $config['tax_query'];
        if (!empty($config['meta_query'])) $args['meta_query'] = $config['meta_query'];

        return CustomTableManager::query($args);
    }

    private static function generateCacheKey(array $config): string
    {
        $version = CacheHelper::getDataVersion('merged_posts') ?? 1;
        $key = [
            'pt'  => implode(',', (array)$config['post_types']),
            'ppp' => $config['posts_per_page'],
            'p'   => $config['paged'],
            'o'   => $config['orderby'] . $config['order'],
            't'   => md5(serialize($config['tax_query'] ?? [])),
            'm'   => md5(serialize($config['meta_query'] ?? [])),
        ];
        return 'mpq_' . md5(serialize($key)) . '_v' . $version;
    }

    private static function hasFlagsMetaQuery($meta_query): bool
    {
        if (empty($meta_query)) return false;
        foreach ((array)$meta_query as $q) {
            if (isset($q['key']) && $q['key'] === 'flags') return true;
            if (is_array($q) && self::hasFlagsMetaQuery($q)) return true;
        }
        return false;
    }

    public static function flushCache($post_id, $post = null): void
    {
        $post = $post ?: get_post($post_id);
        if (!$post) return;

        foreach (self::$configs as $cfg) {
            if (in_array($post->post_type, (array)$cfg['post_types'])) {
                CacheHelper::bumpDataVersion('merged_posts');
                self::$requestCache = [];
                return;
            }
        }
    }

    /** ====================== HELPER ====================== */
    public static function latest(int $limit = 6, array $post_types = ['post', 'event']): WP_Query
    {
        return self::get(['post_types' => $post_types, 'posts_per_page' => $limit, 'orderby' => 'date', 'order' => 'DESC']);
    }

    public static function breaking(int $limit = 6, array $post_types = ['post', 'event']): WP_Query
    {
        return self::get(['post_types' => $post_types, 'posts_per_page' => $limit, 'meta_query' => [['key' => 'flags', 'value' => ['breaking'], 'compare' => 'IN']]]);
    }

    public static function hot(int $limit = 5, array $post_types = ['post', 'event']): WP_Query
    {
        return self::get(['post_types' => $post_types, 'posts_per_page' => $limit, 'meta_query' => [['key' => 'flags', 'value' => ['hot'], 'compare' => 'IN']]]);
    }

    public static function featured(int $limit = 5, array $post_types = ['post', 'event']): WP_Query
    {
        return self::get(['post_types' => $post_types, 'posts_per_page' => $limit, 'meta_query' => [['key' => 'flags', 'value' => ['featured'], 'compare' => 'IN']]]);
    }
}