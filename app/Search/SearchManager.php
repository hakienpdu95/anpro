<?php
namespace App\Search;

use App\Database\CustomTableManager;

class SearchManager {

    public static function init(): void {
        add_action('pre_get_posts', [self::class, 'optimizeSearchQuery'], 5);
        add_filter('posts_join', [self::class, 'joinCustomMetaForSearch'], 999, 2);
        add_filter('posts_where', [self::class, 'extendSearchWhere'], 999, 2);
        add_filter('posts_distinct', [self::class, 'searchDistinct'], 10, 2);
    }

    public static function optimizeSearchQuery(\WP_Query $query): void {
        if (!$query->is_search() || !$query->is_main_query() || is_admin()) return;

        $query->set('post_type', ['post', 'event']);
        $query->set('posts_per_page', 12);
        $query->set('no_found_rows', false);
        $query->set('update_post_meta_cache', false);
        $query->set('update_post_term_cache', false);
        $query->set('suppress_filters', false);
    }

    public static function joinCustomMetaForSearch(string $join, \WP_Query $query): string {
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

    public static function extendSearchWhere(string $where, \WP_Query $query): string {
        if (!$query->is_search() || empty($query->query_vars['s']) || is_admin()) return $where;

        global $wpdb;
        $search_term = trim($query->query_vars['s']);
        $like = '%' . $wpdb->esc_like($search_term) . '%';

        $meta_conditions = $wpdb->prepare(
            "(search_meta.meta_key IN ('subtitle', 'lead') AND search_meta.meta_value LIKE %s) " .
            "OR (search_meta.meta_key = 'flags' AND search_meta.meta_value LIKE %s)",
            $like, $like
        );

        $new_where = $wpdb->prepare(
            "({$wpdb->posts}.post_title LIKE %s " .
            "OR {$wpdb->posts}.post_content LIKE %s " .
            "OR {$wpdb->posts}.post_excerpt LIKE %s " .
            "OR {$meta_conditions})",
            $like, $like, $like
        );

        $where = preg_replace(
            '/\(\s*post_title LIKE .+?\s*\)/',
            $new_where,
            $where
        );

        return $where;
    }

    public static function searchDistinct(string $distinct, \WP_Query $query): string {
        return $query->is_search() ? 'DISTINCT' : $distinct;
    }

    /** 
     * LẤY THỜI GIAN XỬ LÝ QUERY – AN TOÀN & CHÍNH XÁC 
     */
    public static function getQueryTime(): float {
        return round(timer_stop(0, 5), 2);
    }
}