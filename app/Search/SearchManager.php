<?php
namespace App\Search;

use App\Database\CustomTableManager;

class SearchManager {

    public static function init(): void {
        add_action('pre_get_posts', [self::class, 'optimizeSearchQuery'], 5);
        add_filter('posts_join', [self::class, 'joinCustomMeta'], 999, 2);
        add_filter('posts_search', [self::class, 'buildOptimizedSearch'], 999, 2);
        add_filter('posts_orderby', [self::class, 'relevanceOrderBy'], 999, 2);
        add_filter('posts_clauses', [self::class, 'addGroupByToSearch'], 999, 2);
        add_filter('posts_distinct', [self::class, 'searchDistinct'], 999, 2);   // ← Thêm an toàn duplicate

        add_action('the_posts', [self::class, 'preloadSearchMeta'], 10, 2);

        // TẮT CACHE HOÀN TOÀN CHO SEARCH
        add_action('template_redirect', [self::class, 'disableCacheForSearch']);
    }

    public static function disableCacheForSearch(): void {
        if (is_search()) {
            nocache_headers();
        }
    }

    public static function optimizeSearchQuery(\WP_Query $query): void {
        if (!$query->is_search() || !$query->is_main_query() || is_admin()) return;

        $query->set('post_type', ['post', 'event', 'happy-family', 'family-values', 'violence-prevention']);
        $query->set('posts_per_page', 12);
        $query->set('no_found_rows', false);
        $query->set('update_post_meta_cache', false);
        $query->set('update_post_term_cache', false);
        $query->set('suppress_filters', false);
        $query->set('cache_results', false);           // Tắt cache WP
    }

    public static function joinCustomMeta(string $join, \WP_Query $query): string {
        if (!$query->is_search() || is_admin()) return $join;

        global $wpdb;
        $post_type = $query->get('post_type');
        if (is_array($post_type)) $post_type = $post_type[0] ?? 'post';

        $meta_table = CustomTableManager::getTableName($post_type);

        if (strpos($join, $meta_table) === false) {
            $join .= " LEFT JOIN `{$meta_table}` AS search_meta ON ({$wpdb->posts}.ID = search_meta.post_id) ";
        }
        return $join;
    }

    public static function buildOptimizedSearch(string $search, \WP_Query $query): string {
        if (!$query->is_search() || empty($query->query_vars['s']) || is_admin()) return $search;

        global $wpdb;
        $term = trim($query->query_vars['s']);
        $like = '%' . $wpdb->esc_like($term) . '%';

        return $wpdb->prepare(
            " AND (
                {$wpdb->posts}.post_title LIKE %s OR
                {$wpdb->posts}.post_content LIKE %s OR
                {$wpdb->posts}.post_excerpt LIKE %s OR
                (search_meta.meta_key IN ('subtitle','lead') AND search_meta.meta_value LIKE %s) OR
                (search_meta.meta_key = 'flags' AND search_meta.meta_value LIKE %s)
            ) ",
            $like, $like, $like, $like, $like
        );
    }

    public static function relevanceOrderBy(string $orderby, \WP_Query $query): string {
        if (!$query->is_search() || is_admin()) return $orderby;

        global $wpdb;
        $term = trim($query->query_vars['s']);
        $like1 = '%' . $wpdb->esc_like($term) . '%';
        $like2 = '%' . $wpdb->esc_like($term);

        return $wpdb->prepare(
            "(CASE 
                WHEN {$wpdb->posts}.post_title LIKE %s THEN 1 
                WHEN {$wpdb->posts}.post_title LIKE %s THEN 2 
                ELSE 3 
            END), {$wpdb->posts}.post_date DESC",
            $like1, $like2
        );
    }

    public static function addGroupByToSearch(array $clauses, \WP_Query $query): array {
        if ($query->is_search() && !is_admin()) {
            global $wpdb;
            $clauses['groupby'] = "{$wpdb->posts}.ID";
        }
        return $clauses;
    }

    public static function searchDistinct(string $distinct, \WP_Query $query): string {
        return $query->is_search() ? 'DISTINCT' : $distinct;
    }

    public static function preloadSearchMeta(array $posts, \WP_Query $query): array {
        if (!$query->is_search() || empty($posts)) return $posts;

        foreach ($posts as $post) {
            if (in_array($post->post_type, CustomTableManager::$registered ?? [])) {
                CustomTableManager::getMeta($post->ID, '');
            }
        }
        return $posts;
    }

    public static function getQueryTime(): float {
        return round(timer_stop(0, 5), 2);
    }
}