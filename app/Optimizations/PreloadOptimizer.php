<?php

namespace App\Optimizations;

use Illuminate\Support\Facades\Vite;

/**
 * PRELOAD OPTIMIZER 12/10
 *
 * - Fix hoàn toàn 2 warning preload Vite/Sage
 * - Sử dụng modulepreload cho JS (chuẩn ES Module)
 * - Thêm crossorigin="anonymous" cho mọi asset
 * - Chỉ preload critical entry points thực tế (không preload file không tồn tại)
 * - Configurable, modular, hiệu suất cao, early return
 */
class PreloadOptimizer
{
    private static array $config = [
        'enabled'          => true,
        'preload_css'      => ['resources/css/app.scss'],   // entry CSS chính của bạn
        'preload_js'       => ['resources/js/app.js'],      // entry JS chính
        'crossorigin'      => 'anonymous',
        'fetchpriority'    => 'high',
        'preconnect'       => [
            'https://fonts.googleapis.com',
            'https://fonts.gstatic.com',
        ],
        'favicon'          => 'public/build/images/favicon.ico', // thay nếu cần
    ];

    public static function init(): void
    {
        if (!self::config('enabled')) {
            return;
        }

        add_action('wp_head', [self::class, 'preloadCriticalAssets'], 1);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('🚀 [PreloadOptimizer 12/10] Initialized');
        }
    }

    private static function config(string $key, $default = null)
    {
        return self::$config[$key] ?? $default;
    }

    public static function setConfig(array $newConfig): void
    {
        self::$config = wp_parse_args($newConfig, self::$config);
    }

    public static function preloadCriticalAssets(): void
    {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        static $done = false;
        if ($done) return;
        $done = true;

        $preload = '';

        // 1. Preload CSS critical (preload + onload fallback)
        foreach (self::config('preload_css') as $entry) {
            try {
                $url = Vite::asset($entry);
                $preload .= sprintf(
                    '<link rel="preload" href="%s" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" crossorigin="%s" fetchpriority="%s">',
                    esc_url($url),
                    esc_attr(self::config('crossorigin')),
                    esc_attr(self::config('fetchpriority'))
                );
            } catch (\Exception $e) {}
        }

        // 2. Preload JS với modulepreload + crossorigin (fix 2 warning)
        foreach (self::config('preload_js') as $entry) {
            try {
                $url = Vite::asset($entry);
                $preload .= sprintf(
                    '<link rel="modulepreload" href="%s" crossorigin="%s" fetchpriority="%s">',
                    esc_url($url),
                    esc_attr(self::config('crossorigin')),
                    esc_attr(self::config('fetchpriority'))
                );
            } catch (\Exception $e) {}
        }

        // 3. Preconnect + DNS-Prefetch Google Fonts
        foreach (self::config('preconnect') as $url) {
            $preload .= sprintf('<link rel="preconnect" href="%s" crossorigin>', esc_url($url));
            $preload .= sprintf('<link rel="dns-prefetch" href="%s">', esc_url($url));
        }

        // 4. Favicon
        $ico = get_theme_file_uri(self::config('favicon'));
        $preload .= sprintf(
            '<link rel="icon" href="%s" type="image/x-icon">',
            esc_url($ico)
        );
        $preload .= sprintf(
            '<link rel="shortcut icon" href="%s" type="image/x-icon">',
            esc_url($ico)
        );

        echo $preload;
    }
}