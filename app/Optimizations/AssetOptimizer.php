<?php

namespace App\Optimizations;

use Illuminate\Support\Arr;

class AssetOptimizer
{
    private static array $config = [
        'defer' => ['alpine', 'splide', 'swiper', 'gsap', 'lazysizes', 'fancybox'],
        'async' => ['alpine', 'splide'],
        'exclude' => ['jquery', 'jquery-core', 'jquery-migrate', 'wp-polyfill', 'wp-emoji', 'heartbeat', 'wp-auth-check'],
        'enabled' => true,
    ];

    public static function init(): void
    {
        if (!self::config('enabled')) return;

        add_filter('script_loader_tag', [self::class, 'optimizeScriptTag'], 9999, 3);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('🚀 [AssetOptimizer 12/10] Initialized');
        }
    }

    private static function config(string $key, $default = null)
    {
        return Arr::get(self::$config, $key, $default);
    }

    public static function setConfig(array $newConfig): void
    {
        self::$config = wp_parse_args($newConfig, self::$config);
    }

    public static function optimizeScriptTag(string $tag, string $handle, string $src): string
    {
        if (is_admin() || empty($src) || strpos($tag, ' defer') !== false || strpos($tag, ' async') !== false) {
            return $tag;
        }

        if (wp_doing_ajax()) return $tag;

        if (self::shouldExclude($handle)) return $tag;

        // HỖ TRỢ VITE ES MODULE
        if (strpos($tag, 'type="module"') !== false) {
            return str_replace('<script ', '<script defer ', $tag); // defer cho module là chuẩn nhất
        }

        if (self::shouldAsync($handle)) {
            return str_replace('<script ', '<script async ', $tag);
        }

        if (self::shouldDefer($handle)) {
            return str_replace('<script ', '<script defer ', $tag);
        }

        return $tag;
    }

    private static function shouldExclude(string $handle): bool
    {
        foreach (self::config('exclude') as $exclude) {
            if (str_contains($handle, $exclude)) {
                return true;
            }
        }
        return false;
    }

    private static function shouldDefer(string $handle): bool
    {
        foreach (self::config('defer') as $item) {
            if (str_contains($handle, $item)) {
                return true;
            }
        }
        return false;
    }

    private static function shouldAsync(string $handle): bool
    {
        foreach (self::config('async') as $item) {
            if (str_contains($handle, $item)) {
                return true;
            }
        }
        return false;
    }
}