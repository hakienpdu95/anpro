<?php

namespace App\Permalinks;

class PermalinkManager
{
    private static array $post_types = ['post', 'event'];

    public static function init(): void
    {
        add_filter('wp_insert_post_data', [self::class, 'forcePostSlugWithID'], 99, 2);
        add_action('template_redirect', [self::class, 'redirectOldSlug'], 1);
    }

    public static function addPostType(string $post_type): void
    {
        $post_type = sanitize_key($post_type);
        if (!in_array($post_type, self::$post_types, true)) {
            self::$post_types[] = $post_type;
        }
    }

    /**
     * FORCE SLUG 10/10 – Xóa sạch mọi biến thể postID dù user sửa thủ công
     */
    public static function forcePostSlugWithID(array $data, array $postarr): array
    {
        $post_type = $data['post_type'] ?? '';
        if (!in_array($post_type, self::$post_types, true)) {
            return $data;
        }

        if ($data['post_status'] !== 'publish' || empty($data['post_title'])) {
            return $data;
        }

        $post_id = (int) ($postarr['ID'] ?? 0);
        if ($post_id <= 0) return $data;

        $user_slug = $data['post_name'] ?? sanitize_title($data['post_title']);

        // === FIX MẠNH: XÓA TẤT CẢ CÁC PHẦN CHỨA "post" + số (có hoặc không có dấu -) ===
        $clean_slug = preg_replace('/-?post\d+/i', '', $user_slug);

        // Xóa nhiều dấu gạch ngang liên tiếp và trim
        $clean_slug = preg_replace('/-+/', '-', trim($clean_slug, '- '));

        // Nếu bị xóa sạch → fallback về slug từ title
        if (empty($clean_slug)) {
            $clean_slug = sanitize_title($data['post_title']);
        }

        $desired_slug = $clean_slug . '-post' . $post_id;

        if ($user_slug !== $desired_slug) {
            $data['post_name'] = $desired_slug;

            // Thông báo cho người dùng
            if (is_admin()) {
                add_action('admin_notices', function () use ($user_slug, $desired_slug) {
                    echo '<div class="notice notice-warning is-dismissible">
                            <p><strong>Permalink đã được tự động điều chỉnh:</strong><br>
                            Từ <code>' . esc_html($user_slug) . '</code> → <code>' . esc_html($desired_slug) . '</code></p>
                          </div>';
                });
            }
        }

        return $data;
    }

    public static function redirectOldSlug(): void
    {
        if (!is_singular(self::$post_types)) return;

        $post = get_queried_object();
        if (!$post || empty($post->post_name)) return;

        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        $correct_slug = $post->post_name;

        if (strpos($current_url, $correct_slug) === false) {
            $new_url = home_url('/' . $correct_slug . '/');
            wp_redirect($new_url, 301);
            exit;
        }
    }
}