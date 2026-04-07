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

    public static function getLoadMoreChunk(int $offset, int $posts_per_page = 3): array
    {
        $total_start = microtime(true);

        $post_types = ['post', 'event', 'viet-heritage', 'viet-product', 'viet-travel'];

        $query_start = microtime(true);
        $query = new \WP_Query([
            'post_type'              => $post_types,
            'posts_per_page'         => $posts_per_page + 1,  
            'offset'                 => $offset,
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'post_status'            => 'publish',
            'no_found_rows'          => true,
            'cache_results'          => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'suppress_filters'       => false,
            'ignore_sticky_posts'    => true,
            'lazy_load_term_meta'    => false,
        ]);

        $all_posts = $query->posts;
        $has_more  = count($all_posts) > $posts_per_page;

        // Chỉ render đúng 3 bài (bỏ bài thừa nếu có)
        $posts = $has_more ? array_slice($all_posts, 0, $posts_per_page) : $all_posts;
        $query_time = round((microtime(true) - $query_start) * 1000, 2);

        // === 2. PREFETCH (giữ nguyên) ===
        $prefetch_start = microtime(true);
        if (!empty($posts)) {
            $ids = wp_list_pluck($posts, 'ID');
            update_postmeta_cache($ids);
            _prime_post_caches($ids, false, true);
            sage_prefetch_link_posts($posts);

            foreach ($ids as $id) {
                get_post_meta($id, '_thumbnail_id', true);
                \App\Placeholders\PlaceholderHandler::getUrl($id);
                cmeta('custom_author', $id);
                cmeta('flags', $id);
                cmeta('is_redirect', $id);
                cmeta('redirect_url', $id);
            }
        }
        $prefetch_time = round((microtime(true) - $prefetch_start) * 1000, 2);

        // === 3. RENDER BLADE ===
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

        $total_time = round((microtime(true) - $total_start) * 1000, 2);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[LOADMORE FINAL 10/10] offset={$offset} | Rendered " . count($posts) . " posts | has_more=" . ($has_more ? 'true' : 'false') . " | Tổng {$total_time}ms");
        }

        return [
            'html'      => $html,
            'has_more'  => $has_more,
        ];
    }
}