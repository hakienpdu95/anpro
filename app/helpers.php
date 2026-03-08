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

        }

        $output .= '</ul>';
        return $output;
    }

    /** 
     * =============================================== 
     * SAGE REDIRECT LINK SYSTEM – 10/10 ULTIMATE PERFORMANCE
     * Bulk prefetch + WP_Post object + native meta + max speed
     * =============================================== 
     */

    if (!function_exists('sage_prefetch_link_posts')) {
        /**
         * Gọi TRƯỚC loop để prefetch meta + permalink + title một lần duy nhất
         * → Giảm 70–90% queries khi hiển thị nhiều card
         */
        function sage_prefetch_link_posts(array $posts): void {
            if (empty($posts)) return;

            $ids = [];
            foreach ($posts as $p) {
                $ids[] = is_object($p) && isset($p->ID) ? $p->ID : (int) $p;
            }
            $ids = array_unique(array_filter($ids));

            if (empty($ids)) return;

            // Bulk meta prefetch (siêu mạnh!)
            update_postmeta_cache($ids);

            // Prime post cache (permalink + title)
            _prime_post_caches($ids, false, true);
        }
    }

    /** 
     * Lấy thông tin link (array) 
     */
    if (!function_exists('sage_post_link')) {
        function sage_post_link($post = 0, string $link_type = 'default'): array {
            static $cache = [];

            // Hỗ trợ cả WP_Post object lẫn ID
            if (is_object($post) && isset($post->ID)) {
                $post_id    = $post->ID;
                $post_title = $post->post_title;               // Nhanh nhất
                $post_obj   = $post;
            } else {
                $post_id = (int) ($post ?: get_the_ID());
                $post_title = '';
                $post_obj   = null;
            }

            if ($post_id <= 0) {
                return [
                    'url'          => '#',
                    'target'       => '',
                    'rel'          => '',
                    'is_external'  => false,
                    'link_type'    => $link_type,
                    'post_id'      => 0,
                    'post_title'   => '',
                    'is_redirect'  => false,
                ];
            }

            $cache_key = $post_id . '|' . $link_type;
            if (isset($cache[$cache_key])) {
                return $cache[$cache_key];
            }

            // Dùng get_post_meta native (nhanh hơn rwmb_meta)
            $is_redirect   = (bool) get_post_meta($post_id, 'is_redirect', true);
            $redirect_url  = get_post_meta($post_id, 'redirect_url', true);

            if ($is_redirect && !empty($redirect_url) && filter_var($redirect_url, FILTER_VALIDATE_URL)) {
                $data = [
                    'url'          => esc_url($redirect_url),
                    'target'       => '_blank',
                    'rel'          => 'noopener noreferrer',
                    'is_external'  => true,
                    'link_type'    => $link_type,
                    'post_id'      => $post_id,
                    'post_title'   => $post_title ?: get_the_title($post_id),
                    'is_redirect'  => true,
                ];
            } else {
                $data = [
                    'url'          => get_permalink($post_obj ?: $post_id),
                    'target'       => '',
                    'rel'          => '',
                    'is_external'  => false,
                    'link_type'    => $link_type,
                    'post_id'      => $post_id,
                    'post_title'   => $post_title ?: get_the_title($post_id),
                    'is_redirect'  => false,
                ];
            }

            return $cache[$cache_key] = $data;
        }
    }

    /** 
     * Helper sinh data attributes cho GTM 
     */
    if (!function_exists('sage_link_data_attrs')) {
        function sage_link_data_attrs(array $link): string {
            return sprintf(
                ' data-post-id="%d" data-post-title="%s" data-link-type="%s" data-is-external="%s" data-is-redirect="%s"',
                $link['post_id'] ?? 0,
                esc_attr($link['post_title'] ?? ''),
                esc_attr($link['link_type'] ?? 'default'),
                $link['is_external'] ? 'true' : 'false',
                $link['is_redirect'] ? 'true' : 'false'
            );
        }
    }

    /** 
     * Trả về toàn bộ thẻ <a> cho tiêu đề 
     */
    if (!function_exists('sage_post_title_link')) {
        function sage_post_title_link($post = 0, string $extra_class = '', string $link_type = 'default'): string {
            $link  = sage_post_link($post, $link_type);
            $title = get_the_title($post);
            $class = $extra_class ? ' class="' . esc_attr($extra_class) . '"' : '';

            return sprintf(
                '<a href="%s"%s%s%s%s>%s</a>',
                $link['url'],
                $link['target'] ? ' target="' . $link['target'] . '"' : '',
                $link['rel'] ? ' rel="' . $link['rel'] . '"' : '',
                $class,
                sage_link_data_attrs($link),
                esc_html($title)
            );
        }
    }

    /** 
     * Mở thẻ <a> bao quanh card/block 
     */
    if (!function_exists('sage_post_link_open')) {
        function sage_post_link_open($post = 0, string $extra_classes = '', string $link_type = 'default'): string {
            $link = sage_post_link($post, $link_type);
            $classes = 'group' . ($extra_classes ? ' ' . trim($extra_classes) : '');

            return '<a href="' . $link['url'] . '"' .
                   ($link['target'] ? ' target="' . $link['target'] . '"' : '') .
                   ($link['rel'] ? ' rel="' . $link['rel'] . '"' : '') .
                   sage_link_data_attrs($link) .
                   ' class="' . esc_attr($classes) . '">';
        }
    }

    /** 
     * Đóng thẻ </a> 
     */
    if (!function_exists('sage_post_link_close')) {
        function sage_post_link_close(): string {
            return '</a>';
        }
    }

    /**
     * LẤY FLAGS (giữ nguyên)
     */
    if (!function_exists('sage_get_flags')) {
        function sage_get_flags($post): array
        {
            $post_id = is_object($post) ? $post->ID : (int) $post;
            return $post_id > 0 
                ? \App\Database\CustomTableManager::getMeta($post_id, 'flags', false) 
                : [];
        }
    }

    /**
     * LẤY FLAG ƯU TIÊN NHẤT (chỉ 1 flag) – SIÊU NHANH
     */
    if (!function_exists('sage_get_primary_flag')) {
        function sage_get_primary_flag($post): string
        {
            $flags = sage_get_flags($post);
            if (empty($flags)) return '';

            // Kiểm tra field is_pinned trước (ưu tiên tuyệt đối)
            $is_pinned = \App\Database\CustomTableManager::getMeta($post->ID ?? $post, 'is_pinned');
            if ($is_pinned === '1' || $is_pinned === 1) {
                return 'pinned';
            }

            // Thang điểm ưu tiên
            $priority = [
                'pinned'         => 100,
                'breaking'       => 90,
                'hot'            => 80,
                'featured'       => 70,
                'sponsored'      => 60,
                'first-aid'      => 50,
                'diabetic'       => 45,
                'medical-device' => 40,
            ];

            $best_flag = '';
            $best_score = -1;

            foreach ($flags as $flag) {
                $score = $priority[$flag] ?? 10;   // flag lạ = 10 điểm
                if ($score > $best_score) {
                    $best_score = $score;
                    $best_flag  = $flag;
                }
            }

            return $best_flag;
        }
    }

    /**
     * BADGE (chỉ hiển thị 1 cái)
     */
    if (!function_exists('sage_flag_badge')) {
        function sage_flag_badge(string $flag): string
        {
            $badges = [
                'pinned'         => '<span class="absolute top-3 left-3 bg-blue-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow">📌 PINNED</span>',
                'breaking'       => '<span class="absolute top-3 left-3 bg-orange-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow">🚨 BREAKING</span>',
                'hot'            => '<span class="absolute top-3 left-3 bg-red-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow">🔥 HOT</span>',
                'featured'       => '<span class="absolute top-3 left-3 bg-purple-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow">⭐ FEATURED</span>',
                'sponsored'      => '<span class="absolute top-3 left-3 bg-emerald-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow">💰 SPONSORED</span>',
                'first-aid'      => '<span class="absolute top-3 left-3 bg-green-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow">🩹 FIRST AID</span>',
                'diabetic'       => '<span class="absolute top-3 left-3 bg-amber-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow">🩸 DIABETIC</span>',
                'medical-device' => '<span class="absolute top-3 left-3 bg-cyan-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow">🩺 MEDICAL</span>',
            ];

            return $badges[$flag] ?? '';
        }
    }    
    
    if (!function_exists('sage_views')) {
        function sage_views($post = null) {
            $id = $post ? (is_object($post) ? $post->ID : $post) : get_the_ID();
            return number_format(\App\Helpers\ViewCounter::getViews($id));
        }
    }

    if (!function_exists('sage_hot_badge')) {
        function sage_hot_badge($post = null) {
            $id = $post ? (is_object($post) ? $post->ID : $post) : get_the_ID();
            if (\App\Helpers\ViewCounter::isHot($id)) {
                return '<span class="absolute top-3 right-3 bg-red-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow">🔥 ĐANG HOT</span>';
            }
            return '';
        }
    }

    /**
     * RENDER 1 CỘT FOOTER MENU – Tự động lấy tên menu làm tiêu đề
     * ĐÃ FIX: wp_get_nav_menu_locations() → get_nav_menu_locations()
     */
    if (!function_exists('sage_footer_column')) {
        function sage_footer_column(string $location, string $fallback_title = ''): string {
            // Kiểm tra menu có tồn tại không
            if (!has_nav_menu($location)) {
                return '';
            }

            // Lấy menu locations một cách an toàn
            $locations = get_nav_menu_locations();
            $menu_id   = $locations[$location] ?? 0;

            $menu = wp_get_nav_menu_object($menu_id);
            $title = $menu ? $menu->name : $fallback_title;

            $output = '<div class="col-span-12 sm:col-span-2">';

            // Tiêu đề cột
            if ($title) {
                $output .= '<a href="#" class="block text-white font-semibold hover:text-emerald-400 mb-4">' 
                         . esc_html($title) 
                         . '</a>';
            }

            // Render menu
            $output .= wp_nav_menu([
                'theme_location' => $location,
                'container'      => false,
                'menu_class'     => 'space-y-2.5 text-sm footer-menu',
                'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
                'echo'           => false,
                'fallback_cb'    => false,
                'depth'          => 1,
            ]);

            $output .= '</div>';
            return $output;
        }
    }

    /**
     * SAGE THUMBNAIL – SRCSET + PLACEHOLDER CHẮC CHẮN 100% (v3)
     * Fix lỗi ảnh ghost + placeholder không hiện trong custom loop
     */
    if (!function_exists('sage_thumbnail')) {
        function sage_thumbnail(string $size = 'thumb-medium', array $attr = [], $post = null): string
        {
            $post = get_post($post);
            if (!$post) return '';

            $thumb_id = get_post_thumbnail_id($post->ID);

            $defaults = [
                'class'    => 'w-full h-full object-cover transition-transform duration-300',
                'loading'  => 'lazy',
                'decoding' => 'async',
                'alt'      => get_the_title($post),
            ];

            $attr = wp_parse_args($attr, $defaults);

            // === FORCE PLACEHOLDER khi không có ảnh thật ===
            if (!$thumb_id && class_exists('\App\Placeholders\PlaceholderHandler')) {
                return \App\Placeholders\PlaceholderHandler::replaceWithPlaceholder(
                    '', $post->ID, 0, $size, $attr
                );
            }

            // Có ảnh thật → trả về wp_get_attachment_image (có srcset đầy đủ)
            return wp_get_attachment_image($thumb_id, $size, false, $attr);
        }
    }   

    /**
     * SAGE AUTHOR LINK – Hiển thị tên tác giả + link đến author archive
     * Ưu tiên field 'custom_author' (từ CustomTableManager)
     * Fallback về tác giả WP mặc định
     */
    if (!function_exists('sage_post_author_link')) {
        function sage_post_author_link($post = null, string $extra_class = ''): string
        {
            $post = get_post($post);
            if (!$post) {
                return '<span class="text-primary-100">By Admin</span>';
            }

            // Ưu tiên custom_author (nếu có)
            $custom_author = cmeta('custom_author', $post->ID);
            if ($custom_author && is_string($custom_author) && trim($custom_author) !== '') {
                $author_name = trim($custom_author);
                $author_id   = $post->post_author; // vẫn dùng ID gốc để lấy link archive
            } else {
                $author_id   = $post->post_author;
                $author_name = get_the_author_meta('display_name', $author_id) ?: 'By Admin';
            }

            $author_url = get_author_posts_url($author_id);

            $class = $extra_class ? ' ' . trim($extra_class) : '';

            return sprintf(
                '<a href="%s" class="%s">%s</a>',
                esc_url($author_url),
                esc_attr($class),
                esc_html($author_name)
            );
        }
    }    

    /**
     * SAGE POST DATE – Linh hoạt cho mọi vị trí (slide, grid, content.blade.php)
     */
    if (!function_exists('sage_post_date')) {
        function sage_post_date($post = null, bool $use_modified = false, bool $raw = false, string $extra_class = ''): string
        {
            $post = get_post($post);
            if (!$post) {
                return $raw ? 'Không có ngày' : '<span class="text-light-secondary-text">Không có ngày</span>';
            }

            $format = 'd M Y'; // Giữ nguyên format bạn đang dùng: 12:40 PM, 09 Feb 2027

            $date = $use_modified
                ? get_the_modified_date($format, $post)
                : get_the_date($format, $post);

            $prefix = $use_modified ? '' : '';

            if ($raw) {
                // Trả về chỉ text (dùng cho content.blade.php)
                return $prefix . $date;
            }

            // Trả về <span> đầy đủ (dùng cho slide & grid)
            $class = $extra_class ? ' ' . trim($extra_class) : '';
            return sprintf(
                '<span class="sm:text-base sm:leading-[27px] text-sm text-primary-100%s">%s%s</span>',
                esc_attr($class),
                $prefix,
                esc_html($date)
            );
        }
    }

    /**
     * LẤY BANNER SIDEBAR WIDGET – ĐÃ CHỈNH SỬA THEO YÊU CẦU MỚI
     * - Trang chủ          → Luôn Theme Options
     * - Single Post/Event  → Taxonomy của bài viết → Theme Options
     * - Archive Category   → Taxonomy → Theme Options
     */
    if (!function_exists('sage_get_sidebar_banner')) {
        function sage_get_sidebar_banner(int $block = 1): string
        {
            if (!in_array($block, [1, 2])) return '';

            $option_key   = 'widget_block_' . $block;
            $tax_meta_key = 'banner_' . $block;

            $data = null;

            // ==================== 1. SINGLE POST / EVENT ====================
            if (is_singular(['post', 'event'])) {
                // Lấy taxonomy tương ứng của bài viết
                $taxonomy = (get_post_type() === 'post') ? 'category' : 'event-categories';
                $terms    = get_the_terms(get_the_ID(), $taxonomy);
                $term     = (!empty($terms) && !is_wp_error($terms)) ? reset($terms) : null;

                if ($term) {
                    $tax = get_term_meta($term->term_id, $tax_meta_key, true);
                    $tax = maybe_unserialize($tax);

                    if (isset($tax[0]) && is_array($tax[0])) {
                        $tax = $tax[0];
                    }

                    if (!empty($tax['image_id']) || !empty($tax['image'])) {
                        $data = $tax;
                    }
                }
            }

            // ==================== 2. ARCHIVE CATEGORY / TAXONOMY ====================
            elseif (is_category() || is_tax('event-categories')) {
                $term = get_queried_object();
                if ($term && !is_wp_error($term)) {
                    $tax = get_term_meta($term->term_id, $tax_meta_key, true);
                    $tax = maybe_unserialize($tax);

                    if (isset($tax[0]) && is_array($tax[0])) {
                        $tax = $tax[0];
                    }

                    if (!empty($tax['image_id']) || !empty($tax['image'])) {
                        $data = $tax;
                    }
                }
            }

            // ==================== 3. TRANG CHỦ + FALLBACK ====================
            if (empty($data['image_id']) && empty($data['image'])) {
                $options = get_option('theme_options', []);
                $data    = $options[$option_key] ?? null;

                if (isset($data[0]) && is_array($data[0])) {
                    $data = $data[0];
                }
            }

            // ==================== XỬ LÝ ẢNH & TRẢ VỀ HTML ====================
            $image_id  = $data['image_id'] ?? 0;
            $image_url = $image_id ? wp_get_attachment_url($image_id) : ($data['image'] ?? '');

            if (empty($image_url)) {
                return '';
            }

            $title   = $data['title'] ?? '';
            $link    = $data['link'] ?? '#';
            $new_tab = !empty($data['new_tab']) ? ' target="_blank" rel="noopener"' : '';

            return sprintf(
                '<div class="widget-banner mb-5">
                    <a href="%s"%s>
                        <img src="%s" alt="%s" class="w-full">
                    </a>
                </div>',
                esc_url($link),
                $new_tab,
                esc_url($image_url),
                esc_attr($title)
            );
        }
    }

    /**
     * LẤY CHÍNH XÁC TRƯỜNG EXCERPT THỦ CÔNG
     * Hỗ trợ truyền: không truyền, ID, hoặc WP_Post object
     */
    if (!function_exists('sage_excerpt')) {
        function sage_excerpt($post = null, bool $fallback_to_content = false, int $words = 55): string
        {
            $post = get_post($post); // Hỗ trợ cả ID, WP_Post, hoặc null (current post)

            if (!$post) {
                return '';
            }

            // Lấy Excerpt thủ công (post_excerpt)
            $excerpt = trim($post->post_excerpt ?? '');

            // Nếu có excerpt thủ công → trả về luôn (ưu tiên cao nhất)
            if (!empty($excerpt)) {
                return $excerpt;
            }

            // Nếu không có excerpt và bạn cho phép fallback
            if ($fallback_to_content) {
                $content = get_the_content(null, false, $post);
                return wp_trim_words($content, $words, '...');
            }

            // Mặc định: không có excerpt thủ công thì trả về rỗng
            return '';
        }
    }
}