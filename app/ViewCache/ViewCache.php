<?php

namespace App\ViewCache;

use App\Helpers\CacheHelper;
use Illuminate\Support\Facades\Blade;

class ViewCache
{
    public static function init(): void
    {
        Blade::directive('includeCached', function ($expression) {
            return "<?php echo \App\ViewCache\ViewCache::renderCached({$expression}); ?>";
        });

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('🚀 [ViewCache 11/10] @includeCached registered');
        }
    }

    public static function renderCached(string $view, array $data = [], int $ttl = 300, bool $bypass = false): string
    {
        if ($bypass || (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['nocache']))) {
            return view($view, $data)->render();
        }

        $key = self::makeStableKey($view, $data);
        return CacheHelper::remember('html_' . $key, $ttl, function () use ($view, $data) {
            $html = view($view, $data)->render();
            return \App\Helpers\HtmlMinifier::minify($html); 
        });
    }

    private static function makeStableKey(string $view, array $data): string
    {
        $isHome = is_home() || is_front_page();
        $context = $isHome ? 'home' : (get_queried_object_id() . '|' . get_query_var('paged', 1));

        ksort($data);
        return str_replace(['/', '.'], '_', trim($view, '/')) . '_' . md5(serialize($data) . $context);
    }
}