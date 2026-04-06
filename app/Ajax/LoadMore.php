<?php
namespace App\Ajax;

class LoadMore {
    public static function init() {
        add_action('wp_ajax_load_more_posts', [self::class, 'handle']);
        add_action('wp_ajax_nopriv_load_more_posts', [self::class, 'handle']);
    }

    public static function handle() {
        check_ajax_referer('load_more_nonce', 'nonce');

        // === TẮT TẤT CẢ FILTER NẶNG TẠM THỜI (giảm mạnh độ trễ) ===
        remove_filter('post_thumbnail_html', [\App\Placeholders\PlaceholderHandler::class, 'replaceWithPlaceholder'], 999);
        remove_filter('post_thumbnail_url', [\App\Placeholders\PlaceholderHandler::class, 'replaceThumbnailUrl'], 999);
        
        // Tắt thêm các hook nặng khác nếu anh có
        remove_action('save_post', [\App\Helpers\CacheHelper::class, 'flushOnPostSave']); // ví dụ
        // ... có thể tắt thêm ViewCounter, Watermark, v.v.

        while (ob_get_level() > 0) ob_end_clean();

        $offset = max(6, (int) ($_POST['offset'] ?? 6));

        $chunk = \App\Helpers\QueryCache::getLoadMoreChunk($offset, 3);

        // Bật lại filter
        add_filter('post_thumbnail_html', [\App\Placeholders\PlaceholderHandler::class, 'replaceWithPlaceholder'], 999, 5);
        add_filter('post_thumbnail_url', [\App\Placeholders\PlaceholderHandler::class, 'replaceThumbnailUrl'], 999, 3);

        wp_send_json_success($chunk);
    }
}