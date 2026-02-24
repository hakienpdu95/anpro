<?php
namespace App\Database;

class CustomTableManager {
    private static array $registered = [];
    private static array $table_exists = [];     // Cache table existence
    private static array $meta_cache = [];       // Static cache per-request
    private static string $cache_group = 'custom_post_meta';

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

        // Intercept tất cả meta functions (chặn wp_postmeta)
        add_filter('get_post_metadata', [self::class, 'filterGetPostMetadata'], 999, 4);
        add_filter('add_post_metadata', [self::class, 'filterUpdatePostMetadata'], 999, 5);
        add_filter('update_post_metadata', [self::class, 'filterUpdatePostMetadata'], 999, 5);
        add_filter('delete_post_metadata', [self::class, 'filterDeletePostMetadata'], 999, 5);

        // Cleanup khi xóa post
        add_action('delete_post', [self::class, 'deletePostMeta'], 10, 2);

        // Flush cache khi save
        add_action('save_post', [self::class, 'flushPostCache'], 999, 2);
        add_action('rwmb_after_save_post', [self::class, 'flushPostCache'], 999, 1);

        // Preload meta cho trang edit (tăng tốc admin cực mạnh)
        add_action('load-post.php', [self::class, 'preloadCurrentPostMeta']);
        add_action('load-post-new.php', [self::class, 'preloadCurrentPostMeta']);
    }

    private static function shouldHandle(int $post_id): bool {
        if ($post_id <= 0) return false;
        $post_type = get_post_type($post_id);
        return in_array($post_type, self::$registered);
    }

    private static function getTable(int $post_id): ?string {
        if (!self::shouldHandle($post_id)) return null;

        $post_type = get_post_type($post_id);
        $table = self::getTableName($post_type);

        if (!isset(self::$table_exists[$table])) {
            global $wpdb;
            self::$table_exists[$table] = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) === $table;
        }
        return self::$table_exists[$table] ? $table : null;
    }

    // ==================== CACHE LAYER 3 TẦNG ====================
    private static function loadAllMeta(int $post_id): array {
        if (isset(self::$meta_cache[$post_id])) {
            return self::$meta_cache[$post_id];
        }

        $cached = wp_cache_get($post_id, self::$cache_group);
        if ($cached !== false) {
            return self::$meta_cache[$post_id] = $cached;
        }

        $table = self::getTable($post_id);
        if (!$table) {
            return self::$meta_cache[$post_id] = [];
        }

        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_key, meta_value FROM `$table` WHERE post_id = %d",
            $post_id
        ), ARRAY_A);

        $meta = [];
        foreach ($results as $row) {
            $value = json_decode($row['meta_value'], true);
            if ($value === null) {
                $value = maybe_unserialize($row['meta_value']);
            }
            $meta[$row['meta_key']] = $value; // Chuẩn cho rwmb_meta / cmeta
        }

        self::$meta_cache[$post_id] = $meta;
        wp_cache_set($post_id, $meta, self::$cache_group, 3600); // 1 giờ nếu có Redis
        return $meta;
    }

    public static function flushPostCache($post_id, $post = null): void {
        if (is_object($post)) $post_id = $post->ID;
        if ($post_id <= 0) return;

        unset(self::$meta_cache[$post_id]);
        wp_cache_delete($post_id, self::$cache_group);
    }

    public static function preloadCurrentPostMeta(): void {
        $post_id = (int) ($_GET['post'] ?? 0);
        if ($post_id > 0) {
            self::loadAllMeta($post_id);
        }
    }

    // ==================== FILTERS ====================
    public static function filterGetPostMetadata($value, $object_id, $meta_key, $single) {
        if (!self::shouldHandle($object_id)) return $value;

        $all_meta = self::loadAllMeta($object_id);

        if (empty($meta_key)) {
            return $all_meta ?: $value;
        }

        $result = $all_meta[$meta_key] ?? null;
        return $single ? $result : ($result !== null ? [$result] : []);
    }

    public static function filterUpdatePostMetadata($check, $object_id, $meta_key, $meta_value, $prev_value) {
        $table = self::getTable($object_id);
        if (!$table || empty($meta_key)) return $check;

        global $wpdb;

        // Delete trước để tránh duplicate
        $wpdb->delete($table, [
            'post_id'  => $object_id,
            'meta_key' => $meta_key
        ]);

        $save_value = (is_array($meta_value) || is_object($meta_value))
            ? wp_json_encode($meta_value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : $meta_value;

        $wpdb->insert($table, [
            'post_id'    => $object_id,
            'meta_key'   => $meta_key,
            'meta_value' => $save_value,
        ]);

        self::flushPostCache($object_id);
        return true; // Chặn wp_postmeta hoàn toàn
    }

    public static function filterDeletePostMetadata($check, $object_id, $meta_key, $meta_value, $delete_all) {
        $table = self::getTable($object_id);
        if (!$table) return $check;

        global $wpdb;

        if ($delete_all || empty($meta_key)) {
            $wpdb->delete($table, ['post_id' => $object_id]);
        } else {
            $where = ['post_id' => $object_id, 'meta_key' => $meta_key];
            if ($meta_value !== '') $where['meta_value'] = $meta_value;
            $wpdb->delete($table, $where);
        }

        self::flushPostCache($object_id);
        return true;
    }

    public static function deletePostMeta(int $post_id, $post): void {
        $table = self::getTable($post_id);
        if ($table) {
            global $wpdb;
            $wpdb->delete($table, ['post_id' => $post_id]);
        }
        self::flushPostCache($post_id);
    }

    // ==================== TẠO BẢNG + INDEX TỐI ƯU ====================
    public static function createMissingTables(): void {
        global $wpdb;
        foreach (self::$registered as $post_type) {
            $table = self::getTableName($post_type);
            if (!isset(self::$table_exists[$table])) {
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
            KEY `post_id_meta_key` (`post_id`, `meta_key`(191))
        ) $charset_collate;";

        dbDelta($sql);
        self::$table_exists[$table_name] = true;
        error_log("✅ [CustomTable 10/10] Đã tạo/tối ưu bảng: {$table_name}");
    }

    // Helper tiện lợi (dùng trong Blade nếu muốn)
    public static function getMeta(int $post_id, string $key = '', bool $single = true) {
        return self::filterGetPostMetadata(null, $post_id, $key, $single);
    }
}