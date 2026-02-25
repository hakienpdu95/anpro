<?php

namespace App\Helpers;

class DataCache
{
    private static bool $debug = false;

    public static function init(): void
    {
        self::$debug = defined('WP_DEBUG') && WP_DEBUG;
        if (self::$debug) {
            error_log('🚀 [DataCache 11/10] Initialized - Global Data Cache (độc lập)');
        }
    }

    public static function remember(string $key, int $ttl, callable $callback)
    {
        $start = microtime(true);

        $result = CacheHelper::remember('data_' . $key, $ttl, $callback);

        $time = round((microtime(true) - $start) * 1000, 2);

        if (self::$debug) {
            error_log("🔍 [DATA CACHE] {$key} | {$time}ms | TTL {$ttl}s");
        }

        return $result;
    }
}