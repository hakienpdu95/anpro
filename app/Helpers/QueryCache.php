<?php

namespace App\Helpers;

class QueryCache
{
    private static bool $debug = false;

    public static function init(): void
    {
        self::$debug = defined('WP_DEBUG') && WP_DEBUG;
        if (self::$debug) error_log('🚀 [QueryCache 11/10] Initialized');
    }

    public static function getPostsWithAllFlags(string $post_type, array $flags, int $posts_per_page = 8, int $ttl = 300)
    {
        $start = microtime(true);
        $isHome = is_home() || is_front_page();
        $context = $isHome ? 'home' : get_query_var('paged', 1);

        ksort($flags);
        $flagsHash = md5(json_encode($flags) . $posts_per_page . $context);

        // === TỰ ĐỘNG LẤY VERSION - KHÔNG HARD CODE ===
        $version = CacheHelper::getDataVersion($post_type);

        $key = "getPostsWithAllFlags_{$post_type}_v{$version}_{$flagsHash}";

        $result = DataCache::remember($key, $ttl, function () use ($post_type, $flags, $posts_per_page) {
            return QueryHelper::getPostsWithAllFlags($post_type, $flags, $posts_per_page);
        });

        $time = round((microtime(true) - $start) * 1000, 2);
        if (self::$debug) {
            error_log("🔍 [QUERY CACHE] getPostsWithAllFlags | {$time}ms | v{$version} | PT:{$post_type}");
        }
        return $result;
    }
}