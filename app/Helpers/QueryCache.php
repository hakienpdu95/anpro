<?php

namespace App\Helpers;

class QueryCache
{
    private static bool $debug = false;

    public static function init(): void
    {
        self::$debug = defined('WP_DEBUG') && WP_DEBUG;
        if (self::$debug) {
            error_log('🚀 [QueryCache 110%] Initialized');
        }
    }

    public static function remember(string $queryName, array $args, int $ttl = 300, callable $callback)
    {
        $start = microtime(true);
        $key = self::generateKey($queryName, $args);

        $result = CacheHelper::remember($key, $ttl, $callback);

        $time = round((microtime(true) - $start) * 1000, 2);
        if (self::$debug) {
            error_log("QUERY CACHE | {$queryName} | {$time}ms | TTL {$ttl}s | Args: " . json_encode($args));
        }

        return $result;
    }

    private static function generateKey(string $name, array $args): string
    {
        $hash = md5(serialize($args) . get_query_var('paged', 1));
        return "query_{$name}_{$hash}";
    }

    // Helper cho query phổ biến
    public static function getPostsWithAllFlags(string $post_type, array $flags, int $posts_per_page = 8, int $ttl = 300)
    {
        return self::remember('getPostsWithAllFlags', [$post_type, $flags, $posts_per_page], $ttl, function () use ($post_type, $flags, $posts_per_page) {
            return QueryHelper::getPostsWithAllFlags($post_type, $flags, $posts_per_page);
        });
    }
}