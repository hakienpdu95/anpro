<?php

namespace App\Helpers;

class QueryCache
{
    private static bool $debug = false;

    public static function init(): void
    {
        self::$debug = defined('WP_DEBUG') && WP_DEBUG;
        if (self::$debug) {
            error_log('🚀 [QueryCache 11/10] Initialized');
        }
    }

    public static function remember(string $queryName, array $args, int $ttl = 300, callable $callback)
    {
        $start = microtime(true);

        $post_type = $args[0] ?? 'global';
        $isHome = is_home() || is_front_page();
        $context = $isHome ? 'home' : get_query_var('paged', 1);

        ksort($args); // Sắp xếp để key luôn giống nhau

        $key = $post_type . '_query_' . $queryName . '_' . md5(json_encode($args) . $context);

        $result = CacheHelper::remember($key, $ttl, $callback);

        $time = round((microtime(true) - $start) * 1000, 2);

        if (self::$debug) {
            error_log("🔍 QUERY CACHE | {$queryName} | {$time}ms | TTL {$ttl}s | PostType: {$post_type} | Context: {$context}");
        }

        return $result;
    }

    public static function getPostsWithAllFlags(string $post_type, array $flags, int $posts_per_page = 8, int $ttl = 300)
    {
        return self::remember('getPostsWithAllFlags', [$post_type, $flags, $posts_per_page], $ttl, function () use ($post_type, $flags, $posts_per_page) {
            return QueryHelper::getPostsWithAllFlags($post_type, $flags, $posts_per_page);
        });
    }
}