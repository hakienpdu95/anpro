<?php

namespace App\Permalinks;

class PermalinkManager
{
    /** Danh sách post type áp dụng (có thể thêm động) */
    private static array $post_types = ['post', 'event'];

    /** Cache để tránh xử lý lặp lại trong cùng request */
    private static array $processed = [];

    public static function init(): void
    {
        // Filter mạnh & hiệu suất cao nhất
        add_filter('wp_insert_post_data', [self::class, 'forcePostSlugWithID'], 99, 2);

        // Redirect 301 slug cũ → slug mới
        add_action('template_redirect', [self::class, 'redirectOldSlug'], 1);
    }

    /**
     * Thêm CPT mới (gọi ở setup.php)
     */
    public static function addPostType(string $post_type): void
    {
        $post_type = sanitize_key($post_type);
        if (!in_array($post_type, self::$post_types, true)) {
            self::$post_types[] = $post_type;
        }
    }

    /**
     * FORCE SLUG 10/10 – Luôn chỉ có đúng 1 lần -post{ID} ở cuối
     */
    public static function forcePostSlugWithID(array $data, array $postarr): array
    {
        $post_type = $data['post_type'] ?? '';
        if (!in_array($post_type, self::$post_types, true)) {
            return $data;
        }

        // Early return tối ưu hiệu suất
        if ($data['post_status'] !== 'publish' 
            || empty($data['post_title']) 
            || isset($data['post_name']) && strpos($data['post_name'], 'revision') !== false) {
            return $data;
        }

        $post_id = (int) ($postarr['ID'] ?? 0);
        if ($post_id <= 0) {
            return $data;
        }

        $cache_key = $post_id;
        if (isset(self::$processed[$cache_key])) {
            return $data;
        }

        $user_slug = $data['post_name'] ?? sanitize_title($data['post_title']);

        $desired_slug = self::cleanAndAppendID($user_slug, $post_id);

        // Chỉ cập nhật khi thực sự thay đổi
        if ($user_slug !== $desired_slug) {
            $data['post_name'] = $desired_slug;

            // Thông báo cho admin (chỉ hiện 1 lần)
            if (is_admin()) {
                self::showAdminNotice($user_slug, $desired_slug);
            }
        }

        self::$processed[$cache_key] = true;
        return $data;
    }

    /**
     * Hàm riêng xử lý clean slug – dễ đọc & dễ mở rộng
     */
    private static function cleanAndAppendID(string $slug, int $post_id): string
    {
        // Xóa toàn bộ các phần chứa "post" + số (có hoặc không dấu gạch ngang)
        $clean = preg_replace('/-?post\d+/i', '', $slug);

        // Xóa nhiều dấu gạch ngang liên tiếp và trim
        $clean = preg_replace('/-+/', '-', trim($clean, '- '));

        // Fallback nếu bị xóa sạch
        if (empty($clean)) {
            $clean = sanitize_title($slug); // fallback từ title gốc
        }

        return $clean . '-post' . $post_id;
    }

    /**
     * Thông báo cho người dùng khi slug bị tự động sửa
     */
    private static function showAdminNotice(string $old_slug, string $new_slug): void
    {
        add_action('admin_notices', function () use ($old_slug, $new_slug) {
            printf(
                '<div class="notice notice-warning is-dismissible"><p><strong>Permalink đã được tự động điều chỉnh:</strong><br>Từ <code>%s</code> → <code>%s</code></p></div>',
                esc_html($old_slug),
                esc_html($new_slug)
            );
        });
    }

    /**
     * Redirect 301 slug cũ → slug mới
     */
    public static function redirectOldSlug(): void
    {
        if (!is_singular(self::$post_types)) {
            return;
        }

        $post = get_queried_object();
        if (!$post || empty($post->post_name)) {
            return;
        }

        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        $correct_slug = $post->post_name;

        if (strpos($current_url, $correct_slug) === false) {
            $new_url = home_url('/' . $correct_slug . '/');
            wp_redirect($new_url, 301);
            exit;
        }
    }
}