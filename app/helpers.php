<?php

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

/**
 * Lấy Theme Option với cache (siêu nhanh)
 */
if (!function_exists('theme_option')) {
    function theme_option(string $key, $default = null)
    {
        return \App\CMB2\ThemeOptions::get($key, $default);
    }
}

if (!function_exists('tmeta')) {
    function tmeta(string $key, int $term_id = 0)
    {
        if ($term_id === 0) {
            $term = get_queried_object();
            $term_id = $term->term_id ?? 0;
        }
        return get_term_meta($term_id, $key, true);
    }
}

if (!function_exists('get_toc')) {
    function get_toc() {
        if (!is_singular()) return [];

        $content = get_post_field('post_content', get_the_ID());
        $headings = [];

        preg_match_all('/<h([2-4])([^>]*)id="([^"]+)"([^>]*)>(.*?)<\/h\1>/is', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $headings[] = [
                'level' => (int)$m[1],
                'id'    => $m[3],
                'text'  => wp_strip_all_tags($m[5])
            ];
        }

        return $headings;
    }
}