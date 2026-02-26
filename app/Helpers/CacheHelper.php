<?php

namespace App\Helpers;

use Illuminate\Cache\Repository;
use Illuminate\Cache\FileStore;
use Illuminate\Filesystem\Filesystem;

class CacheHelper
{
    private static ?Repository $cache = null;
    private static array $memory = [];
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

        add_action('save_post', [self::class, 'flushOnPostSave'], 20, 2);
        add_action('deleted_post', [self::class, 'flushOnPostSave']);

        if (self::$debug) {
            $driver = wp_using_ext_object_cache() ? 'Redis' : 'File';
            error_log("🚀 [CacheHelper 10/10] Initialized - Driver: {$driver}");
        }
    }

    public static function remember(string $key, int $seconds, callable $callback): mixed
    {
        $fullKey = 'sage:' . $key;
        $start = microtime(true);

        if (isset(self::$memory[$fullKey])) {
            $time = round((microtime(true) - $start) * 1000, 2);
            if (self::$debug) error_log("⚡ MEMORY HIT → {$key} | {$time}ms");
            return self::$memory[$fullKey];
        }

        $result = self::$cache->remember($fullKey, $seconds, $callback);
        self::$memory[$fullKey] = $result;

        $time = round((microtime(true) - $start) * 1000, 2);
        if (self::$debug) error_log("📦 REDIS HIT → {$key} | {$time}ms | TTL {$seconds}s");

        return $result;
    }

    /**
     * DATA VERSIONING PER POST TYPE - TỰ ĐỘNG HOÀN TOÀN (10/10 scalable)
     */
    public static function getDataVersion(string $post_type): int
    {
        if (empty($post_type)) return 1;
        $key = 'sage:data_version:' . sanitize_key($post_type);
        return (int) self::$cache->get($key, 1);
    }

    public static function bumpDataVersion(string $post_type): void
    {
        if (empty($post_type)) return;

        $key = 'sage:data_version:' . sanitize_key($post_type);
        $current = self::getDataVersion($post_type);
        $newVersion = $current + 1;

        self::$cache->forever($key, $newVersion);   // forever = không expire
        self::$memory = [];                         // xóa memory layer

        if (self::$debug) {
            error_log("🔄 [CACHE VERSION] BUMP → {$post_type} | v{$newVersion}");
        }
    }

    public static function flushOnPostSave(int $post_id, $post = null): void
    {
        self::$memory = [];
        
        $post_type = is_object($post) ? $post->post_type : get_post_type($post_id);
        
        if ($post_type && in_array($post_type, \App\Database\CustomTableManager::$registered ?? [])) {
            self::bumpDataVersion($post_type);
        }

        // === BUMP CHO DANH SÁCH MERGED (post + event) ===
        if (in_array($post_type, ['post', 'event'])) {
            self::bumpDataVersion('content_list');
        }
        
        if (self::$debug) {
            error_log("🗑️ [CacheHelper] FLUSH → Post #{$post_id} ({$post_type})");
        }
    }
}