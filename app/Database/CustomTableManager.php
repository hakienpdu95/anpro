<?php

namespace App\Database;

class CustomTableManager
{
    private static array $registered = [];

    public static function register(string $post_type): void
    {
        $post_type = sanitize_key($post_type);
        if (!in_array($post_type, self::$registered)) {
            self::$registered[] = $post_type;
        }
    }

    public static function getTableName(string $post_type): string
    {
        global $wpdb;
        $slug = sanitize_key($post_type);
        return ($slug === 'post') 
            ? $wpdb->prefix . 'post_custom_meta' 
            : $wpdb->prefix . $slug . 'meta';
    }

    public static function init(): void
    {
        add_action('after_switch_theme', [self::class, 'createAllMissingTables'], 10);
        add_action('admin_init', [self::class, 'createAllMissingTables'], 5);
        add_action('save_post', [self::class, 'syncToCustomTable'], 30, 2);
    }

    public static function createAllMissingTables(): void
    {
        global $wpdb;

        foreach (self::$registered as $post_type) {
            $table = self::getTableName($post_type);

            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
                self::createTable($table);
            }
        }
    }

    private static function createTable(string $table_name): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
            `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
            `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
            `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
            PRIMARY KEY (`meta_id`),
            KEY `post_id` (`post_id`),
            KEY `meta_key` (`meta_key`),
            KEY `post_id_meta_key` (`post_id`, `meta_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;";

        dbDelta($sql);
        error_log("✅ [CustomTable] Đã tạo bảng: {$table_name}");
    }

    public static function syncToCustomTable($post_id, $post): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;
        if ($post_id <= 0) return;

        $post_type = $post->post_type ?? get_post_type($post_id);
        if (!in_array($post_type, self::$registered)) return;

        global $wpdb;
        $table = self::getTableName($post_type);

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) return;

        $wpdb->delete($table, ['post_id' => $post_id]);

        $meta = rwmb_meta('', [], $post_id);
        if (empty($meta)) return;

        $inserts = [];
        foreach ($meta as $key => $value) {
            $inserts[] = [
                'post_id'    => $post_id,
                'meta_key'   => $key,
                'meta_value' => is_array($value) ? wp_json_encode($value, JSON_UNESCAPED_UNICODE) : $value,
            ];
        }

        foreach ($inserts as $data) {
            $wpdb->insert($table, $data);
        }
    }
}