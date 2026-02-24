<?php
namespace App\Database;

use WP_Meta_Query;
use WP_Query;

class CustomTableManager {
    public static array $registered = [];
    private static array $table_exists = [];
    private static array $meta_cache = [];
    private static string $cache_group = 'custom_post_meta_v2';

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

        // Meta CRUD + Cache
        add_filter('get_post_metadata', [self::class, 'filterGetPostMetadata'], 999, 4);
        add_filter('add_post_metadata', [self::class, 'filterUpdatePostMetadata'], 999, 5);
        add_filter('update_post_metadata', [self::class, 'filterUpdatePostMetadata'], 999, 5);
        add_filter('delete_post_metadata', [self::class, 'filterDeletePostMetadata'], 999, 5);

        // META_QUERY + ORDERBY siêu nhanh
        add_filter('posts_clauses', [self::class, 'filterPostsClauses'], 999, 2);
        add_filter('posts_orderby', [self::class, 'filterOrderByMeta'], 999, 2);

        // Cleanup + Cache flush
        add_action('delete_post', [self::class, 'deletePostMeta'], 10, 2);
        add_action('save_post', [self::class, 'flushPostCache'], 999, 2);
        add_action('rwmb_after_save_post', [self::class, 'flushPostCache'], 999, 1);

        // Preload tối ưu
        add_action('load-post.php', [self::class, 'preloadCurrentPostMeta']);
        add_action('load-post-new.php', [self::class, 'preloadCurrentPostMeta']);
        add_filter('the_posts', [self::class, 'preloadThePostsMeta'], 10, 2);
    }

    private static function shouldHandle(int $post_id): bool {
        if ($post_id <= 0) return false;
        return in_array(get_post_type($post_id), self::$registered);
    }

    private static function getTable(int $post_id): ?string {
        if (!self::shouldHandle($post_id)) return null;
        $table = self::getTableName(get_post_type($post_id));
        if (!isset(self::$table_exists[$table])) {
            global $wpdb;
            self::$table_exists[$table] = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) === $table;
        }
        return self::$table_exists[$table] ? $table : null;
    }

    // ==================== CACHE 3 TẦNG + PRELOAD ====================
    private static function loadAllMeta(int $post_id): array {
        if (isset(self::$meta_cache[$post_id])) return self::$meta_cache[$post_id];
        $cached = wp_cache_get($post_id, self::$cache_group);
        if ($cached !== false) return self::$meta_cache[$post_id] = $cached;

        $table = self::getTable($post_id);
        if (!$table) return self::$meta_cache[$post_id] = [];

        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_key, meta_value FROM `$table` WHERE post_id = %d", $post_id
        ), ARRAY_A);

        $meta = [];
        foreach ($results as $row) {
            $val = json_decode($row['meta_value'], true) ?? maybe_unserialize($row['meta_value']);
            $meta[$row['meta_key']] = $val;
        }

        self::$meta_cache[$post_id] = $meta;
        wp_cache_set($post_id, $meta, self::$cache_group, 3600); // Redis sẽ tự động hit
        return $meta;
    }

    public static function preloadThePostsMeta(array $posts, WP_Query $query): array {
        foreach ($posts as $post) {
            if (self::shouldHandle($post->ID)) self::loadAllMeta($post->ID);
        }
        return $posts;
    }

    public static function getMeta(int $post_id, string $key = '', bool $single = true) {
        if ($post_id <= 0 || !self::shouldHandle($post_id)) {
            return $single ? '' : [];
        }

        $all = self::loadAllMeta($post_id);
        if ($key === '') return $all;

        $result = $all[$key] ?? null;

        if ($key === 'flags') {
            if (is_string($result)) {
                $decoded = json_decode($result, true);
                $result = (is_array($decoded)) ? $decoded : [$result];
            } elseif (!is_array($result)) {
                $result = $result ? [$result] : [];
            }
        }

        return $single ? $result : ($result !== null ? [$result] : []);
    }

    public static function flushPostCache($post_id, $post = null): void {
        if (is_object($post)) $post_id = $post->ID;
        if ($post_id > 0) {
            unset(self::$meta_cache[$post_id]);
            wp_cache_delete($post_id, self::$cache_group);
        }
    }

    public static function preloadCurrentPostMeta(): void {
        $post_id = (int) ($_GET['post'] ?? 0);
        if ($post_id > 0) self::loadAllMeta($post_id);
    }

    // ==================== META CRUD (giữ nguyên) ====================
    public static function filterGetPostMetadata($value, $object_id, $meta_key, $single) {
        if (!self::shouldHandle($object_id)) return $value;
        $all = self::loadAllMeta($object_id);
        if ($meta_key === '') return $all;
        $result = $all[$meta_key] ?? null;
        return $single ? $result : ($result !== null ? [$result] : []);
    }

    public static function filterUpdatePostMetadata($check, $object_id, $meta_key, $meta_value, $prev_value) {
        $table = self::getTable($object_id);
        if (!$table || empty($meta_key)) return $check;

        global $wpdb;

        $wpdb->delete($table, [
            'post_id'  => $object_id,
            'meta_key' => $meta_key
        ]);

        if (is_array($meta_value) || is_object($meta_value)) {
            $save_value = wp_json_encode($meta_value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $save_value = $meta_value;
        }

        $wpdb->insert($table, [
            'post_id'    => $object_id,
            'meta_key'   => $meta_key,
            'meta_value' => $save_value,
        ]);

        self::flushPostCache($object_id);
        return true;
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

    public static function deletePostMeta(int $post_id): void {
        $table = self::getTable($post_id);
        if ($table) {
            global $wpdb;
            $wpdb->delete($table, ['post_id' => $post_id]);
        }
        self::flushPostCache($post_id);
    }

    // ==================== META_QUERY 10/10 (FULL WP SUPPORT) ====================
    public static function filterPostsClauses(array $clauses, WP_Query $query): array {
        $post_type = $query->get('post_type');
        if (is_array($post_type)) $post_type = $post_type[0] ?? '';
        if (!$post_type || !in_array($post_type, self::$registered)) return $clauses;

        $meta_query = $query->get('meta_query');
        if (empty($meta_query)) return $clauses;

        global $wpdb;
        $table = self::getTableName($post_type);

        $mq = new WP_Meta_Query($meta_query);
        $sql = $mq->get_sql('post', $wpdb->posts, 'ID');

        if (!empty($sql['join'])) {
            $sql['join'] = str_replace($wpdb->postmeta, $table, $sql['join']);
            $clauses['join'] .= $sql['join'];
        }
        if (!empty($sql['where'])) {
            $sql['where'] = str_replace($wpdb->postmeta, $table, $sql['where']);
            $clauses['where'] .= $sql['where'];
        }

        // Group by tránh duplicate khi JOIN
        $clauses['groupby'] = $wpdb->posts . '.ID';

        return $clauses;
    }

    public static function filterOrderByMeta(string $orderby, WP_Query $query): string {
        $post_type = $query->get('post_type');
        if (is_array($post_type)) $post_type = $post_type[0] ?? '';
        if (!$post_type || !in_array($post_type, self::$registered)) return $orderby;

        $meta_key = $query->get('meta_key');
        if (!$meta_key) return $orderby;

        global $wpdb;
        $table = self::getTableName($post_type);
        $order = strtoupper($query->get('order')) === 'ASC' ? 'ASC' : 'DESC';

        return "MAX(CASE WHEN {$table}.meta_key = '{$meta_key}' THEN {$table}.meta_value END) {$order}";
    }

    // ==================== TẠO BẢNG + INDEX TỐI ƯU ====================
    public static function createMissingTables(): void {
        global $wpdb;
        foreach (self::$registered as $pt) {
            $table = self::getTableName($pt);
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
            KEY `post_id_meta_key` (`post_id`, `meta_key`(191)),
            KEY `meta_key_value` (`meta_key`(191), `meta_value`(191))
        ) $charset_collate;";

        dbDelta($sql);
        self::$table_exists[$table_name] = true;
        error_log("✅ [CustomTable 10/10] Đã tạo/tối ưu bảng: {$table_name}");
    }

    // Helper siêu tiện (dùng trong Blade hoặc controller)
    public static function query(array $args = []): WP_Query {
        if (isset($args['post_type']) && in_array($args['post_type'], self::$registered)) {
            $args['suppress_filters'] = false;
        }
        return new WP_Query($args);
    }    
}