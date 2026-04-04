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

    public static function getCachedLoadMoreChunk(int $offset, int $posts_per_page = 3): array
    {
        $version = CacheHelper::getDataVersion('content_list');
        $key     = "loadmore_offset_{$offset}_pp{$posts_per_page}_v{$version}";

        return DataCache::remember($key, 1800, function () use ($offset, $posts_per_page) {
            $total_start = microtime(true);

            // === 1. QUERY ===
            $query_start = microtime(true);
            $posts = get_posts([
                'post_type'              => ['post', 'event'],
                'posts_per_page'         => $posts_per_page,
                'offset'                 => $offset,
                'orderby'                => 'date',
                'order'                  => 'DESC',
                'post_status'            => 'publish',
                'no_found_rows'          => true,
                'cache_results'          => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'suppress_filters'       => false,
            ]);
            $query_time = round((microtime(true) - $query_start) * 1000, 2);

            // === 2. BULK PREFETCH SIÊU MẠNH (quan trọng nhất) ===
            $prefetch_start = microtime(true);
            if (!empty($posts)) {
                $ids = wp_list_pluck($posts, 'ID');

                update_postmeta_cache($ids);                    // WP meta
                _prime_post_caches($ids, false, true);          // permalink + title
                sage_prefetch_link_posts($posts);               // redirect + link helper

                // === Prefetch CustomTableManager + cmeta (giảm 80% overhead) ===
                foreach ($ids as $id) {
                    cmeta('custom_author', $id);
                    cmeta('flags', $id);
                    cmeta('is_redirect', $id);
                    cmeta('redirect_url', $id);
                    cmeta('is_pinned', $id);   // nếu dùng
                }
            }
            $prefetch_time = round((microtime(true) - $prefetch_start) * 1000, 2);

            // === 3. RENDER BLADE (giữ nguyên tất cả helper) ===
            $render_start = microtime(true);
            $html = '';
            if (!empty($posts)) {
                global $post;
                ob_start();
                foreach ($posts as $post) {
                    setup_postdata($post);
                    $html .= view('partials.content-loadmore')->render();
                }
                wp_reset_postdata();
            }
            $render_time = round((microtime(true) - $render_start) * 1000, 2);

            // === 4. has_more ===
            $has_more = !empty(get_posts([
                'post_type'      => ['post', 'event'],
                'posts_per_page' => 1,
                'offset'         => $offset + $posts_per_page,
                'fields'         => 'ids',
                'no_found_rows'  => true,
            ]));

            $total_time = round((microtime(true) - $total_start) * 1000, 2);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[LOADMORE 10/10] offset={$offset} | Query:{$query_time}ms | Prefetch:{$prefetch_time}ms | Render:{$render_time}ms | Tổng:{$total_time}ms");
            }

            return ['html' => $html, 'has_more' => $has_more];
        });
    }
}