<?php
/**
 * Global Helpers – Chuẩn Sage 10/10
 * Load SIÊU SỚM → cmeta(), sage_get_files() có mặt ngay từ đầu
 */

if (!function_exists('cmeta')) {
    function cmeta(string $key = '', $post_id = null, array $args = []) {
        $post_id = $post_id ?? get_the_ID();
        if (class_exists(\App\Database\CustomTableManager::class)) {
            return \App\Database\CustomTableManager::getMeta((int)$post_id, $key);
        }
        return function_exists('rwmb_meta') ? rwmb_meta($key, $args, $post_id) : '';
    }
}

// Helper cache file list (static + transient) – DÙNG CHO AUTO REGISTER
if (!function_exists('sage_get_files')) {
    function sage_get_files(string $folder, string $exclude = ''): array {
        static $cache = [];
        $key = md5($folder . $exclude);
        if (isset($cache[$key])) return $cache[$key];
        if (!is_dir($folder)) return [];
        $files = glob($folder . '/*.php');
        if ($exclude) {
            $files = array_filter($files, fn($f) => basename($f) !== $exclude);
        }
        $cache[$key] = $files;
        return $files;
    }
}

// Helper bổ sung (tương lai scale)
if (!function_exists('cpost')) {
    function cpost($post_id = null) {
        return get_post($post_id ?? get_the_ID());
    }
}

if (!function_exists('cterm_meta')) {
    /**
     * Lấy Term Meta (Taxonomy Meta) siêu dễ – dùng với Meta Box
     * Ví dụ: cterm_meta('thumbnail_id'), cterm_meta('icon')
     */
    function cterm_meta(string $key, $term_id = null, array $args = []) {
        $term_id = $term_id ?? get_queried_object_id();
        if (!$term_id) return null;

        return rwmb_meta($key, ['object_type' => 'term'] + $args, $term_id);
    }
}