<?php namespace App\Helpers;

use App\Database\CustomTableManager;

class ViewCounter {

    private static string $cache_group = 'sage_views';
    private static array  $request_lock = [];

    public static function init(): void {
        add_action('wp', [self::class, 'incrementView'], 10);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[ViewCounter] Initialized');
        }
    }

    public static function incrementView(): void {
        if (!is_singular() || is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        $post_id = get_queried_object_id();
        if ($post_id <= 0 || !CustomTableManager::isHandledPost($post_id)) {
            return;
        }

        // Tầng 1: request lock — ngăn hook chạy 2 lần trong cùng request
        if (isset(self::$request_lock[$post_id])) return;
        self::$request_lock[$post_id] = true;

        // Tầng 2: IP lock — chống F5, refresh nhanh, bot (60 giây / IP+post)
        $ip       = self::getClientIp();
        $lock_key = 'view_lock_' . $post_id . '_' . md5($ip);
        if (get_transient($lock_key)) return;
        set_transient($lock_key, '1', 60);

        // Tầng 3: lấy count hiện tại, tăng +1
        $current   = self::getViews($post_id, false);
        $new_total = $current + 1;

        // Cập nhật object cache ngay để hiển thị real-time
        wp_cache_set(self::$cache_group . ':' . $post_id, $new_total, self::$cache_group, 0);

        // Defer DB write ra shutdown — không block page render
        add_action('shutdown', function () use ($post_id, $new_total) {
            update_post_meta($post_id, 'post_views', $new_total);
        }, 999);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[ViewCounter] post #{$post_id}: {$new_total} views (ip: {$ip})");
        }
    }

    /**
     * Lấy IP thật của client, xử lý đúng khi đứng sau proxy / Cloudflare.
     */
    private static function getClientIp(): string {
        $headers = [
            'HTTP_CF_CONNECTING_IP',   // Cloudflare
            'HTTP_X_REAL_IP',          // nginx proxy
            'HTTP_X_FORWARDED_FOR',    // load balancer (có thể chứa nhiều IP)
        ];

        foreach ($headers as $header) {
            $value = $_SERVER[$header] ?? '';
            if ($value === '') continue;
            // X-Forwarded-For có thể là "client, proxy1, proxy2" — lấy phần tử đầu tiên
            $ip = trim(explode(',', $value)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    public static function getViews(int $post_id, bool $real_time = true): int {
        if ($post_id <= 0) return 0;

        $cached = (int) wp_cache_get(self::$cache_group . ':' . $post_id, self::$cache_group);

        if ($real_time && $cached > 0) {
            return $cached;
        }

        return (int) CustomTableManager::getMeta($post_id, 'post_views', true) ?: 0;
    }

    public static function isHot(int $post_id): bool {
        return self::getViews($post_id) >= 5000;
    }
}
