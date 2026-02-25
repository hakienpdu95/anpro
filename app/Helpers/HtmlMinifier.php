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

        // ==================== CẤU HÌNH AN TOÀN 100% CHO ALPINEJS + SPLIDEJS ====================
        self::$minifier->doOptimizeViaHtmlDomParser(true);     // Bắt buộc để minify whitespace hoạt động
        self::$minifier->doRemoveComments(true);
        self::$minifier->doSumUpWhitespace(true);
        self::$minifier->doRemoveWhitespaceAroundTags(true);

        // TẮT hoàn toàn các option nguy hiểm với Alpine (x-data, @click, :class, data-splide-config...)
        self::$minifier->doOptimizeAttributes(false);          // QUAN TRỌNG NHẤT
        self::$minifier->doSortHtmlAttributes(false);
        self::$minifier->doSortCssClassNames(false);
        self::$minifier->doRemoveOmittedQuotes(false);         // Không bỏ quote attribute
        self::$minifier->doRemoveEmptyAttributes(false);
        self::$minifier->doRemoveValueFromEmptyInput(false);

        error_log('🚀 [HtmlMinifier] ĐÃ BẬT THÀNH CÔNG – Safe mode cho AlpineJS + SplideJS');
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
            error_log("📦 [HTML MINIFY] {$time}ms | Tiết kiệm {$saved} KB");
        }

        return $minified;
    }
}