<?php
namespace App\Queries;

use WP_Query;
use App\Helpers\DataCache;
use App\Helpers\CacheHelper;
use App\Database\CustomTableManager;

class MergedPostsQuery
{
    private static bool $initialized = false;
    private static array $homepageConfig = [];

    /** ====================== HOMEPAGE SETUP (Main Query) ====================== */
    public static function initHomepage(array $config = []): void
    {
        if (self::$initialized) {
            return;
        }

        self::$homepageConfig = wp_parse_args($config, [
            'post_types'     => ['post', 'event'],
            'posts_per_page' => 1,           // Thay số bài/trang tại đây
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        add_action('pre_get_posts', [self::class, 'modifyHomepageMainQuery'], 2);
        add_filter('redirect_canonical', [self::class, 'smartBlockCanonical'], 10, 2);

        // Flush cache thông minh
        add_action('save_post', [self::class, 'flushCache'], 999, 2);
        add_action('delete_post', [self::class, 'flushCache'], 999);

        self::$initialized = true;
    }

    public static function modifyHomepageMainQuery(WP_Query $query): void
    {
        // Early return tối ưu hiệu suất (không chạy thừa)
        if (is_admin() || !$query->is_main_query() || !(is_home() || is_front_page())) {
            return;
        }

        $paged = max(1, (int) get_query_var('paged', 1));

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[MergedPostsQuery] Homepage main query | paged={$paged}");
        }

        $query->set('post_type', self::$homepageConfig['post_types']);
        $query->set('posts_per_page', self::$homepageConfig['posts_per_page']);
        $query->set('orderby', self::$homepageConfig['orderby']);
        $query->set('order', self::$homepageConfig['order']);
        $query->set('post_status', 'publish');
        $query->set('no_found_rows', false);
        $query->set('suppress_filters', false);
        $query->set('update_post_meta_cache', false);
        $query->set('update_post_term_cache', false);

        // Cho phép tùy chỉnh từ ngoài (extensible 10/10)
        $query = apply_filters('sage_merged_posts_query', $query, $paged);
    }

    /** ====================== BLOCK CANONICAL THÔNG MINH ====================== */
    public static function smartBlockCanonical($redirect_url, $requested_url)
    {
        if (!(is_home() || is_front_page())) {
            return $redirect_url;
        }

        if (preg_match('#/page/(\d+)/?$#', $requested_url, $matches)) {
            $page = (int) $matches[1];
            global $wp_query;
            if ($page > 1 && $page <= ($wp_query->max_num_pages ?? 1)) {
                if (WP_DEBUG) {
                    error_log("[MergedPostsQuery] Blocked canonical for valid page {$page}");
                }
                return false;
            }
        }
        return $redirect_url;
    }

    /** ====================== REUSABLE QUERY (cho archive, sidebar, related...) ====================== */
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

        return DataCache::remember($cacheKey, $config['cache_duration'], function () use ($config) {
            return self::executeRawQuery($config);
        });
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
            'pt'  => implode(',', (array) $config['post_types']),
            'ppp' => $config['posts_per_page'],
            'p'   => $config['paged'],
            'o'   => $config['orderby'] . $config['order'],
            't'   => md5(serialize($config['tax_query'] ?? [])),
            'm'   => md5(serialize($config['meta_query'] ?? [])),
        ];
        return 'mpq_' . md5(serialize($key)) . '_v' . $version;
    }

    /** ====================== FLUSH CACHE THÔNG MINH ====================== */
    public static function flushCache($post_id, $post = null): void
    {
        if ($post && in_array($post->post_type, self::$homepageConfig['post_types'] ?? ['post', 'event'])) {
            CacheHelper::bumpDataVersion('merged_posts');
        }
    }
}