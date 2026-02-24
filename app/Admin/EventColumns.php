<?php
namespace App\Admin;

use App\Database\CustomTableManager;

class EventColumns {

    public static function init(): void {
        add_filter('manage_event_posts_columns', [self::class, 'addColumns']);
        add_action('manage_event_posts_custom_column', [self::class, 'renderColumn'], 10, 2);
    }

    public static function addColumns(array $columns): array {
        $new_columns = [];
        foreach ($columns as $key => $title) {
            $new_columns[$key] = $title;
            if ($key === 'title') {
                $new_columns['thumbnail']     = 'Ảnh';
                $new_columns['reading_time']  = 'Thời gian đọc';
                $new_columns['source']        = 'Nguồn';
                $new_columns['flags']         = 'Đánh dấu';
                $new_columns['event_cat']      = 'Thể loại';
            }
        }
        return $new_columns;
    }

    public static function renderColumn(string $column, int $post_id): void {
        switch ($column) {
            case 'thumbnail':
                echo get_the_post_thumbnail($post_id, [60, 60]);
                break;

            case 'reading_time':
                echo (int) cmeta('reading_time', $post_id) . ' phút';
                break;

            case 'source':
                echo esc_html(cmeta('source', $post_id));
                break;

            case 'flags':
                $flags = cmeta('flags', $post_id);
                if (is_array($flags) || is_string($flags)) {
                    echo str_replace(
                        ['hot', 'featured', 'breaking'],
                        ['🔥 Nóng', '⭐ Nổi bật', '🚨 Khẩn'],
                        implode(', ', (array)$flags)
                    );
                }
                break;

            case 'event_cat':
                $terms = get_the_terms($post_id, 'event-categories');
                echo $terms ? implode(', ', wp_list_pluck($terms, 'name')) : '—';
                break;
        }
    }
}