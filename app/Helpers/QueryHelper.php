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
     * LẤY BÀI CÓ TẤT CẢ FLAGS (AND) - RAW SQL TỐI ƯU
     * Luôn ORDER BY post_id DESC (an toàn nhất vì bảng custom meta chỉ có post_id)
     */
    public static function getPostsWithAllFlags(
        string $post_type,
        array $requiredFlags,
        int $posts_per_page = 8
    ): array {
        if (empty($requiredFlags)) return [];

        global $wpdb;
        $table = CustomTableManager::getTableName($post_type);
        $flag_count = count($requiredFlags);
        $placeholders = str_repeat('%s,', $flag_count - 1) . '%s';

        $sql = $wpdb->prepare(
            "SELECT post_id FROM `$table`
             WHERE meta_key = 'flags' AND meta_value IN ($placeholders)
             GROUP BY post_id
             HAVING COUNT(DISTINCT meta_value) = %d
             ORDER BY post_id DESC
             LIMIT %d",
            array_merge($requiredFlags, [$flag_count, $posts_per_page])
        );

        $post_ids = $wpdb->get_col($sql);

        if (empty($post_ids)) return [];

        return get_posts([
            'post_type'        => $post_type,
            'post__in'         => $post_ids,
            'posts_per_page'   => $posts_per_page,
            'orderby'          => 'post__in',
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
     * XÂY DỰNG TAX_QUERY LINH HOẠT TỪ CÚ PHÁP NGẮN GỌN
     */
    private static function buildTaxQuery(array $config): array
    {
        $tax_query = $config['tax_query'] ?? [];

        // Cú pháp ngắn gọn: 'category' => 'slug' hoặc 'category' => 123 hoặc 'event-categories' => [12,34]
        $short_keys = ['category', 'post_tag', 'tag', 'tags', 'event-categories', 'category_id', 'tag_id'];

        foreach ($short_keys as $key) {
            if (empty($config[$key])) continue;

            $taxonomy = $key;
            if (in_array($key, ['category_id', 'tag_id'])) $taxonomy = str_replace('_id', '', $key);
            if (in_array($key, ['tags', 'tag'])) $taxonomy = 'post_tag';

            $terms = (array) $config[$key];
            $field = (is_numeric($terms[0] ?? '') || is_numeric($terms)) ? 'term_id' : 'slug';

            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => $field,
                'terms'    => $terms,
                'operator' => 'IN'
            ];
        }

        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }

        return $tax_query;
    }

    /**
     * QUERY 11/10 – HỖ TRỢ TAX_QUERY LINH HOẠT + PINNED_FIRST + FLAGS (tối ưu tốc độ)
     */
    public static function getAdvancedPosts(array $config = [], int $ttl = 300): array
    {
        $defaults = [
            'post_type'      => 'post',
            'posts_per_page' => 8,
            'flags'          => [],
            'pinned_first'   => false,
            'post_status'    => 'publish',
        ];

        $config = wp_parse_args($config, $defaults);
        $post_type = $config['post_type'];

        // === XÂY DỰNG TAX_QUERY LINH HOẠT ===
        $tax_query = self::buildTaxQuery($config);

        // === META QUERY (flags + pinned) ===
        $meta_query = [];
        if (!empty($config['flags'])) {
            if (count($config['flags']) === 1) {
                $meta_query[] = ['key' => 'flags', 'value' => $config['flags'][0]];
            } else {
                // multi flags → raw SQL cũ (đã tối ưu)
                return self::getPostsWithAllFlags($post_type, $config['flags'], $config['posts_per_page']);
            }
        }

        // === PINNED_FIRST (luôn dùng WP_Query để hỗ trợ tax_query) ===
        if ($config['pinned_first']) {
            $meta_query[] = ['key' => 'is_pinned', 'value' => '1', 'compare' => '='];
            $meta_query[] = [
                'key'     => 'pinned_until',
                'value'   => current_time('mysql'),
                'compare' => '>=',
                'type'    => 'DATETIME'
            ];
        }

        // === BUILD WP_QUERY ARGS ===
        $args = [
            'post_type'              => $post_type,
            'posts_per_page'         => $config['posts_per_page'],
            'post_status'            => $config['post_status'],
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'suppress_filters'       => false,
        ];

        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
            $args['meta_query']['relation'] = 'AND';
        }

        $query = CustomTableManager::query($args);
        return $query->posts ?? [];
    }

    /**
     * PINNED + FLAGS 10/10 – Raw SQL cực nhanh (không đi qua WP_Query)
     */
    private static function getPinnedPostsWithFlags(
        string $post_type,
        array $flags,
        int $limit = 8
    ): array {
        if (empty($flags)) return [];

        global $wpdb;
        $table = CustomTableManager::getTableName($post_type);
        $now   = current_time('mysql');
        $placeholders = str_repeat('%s,', count($flags) - 1) . '%s';

        $sql = $wpdb->prepare(
            "SELECT DISTINCT m1.post_id 
             FROM `$table` m1
             INNER JOIN `$table` m2 ON m1.post_id = m2.post_id
             INNER JOIN `$table` m3 ON m1.post_id = m3.post_id
             INNER JOIN `$wpdb->posts` p ON m1.post_id = p.ID
             WHERE m1.meta_key = 'flags' 
               AND m1.meta_value IN ($placeholders)
               AND m2.meta_key = 'is_pinned' AND m2.meta_value = '1'
               AND m3.meta_key = 'pinned_until' AND m3.meta_value >= %s
               AND p.post_status = 'publish'
             ORDER BY p.post_date DESC
             LIMIT %d",
            array_merge($flags, [$now, $limit])
        );

        $post_ids = $wpdb->get_col($sql);
        if (empty($post_ids)) return [];

        return get_posts([
            'post_type'        => $post_type,
            'post__in'         => $post_ids,
            'posts_per_page'   => $limit,
            'orderby'          => 'post__in',
            'suppress_filters' => false,
        ]);
    }  
}