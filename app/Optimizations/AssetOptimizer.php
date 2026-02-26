<?php

namespace App\Optimizations;

use Illuminate\Support\Arr;

/**
 * ASSET OPTIMIZER 12/10
 *
 * - Defer / Async tất cả JS frontend (tối ưu Core Web Vitals - FID, TBT)
 * - Configurable 100% theo handle/pattern
 * - Tự động tránh conflict với jQuery, WP core, admin
 * - Hỗ trợ cả defer + async (Splide/Alpine mặc định async)
 * - Early return + cache-friendly
 * - Debug rõ ràng khi WP_DEBUG
 */
class AssetOptimizer
{
    private static array $config = [
        // Handle hoặc pattern chứa → áp dụng defer
        'defer' => [
            'alpine',
            'splide',
            'swiper',
            'gsap',
            'lazysizes',
            'fancybox',
        ],

        // Handle hoặc pattern chứa → áp dụng async (ưu tiên cho lightweight libs)
        'async' => [
            'alpine',
            'splide',
        ],

        // Không bao giờ defer/async (critical hoặc gây lỗi)
        'exclude' => [
            'jquery',
            'jquery-core',
            'jquery-migrate',
            'wp-polyfill',
            'wp-emoji',
            'heartbeat',           // đã xử lý riêng
            'wp-auth-check',
        ],

        'enabled' => true,         // Tắt nhanh nếu cần debug
    ];

    public static function init(): void
    {
        if (!self::config('enabled')) {
            return;
        }

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
        // === EARLY RETURN TỐI ƯU ===
        if (is_admin() || empty($src) || strpos($tag, ' defer') !== false || strpos($tag, ' async') !== false) {
            return $tag;
        }

        // Không áp dụng cho script inline hoặc admin
        if (wp_doing_ajax() || strpos($tag, 'type="text/javascript"') === false) {
            return $tag;
        }

        // Check exclude
        if (self::shouldExclude($handle)) {
            return $tag;
        }

        // Async ưu tiên (nhẹ + non-blocking)
        if (self::shouldAsync($handle)) {
            return str_replace('<script ', '<script async ', $tag);
        }

        // Defer (chạy sau DOM parsed)
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