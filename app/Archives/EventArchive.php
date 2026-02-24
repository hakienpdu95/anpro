<?php
namespace App\Archives;

use WP_Query;

/**
 * EventArchive – Quản lý toàn bộ archive + filter cho CPT 'event'
 * Modular 10/10 – Dễ scale khi site có 20+ CPT
 */
class EventArchive {

    public static function init(): void {
        add_action('pre_get_posts', [self::class, 'applyFilters'], 20);
    }

    public static function applyFilters(WP_Query $query): void {
        // Chỉ áp dụng cho archive chính của event (không ảnh hưởng admin hoặc query khác)
        if (is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'event') {
            return;
        }

        // === TÌM KIẾM ===
        if (!empty($_GET['s'])) {
            $query->set('s', sanitize_text_field($_GET['s']));
        }

        // === TAXONOMY FILTER ===
        if (!empty($_GET['event_cat'])) {
            $query->set('tax_query', [
                [
                    'taxonomy' => 'event-categories',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($_GET['event_cat']),
                ],
            ]);
        }

        // === FLAGS (checkbox_list) ===
        if (!empty($_GET['flags']) && is_array($_GET['flags'])) {
            $flags = array_map('sanitize_text_field', $_GET['flags']);
            $meta_query = $query->get('meta_query') ?: ['relation' => 'AND'];
            $meta_query[] = [
                'key'     => 'flags',
                'value'   => $flags,
                'compare' => 'IN',
            ];
            $query->set('meta_query', $meta_query);
        }

        // === READING TIME RANGE ===
        $meta_query = $query->get('meta_query') ?: ['relation' => 'AND'];
        if (!empty($_GET['reading_time_min']) || !empty($_GET['reading_time_max'])) {
            $meta_query[] = [
                'key'     => 'reading_time',
                'value'   => [
                    (int) ($_GET['reading_time_min'] ?? 0),
                    (int) ($_GET['reading_time_max'] ?? 999),
                ],
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC',
            ];
        }
        $query->set('meta_query', $meta_query);

        // === ORDERBY ===
        $orderby = sanitize_text_field($_GET['orderby'] ?? 'date');
        if ($orderby === 'reading_time') {
            $query->set('orderby', 'meta_value_num');
            $query->set('meta_key', 'reading_time');
        } elseif ($orderby === 'title') {
            $query->set('orderby', 'title');
        } else {
            $query->set('orderby', 'date');
        }

        $query->set('order', strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC');

        // Số bài mỗi trang (có thể config động sau)
        $query->set('posts_per_page', 12);
    }
}