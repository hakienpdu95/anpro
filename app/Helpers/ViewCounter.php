<?php namespace App\Helpers;

use App\Database\CustomTableManager;

class ViewCounter {

    private static string $view_prefix = 'sage_post_views:';
    private static string $pending_key = 'sage_pending_views';
    private static string $cache_group = 'sage_views';

    public static function init(): void {
        // Đếm view khi xem bài
        add_action('template_redirect', [self::class, 'incrementView'], 5);

        // Cron tự động mỗi 5 phút
        if (!wp_next_scheduled('sage_sync_post_views')) {
            wp_schedule_event(time(), 'every_5_minutes', 'sage_sync_post_views');
        }
        add_action('sage_sync_post_views', [self::class, 'syncToDatabase']);

        // Manual trigger (dùng để test ngay)
        add_action('init', [self::class, 'handleManualSync']);

        // Đăng ký cron interval 5 phút
        add_filter('cron_schedules', [self::class, 'addCustomCronInterval']);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('🚀 [ViewCounter] Initialized successfully');
        }
    }

    public static function addCustomCronInterval($schedules) {
        $schedules['every_5_minutes'] = [
            'interval' => 300,
            'display'  => 'Every 5 minutes'
        ];
        return $schedules;
    }

    public static function handleManualSync() {
        if (isset($_GET['force_sync_views']) && current_user_can('manage_options')) {
            self::syncToDatabase();
            wp_die('<h2>✅ ViewCounter đã sync thủ công thành công!</h2><p>Kiểm tra debug.log và database để xem kết quả.</p>', 'ViewCounter Sync Done', ['response' => 200]);
        }
    }

    public static function incrementView(): void {
        if (!is_singular() || is_admin() || wp_doing_ajax() || wp_doing_cron()) return;

        $post_id = get_queried_object_id();
        if ($post_id <= 0 || !CustomTableManager::isHandledPost($post_id)) return;

        $key = self::$view_prefix . $post_id;

        // Tăng view trong cache
        $current = (int) wp_cache_get($key, self::$cache_group);
        wp_cache_set($key, $current + 1, self::$cache_group, 0); // 0 = forever

        // Thêm vào pending list
        $pending = wp_cache_get(self::$pending_key, self::$cache_group) ?: [];
        $pending[$post_id] = ($pending[$post_id] ?? 0) + 1;
        wp_cache_set(self::$pending_key, $pending, self::$cache_group, 3600);

        // Bump version để list bài viết tự update
        $post_type = get_post_type($post_id);
        CacheHelper::bumpDataVersion($post_type);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[ViewCounter] +1 view → Post #{$post_id} (Redis: " . ($current + 1) . ")");
        }
    }

    public static function getViews(int $post_id, bool $real_time = true): int {
        if ($post_id <= 0) return 0;

        $redis_views = (int) wp_cache_get(self::$view_prefix . $post_id, self::$cache_group);

        return $real_time 
            ? $redis_views + self::getDatabaseViews($post_id) 
            : self::getDatabaseViews($post_id);
    }

    private static function getDatabaseViews(int $post_id): int {
        return (int) CustomTableManager::getMeta($post_id, 'post_views', true) ?: 0;
    }

    public static function syncToDatabase(): void {
        $pending = wp_cache_get(self::$pending_key, self::$cache_group) ?: [];
        if (empty($pending)) {
            if (defined('WP_DEBUG') && WP_DEBUG) error_log('[ViewCounter] Sync: Không có view nào cần sync');
            return;
        }

        $count = 0;
        foreach ($pending as $post_id => $add_views) {
            if ($post_id <= 0 || $add_views <= 0) continue;

            $current_db = self::getDatabaseViews($post_id);
            $new_total  = $current_db + $add_views;

            // Update meta (sẽ tự động trigger CustomTableManager)
            update_post_meta($post_id, 'post_views', $new_total);

            // Xóa Redis view của bài này
            wp_cache_delete(self::$view_prefix . $post_id, self::$cache_group);

            $count++;
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[ViewCounter] Synced Post #{$post_id}: +{$add_views} → Tổng {$new_total}");
            }
        }

        // Xóa pending list
        wp_cache_delete(self::$pending_key, self::$cache_group);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[ViewCounter] ✅ Đã sync {$count} bài viết xuống database");
        }
    }
}