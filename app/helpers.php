<?php
/**
 * Global Helpers cho Blade – Chuẩn Sage + Acorn
 * Load siêu sớm → cmeta() luôn có mặt
 */

if (!function_exists('cmeta')) {
    /**
     * Lấy meta siêu nhanh & an toàn nhất
     */
    function cmeta(string $key, $post_id = null, array $args = []) {
        $post_id = $post_id ?? get_the_ID();

        // Ưu tiên CustomTableManager (cache + meta_query)
        if (class_exists(\App\Database\CustomTableManager::class)) {
            return \App\Database\CustomTableManager::getMeta((int)$post_id, $key);
        }

        // Fallback an toàn
        return function_exists('rwmb_meta') 
            ? rwmb_meta($key, $args, $post_id) 
            : '';
    }
}