<?php

namespace App\Helpers;

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
}