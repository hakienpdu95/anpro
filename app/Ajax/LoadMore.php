<?php
namespace App\Ajax;

class LoadMore {
    public static function init() {
        add_action('wp_ajax_load_more_posts', [self::class, 'handle']);
        add_action('wp_ajax_nopriv_load_more_posts', [self::class, 'handle']);
    }

    public static function handle() {
        check_ajax_referer('load_more_nonce', 'nonce');

        // Tắt buffer + header tối ưu
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        $offset = max(6, (int) ($_POST['offset'] ?? 6));

        $chunk = \App\Helpers\QueryCache::getCachedLoadMoreChunk($offset, 3);

        wp_send_json_success($chunk);
    }
}