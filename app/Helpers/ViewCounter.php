<?php namespace App\Helpers;

use App\Database\CustomTableManager;

class ViewCounter {

    private static string $cache_group = 'sage_views';
    private static array  $request_lock = [];   // chống duplicate trong cùng 1 request

    public static function init(): void {
        // Hook muộn + an toàn nhất (chạy sau khi query hoàn tất)
        add_action('wp', [self::class, 'incrementView'], 10);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('🚀 [ViewCounter Real-time 12/10] Direct DB Update - No Cron Delay');
        }
    }

    /**
     * TĂNG VIEW NGAY LẬP TỨC + CHỐNG DUPLICATE
     */
    public static function incrementView(): void {
        if (!is_singular() || is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        $post_id = get_queried_object_id();
        if ($post_id <= 0 || !CustomTableManager::isHandledPost($post_id)) {
            return;
        }

        // === 1. LOCK TRONG REQUEST (ngăn hook chạy nhiều lần) ===
        if (isset(self::$request_lock[$post_id])) return;
        self::$request_lock[$post_id] = true;

        // === 2. LOCK THEO IP + POST (chống F5, refresh nhanh, bot) ===
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $lock_key = 'view_lock_' . $post_id . '_' . md5($ip);
        if (get_transient($lock_key)) return;           // đã count trong 60 giây qua
        set_transient($lock_key, '1', 60);              // lock 60 giây

        // === 3. LẤY SỐ VIEW HIỆN TẠI + TĂNG +1 ===
        $current = self::getViews($post_id, false);     // lấy từ DB (không real-time để tránh race)
        $new_total = $current + 1;

        // Update trực tiếp vào DB → trigger CustomTableManager + bump version tự động
        update_post_meta($post_id, 'post_views', $new_total);

        // Update cache ngay để hiển thị real-time
        wp_cache_set(self::$cache_group . ':' . $post_id, $new_total, self::$cache_group, 0);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[ViewCounter] +1 view → Post #{$post_id} | Tổng: {$new_total}");
        }
    }

    public static function getViews(int $post_id, bool $real_time = true): int {
        if ($post_id <= 0) return 0;

        $cached = (int) wp_cache_get(self::$cache_group . ':' . $post_id, self::$cache_group);

        if ($real_time && $cached > 0) {
            return $cached;
        }

        return (int) CustomTableManager::getMeta($post_id, 'post_views', true) ?: 0;
    }

    // Helper tiện ích
    public static function isHot(int $post_id): bool {
        return self::getViews($post_id) >= 5000;   // bạn chỉnh ngưỡng tùy ý
    }
}