<?php

namespace App\Helpers;

use voku\helper\HtmlMin;

class HtmlMinifier
{
    private static ?HtmlMin $minifier = null;
    private static bool $enabled = true;

    public static function init(): void
    {
        $isDebug = defined('WP_DEBUG') && WP_DEBUG;
        self::$enabled = !$isDebug;

        // FORCE BẬT để test ngay (rất tiện)
        if (isset($_GET['force_minify']) || defined('FORCE_HTML_MINIFY') && FORCE_HTML_MINIFY) {
            self::$enabled = true;
        }

        if (!self::$enabled) {
            if ($isDebug) {
                error_log('🔧 [HtmlMinifier] Tạm tắt vì đang DEBUG mode (dùng ?force_minify=1 để bật)');
            }
            return;
        }

        self::$minifier = new HtmlMin();

        self::safeSetOption('doOptimizeViaHtmlDomParser', true);
        self::safeSetOption('doRemoveComments', true);
        self::safeSetOption('doSumUpWhitespace', true);
        self::safeSetOption('doRemoveWhitespaceAroundTags', true);

        // Disable options unsafe for AlpineJS / SplideJS
        self::safeSetOption('doOptimizeAttributes', false);
        self::safeSetOption('doSortHtmlAttributes', false);
        self::safeSetOption('doSortCssClassNames', false);
        self::safeSetOption('doRemoveOmittedQuotes', false);
        self::safeSetOption('doRemoveEmptyAttributes', false);
        self::safeSetOption('doRemoveValueFromEmptyInput', false);

    }

    /**
     * Helper tránh fatal nếu method không tồn tại ở version thư viện
     */
    private static function safeSetOption(string $method, bool $value): void
    {
        if (self::$minifier && method_exists(self::$minifier, $method)) {
            self::$minifier->{$method}($value);
        }
    }

    public static function minify(string $html): string
    {
        if (!self::$enabled || !self::$minifier) {
            return $html;
        }

        // Bypass thủ công
        if (isset($_GET['nominify']) || isset($_GET['nocache'])) {
            return $html;
        }

        $start = microtime(true);
        $originalSize = strlen($html);

        $minified = self::$minifier->minify($html);

        $time = round((microtime(true) - $start) * 1000, 2);
        $saved = round(($originalSize - strlen($minified)) / 1024, 2);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[HtmlMinifier] minified in {$time}ms, saved {$saved} KB");
        }

        return $minified;
    }
}