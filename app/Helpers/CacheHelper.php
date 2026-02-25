<?php

namespace App\Helpers;

use Illuminate\Cache\Repository;
use Illuminate\Cache\FileStore;
use Illuminate\Filesystem\Filesystem;

class CacheHelper
{
    private static ?Repository $cache = null;
    private static array $memory = [];           // Layer siêu nhanh trong cùng request
    private static string $version = 'v1';       // Tăng version này khi muốn xóa hết cache
    private static bool $debug = false;

    public static function init(): void
    {
        self::$debug = defined('WP_DEBUG') && WP_DEBUG;

        if (function_exists('wp_using_ext_object_cache') && wp_using_ext_object_cache()) {
            self::$cache = \Illuminate\Support\Facades\Cache::store();
        } else {
            $path = wp_upload_dir()['basedir'] . '/sage-cache';
            wp_mkdir_p($path);
            self::$cache = new Repository(new FileStore(new Filesystem(), $path));
        }

        // Tự động flush khi save/xóa bài
        add_action('save_post', [self::class, 'flushOnPostSave'], 20, 2);
        add_action('deleted_post', [self::class, 'flushOnPostSave']);

        if (self::$debug) {
            $driver = wp_using_ext_object_cache() ? 'Redis Object Cache' : 'File Cache';
            error_log("🚀 [CacheHelper 110%] Initialized - Driver: {$driver}");
        }
    }

    /**
     * Cache siêu nhanh + log chi tiết
     */
    public static function remember(string $key, int $seconds, callable $callback)
    {
        $fullKey = 'sage_' . self::$version . ':' . $key;
        $start   = microtime(true);

        // 1. In-memory layer (nhanh nhất)
        if (isset(self::$memory[$fullKey])) {
            $time = round((microtime(true) - $start) * 1000, 2);
            if (self::$debug) error_log("⚡ MEMORY HIT → {$key} | {$time}ms");
            return self::$memory[$fullKey];
        }

        // 2. Redis / File cache
        $result = self::$cache->remember($fullKey, $seconds, $callback);

        self::$memory[$fullKey] = $result;   // Lưu vào memory cho request này

        $time = round((microtime(true) - $start) * 1000, 2);
        if (self::$debug) {
            error_log("📦 CACHE HIT → {$key} | {$time}ms | TTL {$seconds}s");
        }

        return $result;
    }

    public static function flushOnPostSave(int $post_id, $post = null): void
    {
        // Tăng version → tất cả cache cũ tự động invalid
        self::$version = 'v' . time();
        self::$memory = [];

        if (self::$debug) {
            $type = get_post_type($post_id) ?: 'unknown';
            error_log("🗑️  FLUSH CACHE → Post #{$post_id} ({$type}) saved → New version: " . self::$version);
        }
    }

    // Helper tiện ích
    public static function flushAll(): void
    {
        self::$version = 'v' . time();
        self::$memory = [];
        self::$cache->flush();
        if (self::$debug) error_log('🧹 FULL CACHE FLUSHED');
    }
}