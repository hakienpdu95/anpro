<?php
namespace App\Database;

class CustomTableManager {
    public static array $registered = [];

    public static function register(string $post_type): void {
        $post_type = sanitize_key($post_type);
        if (!in_array($post_type, self::$registered)) {
            self::$registered[] = $post_type;
        }
    }

    public static function getTableName(string $post_type): string {
        global $wpdb;
        $slug = sanitize_key($post_type);
        return ($slug === 'post') 
            ? $wpdb->prefix . 'post_custom_meta' 
            : $wpdb->prefix . $slug . '_meta';
    }

    public static function init(): void {
        add_action('admin_init', [self::class, 'createMissingTables'], 5);

        // === INTERCEPT TẤT CẢ META FUNCTIONS (đây là phần cốt lõi) ===
        add_filter('get_post_metadata', [self::class, 'filterGetPostMetadata'], 999, 4);
        add_filter('add_post_metadata', [self::class, 'filterUpdatePostMetadata'], 999, 5);
        add_filter('update_post_metadata', [self::class, 'filterUpdatePostMetadata'], 999, 5);
        add_filter('delete_post_metadata', [self::class, 'filterDeletePostMetadata'], 999, 5);
    }

    private static function shouldHandle(int $post_id): bool {
        if ($post_id <= 0) return false;
        $post_type = get_post_type($post_id);
        return in_array($post_type, self::$registered);
    }

    private static function getTable(int $post_id): ?string {
        if (!self::shouldHandle($post_id)) return null;
        $table = self::getTableName(get_post_type($post_id));
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) === $table ? $table : null;
    }

    // Lấy meta từ custom table
    public static function filterGetPostMetadata($value, $object_id, $meta_key, $single) {
        $table = self::getTable($object_id);
        if (!$table) return $value;

        global $wpdb;

        if ($meta_key) {
            $result = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM `$table` WHERE post_id = %d AND meta_key = %s LIMIT 1",
                $object_id, $meta_key
            ));

            if ($result !== null) {
                // Ưu tiên JSON, fallback unserialize
                $decoded = json_decode($result, true);
                $val = ($decoded !== null) ? $decoded : maybe_unserialize($result);
                return $single ? $val : [$val];
            }
        } else {
            // Lấy tất cả meta (dùng cho rwmb_meta('') hoặc get_post_meta all)
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT meta_key, meta_value FROM `$table` WHERE post_id = %d",
                $object_id
            ), ARRAY_A);

            $meta = [];
            foreach ($results as $row) {
                $decoded = json_decode($row['meta_value'], true);
                $val = ($decoded !== null) ? $decoded : maybe_unserialize($row['meta_value']);
                $meta[$row['meta_key']][] = $val; // format đúng của WP
            }
            return $meta ?: $value;
        }

        return $value;
    }

    // Lưu / update vào custom table + chặn wp_postmeta
    public static function filterUpdatePostMetadata($check, $object_id, $meta_key, $meta_value, $prev_value) {
        $table = self::getTable($object_id);
        if (!$table || empty($meta_key)) return $check;

        global $wpdb;

        // Xóa record cũ (tránh duplicate)
        $wpdb->delete($table, [
            'post_id'  => $object_id,
            'meta_key' => $meta_key
        ]);

        $save_value = (is_array($meta_value) || is_object($meta_value))
            ? wp_json_encode($meta_value, JSON_UNESCAPED_UNICODE)
            : $meta_value;

        $wpdb->insert($table, [
            'post_id'    => $object_id,
            'meta_key'   => $meta_key,
            'meta_value' => $save_value,
        ]);

        return true; // Chặn hoàn toàn việc lưu vào wp_postmeta
    }

    // Xóa khỏi custom table
    public static function filterDeletePostMetadata($check, $object_id, $meta_key, $meta_value, $delete_all) {
        $table = self::getTable($object_id);
        if (!$table) return $check;

        global $wpdb;

        if ($delete_all) {
            $wpdb->delete($table, ['post_id' => $object_id]);
        } elseif ($meta_key) {
            $where = ['post_id' => $object_id, 'meta_key' => $meta_key];
            if ($meta_value !== '') {
                $where['meta_value'] = $meta_value;
            }
            $wpdb->delete($table, $where);
        }
        return true;
    }

    // === Tạo bảng (giữ nguyên logic cũ của bạn) ===
    public static function createMissingTables(): void {
        global $wpdb;
        foreach (self::$registered as $post_type) {
            $table = self::getTableName($post_type);
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) !== $table) {
                self::createTable($table);
            }
        }
    }

    private static function createTable(string $table_name): void {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
            `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
            `meta_key` varchar(255) DEFAULT NULL,
            `meta_value` longtext,
            PRIMARY KEY (`meta_id`),
            KEY `post_id` (`post_id`),
            KEY `meta_key` (`meta_key`),
            KEY `post_id_meta_key` (`post_id`, `meta_key`)
        ) $charset_collate;";

        dbDelta($sql);
        error_log("✅ [CustomTable] Đã tạo bảng thành công: {$table_name}");
    }
}