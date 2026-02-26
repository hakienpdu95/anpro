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

    public static function initHomepage(array $config = []): void
    {
        if (self::$initialized) return;

        self::$homepageConfig = wp_parse_args($config, [
            'post_types'     => ['post', 'event'],
            'posts_per_page' => 1,           // ← Thay số bài mỗi trang ở đây
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        add_action('pre_get_posts', [self::class, 'modifyHomepageMainQuery'], 2);
        add_filter('redirect_canonical', [self::class, 'blockCanonicalRedirect'], 10, 2);

        add_action('save_post', [self::class, 'flushCache'], 999, 2);
        add_action('delete_post', [self::class, 'flushCache'], 999);

        self::$initialized = true;
    }

    public static function modifyHomepageMainQuery(WP_Query $query): void
    {
        if (is_admin() || !$query->is_main_query() || !(is_home() || is_front_page())) {
            return;
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

        $query = apply_filters('sage_merged_posts_query', $query, get_query_var('paged', 1));
    }

    public static function blockCanonicalRedirect($redirect_url, $requested_url)
    {
        if ((is_home() || is_front_page()) && strpos($requested_url, '/page/') !== false) {
            return false;   // Block redirect /page/2/ → /
        }
        return $redirect_url;
    }

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
        if ($post && in_array($post->post_type, self::$homepageConfig['post_types'] ?? ['post', 'event'])) {
            CacheHelper::bumpDataVersion('merged_posts');
        }
    }
}