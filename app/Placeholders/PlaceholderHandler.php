<?php

namespace App\Placeholders;

use Illuminate\Support\Facades\Vite;

class PlaceholderHandler
{
    /** Cấu hình mạnh mẽ */
    private static array $config = [
        'media_id'     => 0,                    // ID ảnh Media Library (ưu tiên cao nhất)
        'default_file' => 'placeholder.png',
        'random_mode'  => false,                // Bật random nhiều ảnh
        'random_files' => ['placeholder.png'],  // Danh sách ảnh random
    ];

    /** Placeholder theo post type */
    private static array $postTypeMap = [
        'post'  => 'placeholder.png',
        'event' => 'placeholder.png',
    ];

    /** Cache siêu mạnh (per post & per file) */
    private static array $urlCache = [];
    private static array $viteCache = [];

    public static function init(): void
    {
        add_filter('post_thumbnail_html', [self::class, 'replaceWithPlaceholder'], 999, 5);
        add_filter('post_thumbnail_url', [self::class, 'replaceThumbnailUrl'], 999, 3);
    }

    /**
     * Lấy URL placeholder với cache cực mạnh
     */
    public static function getUrl(int $post_id): string
    {
        if (isset(self::$urlCache[$post_id])) {
            return self::$urlCache[$post_id];
        }

        $post_type = get_post_type($post_id);

        // 1. Ưu tiên Media Library (khuyến nghị mạnh nhất)
        if (!empty(self::$config['media_id'])) {
            $url = wp_get_attachment_url(self::$config['media_id']);
            if ($url) {
                return self::$urlCache[$post_id] = $url;
            }
        }

        // 2. Random nếu bật
        if (self::$config['random_mode'] && !empty(self::$config['random_files'])) {
            $file = self::$config['random_files'][array_rand(self::$config['random_files'])];
        } else {
            $file = self::$postTypeMap[$post_type] ?? self::$config['default_file'];
        }

        // 3. Vite asset với cache
        if (!isset(self::$viteCache[$file])) {
            self::$viteCache[$file] = Vite::asset("resources/images/{$file}");
        }

        return self::$urlCache[$post_id] = self::$viteCache[$file];
    }

    public static function replaceWithPlaceholder(string $html, int $post_id, $thumb_id, $size, $attr): string
    {
        $post_type = get_post_type($post_id);
        if (!isset(self::$postTypeMap[$post_type])) {
            return $html;
        }

        // Early return siêu nhanh (kiểm tra meta thumbnail thay vì has_post_thumbnail() để tránh query thừa)
        if ($html || get_post_meta($post_id, '_thumbnail_id', true)) {
            return $html;
        }

        $url = self::getUrl($post_id);

        // Xử lý $attr linh hoạt (string/array/null đều ok)
        $attr = is_array($attr) ? $attr : (is_string($attr) ? ['class' => $attr] : []);

        $size_str = is_array($size) ? 'custom' : (string)$size;
        $is_admin = is_admin();

        $default_attr = $is_admin 
            ? [
                'src'     => $url,
                'alt'     => esc_attr(get_the_title($post_id)),
                'class'   => 'placeholder-image object-cover rounded border border-gray-200',
                'width'   => '150',
                'height'  => '100',
              ]
            : [
                'src'     => $url,
                'alt'     => esc_attr(get_the_title($post_id) . ' - Ảnh đại diện'),
                'class'   => "attachment-{$size_str} size-{$size_str} wp-post-image placeholder-image w-full object-cover group-hover:scale-105 transition-transform",
                'loading' => 'lazy',
              ];

        $attr = wp_parse_args($attr, $default_attr);
        $attr = apply_filters('sage_placeholder_attributes', $attr, $post_id, $post_type);

        $html = '<img';
        foreach ($attr as $key => $value) {
            if ($value !== null && $value !== false) {
                $html .= ' ' . $key . '="' . esc_attr($value) . '"';
            }
        }
        $html .= ' />';

        return $html;
    }

    public static function replaceThumbnailUrl(?string $url, int $post_id, $size): ?string
    {
        if ($url || !isset(self::$postTypeMap[get_post_type($post_id)])) {
            return $url;
        }

        if (get_post_meta($post_id, '_thumbnail_id', true)) {
            return $url;
        }

        return self::getUrl($post_id);
    }

    // ====================== API SIÊU MẠNH ======================
    public static function useMediaImage(int $attachment_id): void
    {
        self::$config['media_id'] = $attachment_id;
    }

    public static function enableRandom(bool $enable = true): void
    {
        self::$config['random_mode'] = $enable;
    }

    public static function addPostType(string $post_type, string $image_file = 'placeholder.png'): void
    {
        self::$postTypeMap[$post_type] = $image_file;
    }

    public static function setDefault(string $file): void
    {
        self::$config['default_file'] = $file;
    }
}