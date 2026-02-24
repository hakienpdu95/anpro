<?php

namespace App\Helpers;

class MetaHelper
{
    /**
     * Lấy meta từ custom table (siêu nhanh)
     */
    public static function get($key, $post_id = null)
    {
        global $wpdb;
        $post_id = $post_id ?? get_the_ID();
        $post_type = get_post_type($post_id);

        $table = \App\Database\CustomTableManager::getTableName($post_type);

        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT `$key` FROM `$table` WHERE post_id = %d LIMIT 1",
            $post_id
        ));

        return is_string($value) && ($decoded = json_decode($value, true)) !== null
            ? $decoded
            : $value;
    }
}