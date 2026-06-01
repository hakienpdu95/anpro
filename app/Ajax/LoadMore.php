<?php
namespace App\Ajax;

class LoadMore {

    private const INITIAL_OFFSET  = 3;
    private const POSTS_PER_CHUNK = 3;

    public static function init(): void {
        add_action('wp_ajax_load_more_posts',        [self::class, 'handle']);
        add_action('wp_ajax_nopriv_load_more_posts', [self::class, 'handle']);
    }

    public static function handle(): void {
        check_ajax_referer('load_more_nonce', 'nonce');

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $offset = max(self::INITIAL_OFFSET, (int) ($_POST['offset'] ?? self::INITIAL_OFFSET));
        $chunk  = \App\Helpers\QueryCache::getLoadMoreChunk($offset, self::POSTS_PER_CHUNK);

        header('X-Has-More: ' . ($chunk['has_more'] ? '1' : '0'));
        echo wp_kses_post($chunk['html'] ?? '');
        exit;
    }
}