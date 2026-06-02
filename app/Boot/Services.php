<?php

/**
 * Service initialization: optimizations, cache, search, output, queries.
 */

use App\Optimizations\PerformanceOptimizer;
use App\Optimizations\EditorOptimizer;
use App\Optimizations\PreloadOptimizer;
use App\Optimizations\AssetOptimizer;
use App\Helpers\CacheHelper;
use App\Helpers\DataCache;
use App\Helpers\QueryCache;
use App\Helpers\HtmlMinifier;
use App\Helpers\ViewCounter;
use App\ViewCache\ViewCache;
use App\Search\SearchManager;
use App\Queries\MergedPostsQuery;
use App\CMB2\Registrar;
use App\Ajax\LoadMore;
use App\Permalinks\PermalinkManager;
use App\Watermark\WatermarkHandler;
use App\Placeholders\PlaceholderHandler;

// ─── Performance optimizations ───────────────────────────────────────────────
PerformanceOptimizer::init();
PerformanceOptimizer::setConfig([
    'heartbeat' => ['admin_interval' => 75],
]);

EditorOptimizer::init();
PreloadOptimizer::init();

AssetOptimizer::init();
AssetOptimizer::setConfig([
    'defer'   => ['swiper', 'gsap', 'videojs', 'chartjs', 'fancybox'],
    'async'   => ['alpine', 'splide', 'lazysizes'],
    'exclude' => ['jquery', 'wp-', 'heartbeat', 'wp-auth-check'],
]);

// ─── URL + media ─────────────────────────────────────────────────────────────
PermalinkManager::init();
// To add a CPT: PermalinkManager::addPostType('project');

(new WatermarkHandler())->register();
PlaceholderHandler::init();
// Optional: PlaceholderHandler::useMediaImage($id), ::enableRandom(true), ::addPostType('cpt', 'file.jpg')

// ─── Cache system ─────────────────────────────────────────────────────────────
CacheHelper::init();
DataCache::init();
QueryCache::init();
ViewCache::init();

// ─── HTML minification ───────────────────────────────────────────────────────
HtmlMinifier::init();

add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed() || is_robots()) {
        return;
    }

    if (is_front_page() || is_home() || is_single() || is_page() || is_archive() || is_search()) {
        ob_start([HtmlMinifier::class, 'minify']);
    }
}, 1);

// ─── Admin styles ─────────────────────────────────────────────────────────────
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style(
        'anpro-admin-styles',
        get_theme_file_uri('resources/css/admin/admin.css'),
        ['cmb2-styles'],
        '1.0.0'
    );
}, 99);

// ─── Search, counters, fields ─────────────────────────────────────────────────
SearchManager::init();
ViewCounter::init();
Registrar::init();

// ─── Homepage + archive queries ───────────────────────────────────────────────
MergedPostsQuery::initHomepage(['posts_per_page' => 3]);
MergedPostsQuery::initArchive('event',                ['posts_per_page' => 2]);
MergedPostsQuery::initArchive('happy-family',         ['posts_per_page' => 2]);
MergedPostsQuery::initArchive('family-values',        ['posts_per_page' => 2]);
MergedPostsQuery::initArchive('violence-prevention',  ['posts_per_page' => 2]);

// ─── AJAX ────────────────────────────────────────────────────────────────────
LoadMore::init();
