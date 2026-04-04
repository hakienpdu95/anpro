<?php

namespace App\Helpers;

class QueryCache
{
    private static bool $debug = false;

    public static function init(): void
    {
        self::$debug = defined('WP_DEBUG') && WP_DEBUG;
        if (self::$debug) error_log('🚀 [QueryCache 11/10] Initialized');
    }

    public static function getPostsWithAllFlags(string $post_type, array $flags, int $posts_per_page = 8, int $ttl = 300)
    {
        $start = microtime(true);
        $isHome = is_home() || is_front_page();
        $context = $isHome ? 'home' : get_query_var('paged', 1);

        ksort($flags);
        $flagsHash = md5(json_encode($flags) . $posts_per_page . $context);

        // === TỰ ĐỘNG LẤY VERSION - KHÔNG HARD CODE ===
        $version = CacheHelper::getDataVersion($post_type);

        $key = "getPostsWithAllFlags_{$post_type}_v{$version}_{$flagsHash}";

        $result = DataCache::remember($key, $ttl, function () use ($post_type, $flags, $posts_per_page) {
            return QueryHelper::getPostsWithAllFlags($post_type, $flags, $posts_per_page);
        });

        $time = round((microtime(true) - $start) * 1000, 2);
        if (self::$debug) {
            error_log("🔍 [QUERY CACHE] getPostsWithAllFlags | {$time}ms | v{$version} | PT:{$post_type}");
        }
        return $result;
    }

    /**
     * Cache cho query nâng cao (tự động bump khi save post)
     */
    public static function getCachedAdvancedPosts(string $cache_suffix, array $config, int $ttl = 300): array
    {
        $start = microtime(true);
        $post_type = $config['post_type'] ?? 'post';
        $version   = CacheHelper::getDataVersion($post_type);

        $key = "advanced_{$cache_suffix}_v{$version}_" . md5(json_encode($config));

        $result = DataCache::remember($key, $ttl, function () use ($config) {
            return QueryHelper::getAdvancedPosts($config);
        });

        $time = round((microtime(true) - $start) * 1000, 2);
        if (self::$debug) {
            error_log("[QUERY CACHE] getCachedAdvancedPosts | {$time}ms | v{$version} | {$cache_suffix}");
        }
        return $result;
    }    

    public static function getCachedLoadMoreChunk(int $paged, int $posts_per_page = 3): array
    {
        $version = CacheHelper::getDataVersion('content_list');
        $key     = "loadmore_p{$paged}_pp{$posts_per_page}_v{$version}";

        return DataCache::remember($key, 3600, function () use ($paged, $posts_per_page) { // 1 giờ
            $start = microtime(true);

            $query = new \WP_Query([
                'post_type'              => ['post', 'event'],
                'posts_per_page'         => $posts_per_page,
                'paged'                  => $paged,
                'orderby'                => 'date',
                'order'                  => 'DESC',
                'post_status'            => 'publish',
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'cache_results'          => false,
                'ignore_sticky_posts'    => true,
                'suppress_filters'       => false,   // giữ CustomTableManager
            ]);

            $html = '';
            if ($query->have_posts()) {
                ob_start();
                while ($query->have_posts()) {
                    $query->the_post();
                    echo view('partials.content')->render();
                }
                wp_reset_postdata();
                $html = ob_get_clean();
            }

            // Test nhanh có còn trang sau không (rất nhẹ, chỉ fields=ids)
            $test_query = new \WP_Query([
                'post_type'      => ['post', 'event'],
                'posts_per_page' => 1,
                'paged'          => $paged + 1,
                'fields'         => 'ids',
                'no_found_rows'  => true,
                'cache_results'  => false,
            ]);

            $has_more = $test_query->have_posts();

            $time = round((microtime(true) - $start) * 1000, 2);
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[LOADMORE CACHE] p{$paged} | {$time}ms | has_more: " . ($has_more ? 'true' : 'false'));
            }

            return [
                'html'     => $html,
                'has_more' => $has_more,
            ];
        });
    }
}