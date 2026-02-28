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

if (!function_exists('sage_menu')) {
    function sage_menu(string $location, array $args = []): string
    {
        $defaults = [
            'theme_location' => $location,
            'container'      => false,
            'echo'           => false,
            'fallback_cb'    => false,
        ];
        return wp_nav_menu(array_merge($defaults, $args));
    }
}

/**
 * Social Icons 
 */
if (!function_exists('sage_social_icons')) {
    function sage_social_icons(
        string $location = 'social_navigation',
        string $wrapper_class = 'flex items-center gap-6 text-2xl',
        array $custom_icons = []
    ): string {
        $items = wp_get_nav_menu_items($location);
        if (empty($items)) {
            return '';
        }

        // Icon map mặc định (dễ override qua filter)
        $icon_map = apply_filters('sage/social_icons/map', [
            'facebook'  => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>',
            'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.849.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zm0 10.162a3.999 3.999 0 110-7.998 3.999 3.999 0 010 7.998zm6.406-11.845a1.44 1.44 0 11-2.88 0 1.44 1.44 0 012.88 0z"/></svg>',
            'youtube'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.5 6.186C0 8.07 0 12 0 12s0 3.93.5 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.377.505 9.377.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
            'tiktok'    => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.228C19.59 5.14 18.72 4.27 17.63 4.27H6.37C5.28 4.27 4.41 5.14 4.41 6.23v11.54c0 1.09.87 1.96 1.96 1.96h11.26c1.09 0 1.96-.87 1.96-1.96V6.23z"/><path d="M15.5 12.5v-1.5h-1.5v1.5H15.5zM10.5 15.5V9h1.5v6.5H10.5z"/></svg>',
            'x.com'     => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25l-4.244 5.38L7.5 2.25H2.25l6.188 8.25-6.188 8.25H7.5l4.5-5.7 4.5 5.7h5.25L13.5 10.5 19.5 2.25z"/></svg>',
            'linkedin'  => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h14m-.5 15.5v-5.3a3.26 3.26 0 00-3.26-3.26c-.85 0-1.64.32-2.23.88v-.88h-2.5v9.5h2.5v-5.3c0-.8.65-1.45 1.45-1.45s1.45.65 1.45 1.45v5.3h2.5zM6.88 8.56a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM5.25 19.5h2.5v-9.5h-2.5v9.5z"/></svg>',
            'zalo'      => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.59 2 12.25c0 3.1 1.5 5.85 3.85 7.6L4 22l2.3-1.35c1.1.3 2.25.45 3.45.45 5.52 0 10-4.59 10-10.25S17.52 2 12 2zm3.5 8.5h-1.5v-1.5h1.5v1.5zm-7 0H7v-1.5h1.5v1.5z"/></svg>',
            // Thêm mạng mới ở đây nếu muốn default
        ]);

        // Merge custom icons nếu truyền trực tiếp
        if (!empty($custom_icons)) {
            $icon_map = array_merge($icon_map, $custom_icons);
        }

        $output = '<ul class="' . esc_attr($wrapper_class) . '">';

        foreach ($items as $item) {
            $url   = $item->url;
            $title = $item->title ?: $item->post_title;
            $classes = (array) $item->classes;

            $icon_key = '';
            $icon_svg = '';

            // Ưu tiên 1: CSS class (social-facebook, social-zalo...)
            foreach ($classes as $class) {
                if (str_starts_with($class, 'social-')) {
                    $icon_key = substr($class, 7);
                    break;
                }
            }

            // Ưu tiên 2: Domain
            if (!$icon_key) {
                $host = parse_url($url, PHP_URL_HOST) ?? '';
                foreach (array_keys($icon_map) as $key) {
                    if (stripos($host, $key) !== false) {
                        $icon_key = $key;
                        break;
                    }
                }
            }

            // Ưu tiên 3: Tiêu đề
            if (!$icon_key) {
                foreach (array_keys($icon_map) as $key) {
                    if (stripos($title, $key) !== false) {
                        $icon_key = $key;
                        break;
                    }
                }
            }

            $icon_svg = $icon_map[$icon_key] ?? '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>';

            $output .= sprintf(
                '<li><a href="%s" target="_blank" rel="noopener noreferrer" class="hover:scale-110 transition-transform" title="%s" aria-label="%s">%s</a></li>',
                esc_url($url),
                esc_attr($title),
                esc_attr($title),
                $icon_svg
            );
        }

        $output .= '</ul>';
        return $output;
    }

    /**
     * ===============================================
     * SAGE REDIRECT LINK SYSTEM – 10/10
     * Static cache + tái sử dụng toàn site + hiệu suất cực cao
     * ===============================================
     */

    /**
     * Lấy thông tin link (array)
     */
    if (!function_exists('sage_post_link')) {
        function sage_post_link($post = 0): array
        {
            static $cache = [];

            $post_id = is_object($post) ? $post->ID : ($post ?: get_the_ID());
            if ($post_id <= 0) {
                return ['url' => '#', 'target' => '', 'rel' => '', 'is_external' => false];
            }

            if (isset($cache[$post_id])) {
                return $cache[$post_id];
            }

            $is_redirect  = (bool) rwmb_meta('is_redirect', [], $post_id);
            $redirect_url = rwmb_meta('redirect_url', [], $post_id);

            if ($is_redirect && !empty($redirect_url) && filter_var($redirect_url, FILTER_VALIDATE_URL)) {
                $data = [
                    'url'         => esc_url($redirect_url),
                    'target'      => '_blank',
                    'rel'         => 'noopener noreferrer',
                    'is_external' => true,
                ];
            } else {
                $data = [
                    'url'         => get_permalink($post_id),
                    'target'      => '',
                    'rel'         => '',
                    'is_external' => false,
                ];
            }

            return $cache[$post_id] = $data;
        }
    }

    /**
     * Trả về toàn bộ thẻ <a> cho tiêu đề (dùng nhanh)
     */
    if (!function_exists('sage_post_title_link')) {
        function sage_post_title_link($post = 0, string $extra_class = ''): string
        {
            $link = sage_post_link($post);
            $title = get_the_title($post);
            $class = $extra_class ? ' class="' . esc_attr($extra_class) . '"' : '';

            return sprintf(
                '<a href="%s"%s%s%s>%s</a>',
                $link['url'],
                $link['target'] ? ' target="' . $link['target'] . '"' : '',
                $link['rel'] ? ' rel="' . $link['rel'] . '"' : '',
                $class,
                esc_html($title)
            );
        }
    }

    /**
     * Mở thẻ <a> bao quanh toàn bộ card/block
     */
    if (!function_exists('sage_post_link_open')) {
        function sage_post_link_open($post = 0, string $extra_classes = ''): string
        {
            $link = sage_post_link($post);
            $classes = 'block w-full h-full group' . ($extra_classes ? ' ' . trim($extra_classes) : '');

            return '<a href="' . $link['url'] . '"' 
                . ($link['target'] ? ' target="' . $link['target'] . '"' : '')
                . ($link['rel'] ? ' rel="' . $link['rel'] . '"' : '')
                . ' class="' . esc_attr($classes) . '">';
        }
    }

    /**
     * Đóng thẻ </a>
     */
    if (!function_exists('sage_post_link_close')) {
        function sage_post_link_close(): string
        {
            return '</a>';
        }
    }
}