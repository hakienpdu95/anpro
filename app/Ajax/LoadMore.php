<?php
namespace App\Ajax;

class LoadMore {
    public static function init() {
        add_action('wp_ajax_load_more_posts', [self::class, 'handle']);
        add_action('wp_ajax_nopriv_load_more_posts', [self::class, 'handle']);
    }

    public static function handle() {
        check_ajax_referer('load_more_nonce', 'nonce');

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $offset = max(3, (int) ($_POST['offset'] ?? 3));

        $chunk = \App\Helpers\QueryCache::getLoadMoreChunk($offset, 3);

        header('X-Has-More: ' . ($chunk['has_more'] ? '1' : '0'));
        echo $chunk['html'] ?? '';
        exit;
    }
}