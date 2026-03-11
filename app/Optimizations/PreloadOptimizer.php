<?php

namespace App\Optimizations;

use Illuminate\Support\Facades\Vite;

/**
 * PRELOAD OPTIMIZER 12/10 – ĐÃ FIX HOÀN TOÀN FAVICON 404
 */
class PreloadOptimizer
{
    private static array $config = [
        'enabled'          => true,
        'preload_css'      => ['resources/css/app.scss'],
        'preload_js'       => ['resources/js/app.js'],
        'crossorigin'      => 'anonymous',
        'fetchpriority'    => 'high',

        // ==================== FAVICON – ĐÃ FIX 404 (KHOANH VÙNG QUAN TRỌNG) ====================
        'favicon_path'     => 'public/build/images/favicon.ico',   // ← Giữ nguyên logic code cũ của bạn
        'apple_touch'      => 'public/build/images/apple-touch-icon.png', // tùy chọn
        // ====================================================================================

        'preload_fonts' => [
            'public/build/fonts/Roboto-Regular.woff2' => 'font/woff2',
            'public/build/fonts/Roboto-Medium.woff2'  => 'font/woff2',
        ],
        'google_font_urls' => [],
        'preconnect' => [
            'https://fonts.googleapis.com',
            'https://fonts.gstatic.com',
        ],
    ];

    public static function init(): void
    {
        if (!self::config('enabled')) return;

        add_action('wp_head', [self::class, 'preloadCriticalAssets'], 1);
        
        // === FIX ROOT /favicon.ico 404 (redirect về file trong theme) ===
        add_action('template_redirect', [self::class, 'serveFaviconRedirect']);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('🚀 [PreloadOptimizer 12/10] Initialized + Favicon fixed');
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
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) return;

        static $done = false;
        if ($done) return;
        $done = true;

        $preload = '';

        // 1. Preload CSS
        try {
            $css_url = Vite::asset('resources/css/main.scss');
            $preload .= sprintf(
                '<link rel="preload" href="%s" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" crossorigin="%s" fetchpriority="%s">',
                esc_url($css_url),
                esc_attr(self::config('crossorigin')),
                esc_attr(self::config('fetchpriority'))
            );
        } catch (\Exception $e) {}

        // 2. Preload JS
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

        // ==================== FAVICON FIXED (KHOANH VÙNG) ====================
        $favicon_full_path = get_theme_file_path(self::config('favicon_path'));
        if (file_exists($favicon_full_path)) {
            $favicon_url = get_theme_file_uri(self::config('favicon_path'));
            $preload .= sprintf(
                '<link rel="icon" href="%s" type="image/x-icon" sizes="any">',
                esc_url($favicon_url)
            );
            $preload .= sprintf(
                '<link rel="shortcut icon" href="%s" type="image/x-icon">',
                esc_url($favicon_url)
            );
        }

        // Apple Touch Icon
        $apple_path = get_theme_file_path(self::config('apple_touch'));
        if (file_exists($apple_path)) {
            $apple_url = get_theme_file_uri(self::config('apple_touch'));
            $preload .= sprintf(
                '<link rel="apple-touch-icon" href="%s" sizes="180x180">',
                esc_url($apple_url)
            );
        }
        // =====================================================================

        // Preload Fonts + Google Fonts + Preconnect (giữ nguyên)
        foreach (self::config('preload_fonts') as $fontPath => $fontType) {
            try {
                $url = get_theme_file_uri($fontPath);
                $preload .= sprintf(
                    '<link rel="preload" href="%s" as="font" type="%s" crossorigin="anonymous" fetchpriority="high">',
                    esc_url($url),
                    esc_attr($fontType)
                );
            } catch (\Exception $e) {}
        }

        foreach (self::config('google_font_urls') as $googleUrl) {
            $preload .= sprintf('<link rel="preload" href="%s" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" crossorigin>', esc_url($googleUrl));
        }

        foreach (self::config('preconnect') as $url) {
            $preload .= sprintf('<link rel="preconnect" href="%s" crossorigin>', esc_url($url));
            $preload .= sprintf('<link rel="dns-prefetch" href="%s">', esc_url($url));
        }

        echo $preload;
    }

    /**
     * FIX ROOT /favicon.ico 404 – Redirect về file trong theme
     */
    public static function serveFaviconRedirect(): void
    {
        if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/favicon.ico') {
            $file = get_theme_file_path(self::config('favicon_path'));
            if (file_exists($file)) {
                header('Content-Type: image/x-icon');
                readfile($file);
                exit;
            }
        }
    }
}