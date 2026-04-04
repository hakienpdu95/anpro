<?php
namespace App\Ajax;

class LoadMore {
    public static function init() {
        add_action('wp_ajax_load_more_posts', [self::class, 'handle']);
        add_action('wp_ajax_nopriv_load_more_posts', [self::class, 'handle']);
    }

    public static function handle() {
        check_ajax_referer('load_more_nonce', 'nonce');

        $paged          = max(2, (int) ($_POST['paged'] ?? 2));
        $posts_per_page = 3;

        $chunk = \App\Helpers\QueryCache::getCachedLoadMoreChunk($paged, $posts_per_page);

        wp_send_json_success([
            'html'      => $chunk['html'],
            'next_page' => $paged + 1,
            'has_more'  => $chunk['has_more'],
        ]);
    }
}