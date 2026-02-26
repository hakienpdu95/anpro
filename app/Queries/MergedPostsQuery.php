<?php
namespace App\Queries;

use WP_Query;
use App\Helpers\DataCache;
use App\Helpers\CacheHelper;
use App\Database\CustomTableManager;

class MergedPostsQuery
{
    private static bool $initialized = false;
    private static array $configs = [];

    /** ====================== HOMEPAGE (merge post + event) ====================== */
    public static function initHomepage(array $config = []): void
    {
        self::init('homepage', $config + ['post_types' => ['post', 'event']]);
    }

    /** ====================== ARCHIVE CPT (event, project, ...) ====================== */
    public static function initArchive(string $post_type, array $config = []): void
    {
        self::init("archive_{$post_type}", $config + ['post_types' => [$post_type]]);
    }

    /** ====================== CORE INIT (chung cho homepage + archive) ====================== */
    private static function init(string $context, array $config): void
    {
        if (isset(self::$configs[$context])) return;

        self::$configs[$context] = wp_parse_args($config, [
            'post_types'     => ['post'],
            'posts_per_page' => ($context === 'homepage') ? 1 : 12,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        add_action('pre_get_posts', fn($query) => self::modifyMainQuery($query, $context), 2);
        add_filter('redirect_canonical', fn($r, $u) => self::blockCanonicalRedirect($r, $u, $context), 10, 2);

        // Flush cache thông minh
        add_action('save_post', [self::class, 'flushCache'], 999, 2);
        add_action('delete_post', [self::class, 'flushCache'], 999);

        self::$initialized = true;
    }

    private static function modifyMainQuery(WP_Query $query, string $context): void
    {
        $cfg = self::$configs[$context] ?? null;
        if (!$cfg) return;

        $is_homepage = ($context === 'homepage');
        $post_type   = $cfg['post_types'][0] ?? 'post';

        if (is_admin() || !$query->is_main_query()) return;

        if ($is_homepage && !(is_home() || is_front_page())) return;
        if (!$is_homepage && !is_post_type_archive($post_type)) return;

        $paged = max(1, (int) get_query_var('paged', 1));

        $query->set('post_type', $cfg['post_types']);
        $query->set('posts_per_page', $cfg['posts_per_page']);
        $query->set('orderby', $cfg['orderby']);
        $query->set('order', $cfg['order']);
        $query->set('post_status', 'publish');
        $query->set('no_found_rows', false);
        $query->set('suppress_filters', false);
        $query->set('update_post_meta_cache', false);
        $query->set('update_post_term_cache', false);

        $query = apply_filters("sage_merged_posts_query_{$context}", $query, $paged);
    }

    private static function blockCanonicalRedirect($redirect_url, $requested_url, string $context)
    {
        $is_homepage = ($context === 'homepage');
        $post_type   = self::$configs[$context]['post_types'][0] ?? '';

        if ($is_homepage && (is_home() || is_front_page()) && strpos($requested_url, '/page/') !== false) {
            return false;
        }

        if (!$is_homepage && is_post_type_archive($post_type) && strpos($requested_url, '/page/') !== false) {
            return false;
        }

        return $redirect_url;
    }

    /** ====================== REUSABLE QUERY (dùng ở bất kỳ đâu) ====================== */
    public static function get(array $config = []): WP_Query
    {
        $default = [
            'post_types'     => ['post', 'event'],
            'posts_per_page' => 12,
            'paged'          => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => [],
            'meta_query'     => [],
            'use_cache'      => true,
            'cache_duration' => 20 * MINUTE_IN_SECONDS,
        ];

        $config = wp_parse_args($config, $default);
        $cacheKey = self::generateCacheKey($config);

        if (!$config['use_cache']) {
            return self::executeRawQuery($config);
        }

        return DataCache::remember($cacheKey, $config['cache_duration'], fn() => self::executeRawQuery($config));
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
            'no_found_rows'          => false,
            'suppress_filters'       => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
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

    public static function flushCache($post_id, $post = null): void
    {
        $post = $post ?: get_post($post_id);
        if (!$post) return;

        foreach (self::$configs as $cfg) {
            if (in_array($post->post_type, (array)$cfg['post_types'])) {
                CacheHelper::bumpDataVersion('merged_posts');
                return;
            }
        }
    }
}