<?php

namespace App\Optimizations;

use Illuminate\Support\Arr;

class PerformanceOptimizer
{
    private static array $config = [
        'disable_emoji'          => true,
        'disable_oembed'         => true,
        'disable_resource_hints' => true,
        'disable_rest_links'     => true,
        'disable_xmlrpc'         => true,
        'disable_pingbacks'      => true,
        'remove_query_string'    => true,
        'heartbeat'              => [
            'frontend'       => 'disable',
            'admin_interval' => 60,
        ],
    ];

    public static function init(): void
    {
        add_action('init', [self::class, 'applyOptimizations'], 9999);
        add_filter('heartbeat_settings', [self::class, 'optimizeHeartbeat'], 999);

        if (self::config('remove_query_string')) {
            self::removeQueryStrings();
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('🚀 [PerformanceOptimizer 12/10] Initialized');
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

    public static function applyOptimizations(): void
    {
        if (self::config('disable_emoji')) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('wp_print_styles', 'print_emoji_styles');
        }
        if (self::config('disable_oembed')) {
            remove_action('wp_head', 'wp_oembed_add_discovery_links');
        }
        if (self::config('disable_resource_hints')) {
            remove_action('wp_head', 'wp_resource_hints', 2);
        }
        if (self::config('disable_rest_links')) {
            remove_action('wp_head', 'rest_output_link_wp_head', 10);
            remove_action('wp_head', 'wp_oembed_add_host_js');
        }
        if (self::config('disable_xmlrpc')) {
            add_filter('xmlrpc_enabled', '__return_false');
            remove_action('wp_head', 'rsd_link');
            remove_action('wp_head', 'wlwmanifest_link');
        }
        if (self::config('disable_pingbacks')) {
            add_filter('pings_open', '__return_false', 999);
            add_filter('wp_headers', function ($headers) {
                unset($headers['X-Pingback']);
                return $headers;
            });
        }

        if (self::config('heartbeat.frontend') === 'disable') {
            if (!is_admin() && !wp_doing_ajax() && !wp_doing_cron()) {
                wp_dequeue_script('heartbeat');
                wp_deregister_script('heartbeat');
            }
        }
    }

    public static function optimizeHeartbeat(array $settings): array
    {
        if (is_admin()) {
            $settings['interval'] = self::config('heartbeat.admin_interval', 60);
        }
        return $settings;
    }

    private static function removeQueryStrings(): void
    {
        add_filter('script_loader_src', [self::class, 'stripVer'], 15);
        add_filter('style_loader_src', [self::class, 'stripVer'], 15);
    }

    public static function stripVer(string $src): string
    {
        if (strpos($src, '?ver=') !== false) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }
}