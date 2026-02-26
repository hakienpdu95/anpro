<?php

namespace App\Helpers;

use App\Database\CustomTableManager;

class QueryHelper
{
    /**
     * Query helper chính – có cache transient (Redis sẽ tự dùng nếu có)
     */
    public static function cquery($args = [])
    {
        $default = [
            'post_type'      => 'event',
            'posts_per_page' => 10,
            'no_found_rows'  => true,           // tiết kiệm query count
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ];

        $args = wp_parse_args($args, $default);
        $cache_key = 'cquery_' . md5(serialize($args));

        if ($cached = get_transient($cache_key)) {
            return $cached;
        }

        $query = new \WP_Query($args);
        set_transient($cache_key, $query, 15 * MINUTE_IN_SECONDS); // cache 15 phút

        return $query;
    }

    // Bài mới nhất
    public static function get_latest_news($limit = 12)
    {
        return self::cquery([
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
    }

    // Bài nổi bật (dùng meta flag 'featured')
    public static function get_featured_news($limit = 8)
    {
        return self::cquery([
            'meta_query' => [
                [
                    'key'     => 'flags',
                    'value'   => 'featured',
                    'compare' => 'LIKE',
                ]
            ],
            'posts_per_page' => $limit,
        ]);
    }

    // Bài liên quan (dùng taxonomy + exclude current)
    public static function get_related_posts($post_id, $limit = 6)
    {
        $terms = wp_get_post_terms($post_id, 'event-categories', ['fields' => 'ids']);

        return self::cquery([
            'post__not_in'   => [$post_id],
            'tax_query'      => $terms ? [
                [
                    'taxonomy' => 'event-categories',
                    'field'    => 'term_id',
                    'terms'    => $terms,
                ]
            ] : [],
            'posts_per_page' => $limit,
            'orderby'        => 'rand', // hoặc date
        ]);
    }

    /**
     * LẤY BÀI VIẾT CÓ TẤT CẢ FLAGS CHỈ ĐỊNH (AND condition) - TỐI ƯU
     * Ví dụ: ['breaking', 'hot'] → chỉ lấy bài có cả 2 flag
     */
    public static function getPostsWithAllFlags(
        string $post_type,
        array $requiredFlags,
        int $posts_per_page = 8,
        string $orderby = 'post_id DESC'
    ): array {
        if (empty($requiredFlags)) {
            return [];
        }

        global $wpdb;
        $table = CustomTableManager::getTableName($post_type);

        $flag_count = count($requiredFlags);
        $placeholders = str_repeat('%s,', $flag_count - 1) . '%s';

        $sql = $wpdb->prepare(
            "SELECT post_id 
             FROM `$table`
             WHERE meta_key = 'flags' 
               AND meta_value IN ($placeholders)
             GROUP BY post_id 
             HAVING COUNT(DISTINCT meta_value) = %d 
             ORDER BY {$orderby}
             LIMIT %d",
            array_merge($requiredFlags, [$flag_count, $posts_per_page])
        );

        $post_ids = $wpdb->get_col($sql);

        if (empty($post_ids)) {
            return [];
        }

        return get_posts([
            'post_type'      => $post_type,
            'post__in'       => $post_ids,
            'posts_per_page' => $posts_per_page,
            'orderby'        => 'post__in',
            'suppress_filters' => false,
        ]);
    }

    /**
     * LẤY BÀI VIẾT CÓ ÍT NHẤT 1 FLAG TRONG DANH SÁCH (OR condition)
     * Ví dụ: ['breaking', 'hot'] → bài nào có breaking HOẶC hot đều được
     */
    public static function getPostsWithAnyFlags(
        string $post_type,
        array $flags,
        int $posts_per_page = 8
    ): array {
        if (empty($flags)) {
            return [];
        }

        global $wpdb;
        $table = CustomTableManager::getTableName($post_type);

        $placeholders = str_repeat('%s,', count($flags) - 1) . '%s';

        $post_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT post_id 
             FROM `$table`
             WHERE meta_key = 'flags' 
               AND meta_value IN ($placeholders)
             ORDER BY post_id DESC
             LIMIT %d",
            array_merge($flags, [$posts_per_page])
        ));

        if (empty($post_ids)) {
            return [];
        }

        return get_posts([
            'post_type'      => $post_type,
            'post__in'       => $post_ids,
            'posts_per_page' => $posts_per_page,
            'orderby'        => 'post__in',
            'suppress_filters' => false,
        ]);
    }

    /**
     * LẤY DANH SÁCH MỚI NHẤT MERGED (post + event) – TỐI ƯU CHO 500+ BÀI
     * Dùng DataCache + versioning (tự invalidate khi publish bài mới)
     */
    public static function getLatestMergedPosts(int $posts_per_page = 1, int $paged = 1): \WP_Query
    {
        $paged = max(1, (int) $paged);

        // Tự động lấy version merged (invalidate khi có bài post hoặc event mới)
        $version = \App\Helpers\CacheHelper::getDataVersion('content_list') ?? 1;

        $cacheKey = "merged_latest_{$posts_per_page}_p{$paged}_v{$version}";

        return \App\Helpers\DataCache::remember($cacheKey, 20 * MINUTE_IN_SECONDS, function () use ($posts_per_page, $paged) {
            $args = [
                'post_type'              => ['post', 'event'],
                'post_status'            => 'publish',
                'posts_per_page'         => $posts_per_page,
                'paged'                  => $paged,
                'orderby'                => 'date',
                'order'                  => 'DESC',
                'no_found_rows'          => false,        // BẮT BUỘC để pagination chính xác
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'suppress_filters'       => false,        // Để CustomTableManager chạy meta_query
            ];

            return \App\Database\CustomTableManager::query($args);
        });
    }
}