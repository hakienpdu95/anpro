<?php
/**
 * Global Helpers – Chuẩn Sage 10/10
 * Load SIÊU SỚM → cmeta(), sage_get_files() có mặt ngay từ đầu
 */

if (!function_exists('cmeta')) {
    function cmeta(string $key = '', $post_id = null, array $args = []) {
        $post_id = $post_id ?? get_the_ID();
        $value = null;

        if (class_exists(\App\Database\CustomTableManager::class)) {
            $value = \App\Database\CustomTableManager::getMeta((int)$post_id, $key);
        } elseif (function_exists('rwmb_meta')) {
            $value = rwmb_meta($key, $args, $post_id);
        }

        // === FIX FLAGS: Luôn trả về array cho checkbox_list ===
        if ($key === 'flags' || str_contains($key, 'flag')) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
                return [$value]; // 'breaking' → ['breaking']
            }
            if (!is_array($value)) {
                return $value ? [$value] : [];
            }
        }

        return $value;
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