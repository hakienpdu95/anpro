<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_filter('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    $dependencies = json_decode(Vite::content('editor.deps.json'));

    foreach ($dependencies as $dependency) {
        if (! wp_script_is($dependency)) {
            wp_enqueue_script($dependency);
        }
    }

    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    
    register_nav_menus([
        'primary_navigation'   => __('Primary Navigation', 'sage'),      // Menu chính – Header (đã có)
        'topbar_navigation'    => __('Top Bar Navigation', 'sage'),      // Menu thanh trên cùng (hotline, ngôn ngữ, account...)
        'secondary_navigation' => __('Secondary Navigation', 'sage'),    // Menu phụ (danh mục, mega menu, sidebar...)
        'footer_navigation'    => __('Footer Navigation', 'sage'),       // Menu chân trang chính
        'mobile_navigation'    => __('Mobile Navigation', 'sage'),       // Menu riêng cho mobile (off-canvas/hamburger)
        // 'social_navigation'    => __('Social Navigation', 'sage'),    // Bỏ comment nếu cần menu icon MXH
    ]);
    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

// === PERFORMANCE OPTIMIZER 12/10 (bloat, heartbeat, query string, XML-RPC...) ===
require_once get_theme_file_path('app/Optimizations/PerformanceOptimizer.php');
\App\Optimizations\PerformanceOptimizer::init();

\App\Optimizations\PerformanceOptimizer::setConfig([
    'heartbeat' => [
        'admin_interval' => 75,           // 75 giây cho editor (có thể chỉnh 60-120)
    ],
]);

// === EDITOR OPTIMIZER 12/10 (Force Classic Editor + Disable Gutenberg) ===
require_once get_theme_file_path('app/Optimizations/EditorOptimizer.php');
\App\Optimizations\EditorOptimizer::init();

// === PRELOAD OPTIMIZER 12/10 (fix preload warning Vite/Sage) ===
require_once get_theme_file_path('app/Optimizations/PreloadOptimizer.php');
\App\Optimizations\PreloadOptimizer::init();

// === ASSET OPTIMIZER 12/10 (Defer/Async JS - Core Web Vitals) ===
require_once get_theme_file_path('app/Optimizations/AssetOptimizer.php');
\App\Optimizations\AssetOptimizer::init();

\App\Optimizations\AssetOptimizer::setConfig([
    'defer' => ['alpine', 'splide', 'swiper', 'gsap', 'videojs', 'chartjs', 'fancybox'],
    'async' => ['alpine', 'splide', 'lazysizes'],
    'exclude' => ['jquery', 'wp-', 'heartbeat', 'wp-auth-check'],
]);

/** === CUSTOM PERMALINKS 10/10 (thêm -postID) === */
require_once get_theme_file_path('app/Permalinks/PermalinkManager.php');
\App\Permalinks\PermalinkManager::init();

// === THÊM CPT MỚI VÀO HỆ THỐNG PERMALINK (rất dễ quản lý) ===
// \App\Permalinks\PermalinkManager::addPostType('project');
// \App\Permalinks\PermalinkManager::addPostType('product');

/** === WATERMARK TỰ ĐỘNG === */
require_once get_theme_file_path('app/Watermark/WatermarkHandler.php');
(new \App\Watermark\WatermarkHandler())->register();

/** === PLACEHOLDER IMAGE ULTIMATE v3.0 – 100% sức mạnh === */
require_once get_theme_file_path('app/Placeholders/PlaceholderHandler.php');
\App\Placeholders\PlaceholderHandler::init();

// === CÁC CẤU HÌNH NÂNG CAO (tùy chọn) ===
// \App\Placeholders\PlaceholderHandler::useMediaImage(12345);           // ID ảnh từ Media Library (khuyến nghị)
// \App\Placeholders\PlaceholderHandler::enableRandom(true);             // Bật random placeholder
// \App\Placeholders\PlaceholderHandler::addPostType('project', 'placeholder-project.jpg');

/** ===============================================
 * MODULAR 10/10 – LOAD SAU AUTOLOADER
 * =============================================== */
// Composer autoloader
if (file_exists(get_theme_file_path('vendor/autoload.php'))) {
    require_once get_theme_file_path('vendor/autoload.php');
}

// Global helpers (cmeta, sage_get_files, ...)
require_once get_theme_file_path('app/helpers.php');

// === CUSTOM TABLE EAV 10/10 ===
require_once get_theme_file_path('app/Database/CustomTableManager.php');
\App\Database\CustomTableManager::init();
// === REGISTER CPT + KHAI BÁO FIELD QUAN TRỌNG (CRITICAL) ===
// \App\Database\CustomTableManager::register('post', ['flags', 'status', 'is_breaking']);
\App\Database\CustomTableManager::register('event', ['flags', 'status', 'is_breaking']);
// \App\Database\CustomTableManager::register('project', ['flags', 'budget', 'deadline', 'project_phase', 'client']);
// \App\Database\CustomTableManager::register('news', ['flags', 'hot', 'trending', 'author_custom']);

// Nếu muốn bump TẤT CẢ meta cho 1 CPT nào đó (ví dụ test):
// \App\Database\CustomTableManager::register('video', ['*']);

// === ARCHIVE FILTERS – MODULAR ===
require_once get_theme_file_path('app/Archives/EventArchive.php');
\App\Archives\EventArchive::init();

// === ADMIN COLUMNS – MODULAR 10/10 ===
require_once get_theme_file_path('app/Admin/EventColumns.php');
\App\Admin\EventColumns::init();

// === AUTO REGISTER CPT + TAXONOMY (sử dụng sage_get_files từ helpers) ===
add_action('init', function () {
    foreach (sage_get_files(get_theme_file_path('app/PostTypes'), 'BasePostType.php') as $file) {
        require_once $file;
        $class = '\\App\\PostTypes\\' . basename($file, '.php');
        if (class_exists($class) && is_subclass_of($class, '\\App\\PostTypes\\BasePostType')) {
            (new $class())->register();
        }
    }
    foreach (sage_get_files(get_theme_file_path('app/Taxonomies'), 'BaseTaxonomy.php') as $file) {
        require_once $file;
        $class = '\\App\\Taxonomies\\' . basename($file, '.php');
        if (class_exists($class) && is_subclass_of($class, '\\App\\Taxonomies\\BaseTaxonomy')) {
            (new $class())->register();
        }
    }
    if (defined('WP_DEBUG') && WP_DEBUG && !get_option('sage_rewrite_flushed')) {
        flush_rewrite_rules();
        update_option('sage_rewrite_flushed', true);
    }
}, 5);

/** === META BOX – BOOT + AUTO REGISTER === */
add_action('after_setup_theme', function () {
    if (file_exists(get_theme_file_path('vendor/wpmetabox/meta-box/meta-box.php'))) {
        require_once get_theme_file_path('vendor/wpmetabox/meta-box/meta-box.php');
    }
}, 5);

add_filter('rwmb_meta_boxes', function (array $meta_boxes): array {
    $path = get_theme_file_path('app/Metaboxes');
    if (!is_dir($path)) return $meta_boxes;
    foreach (glob($path . '/*.php') as $file) {
        if (basename($file) === 'BaseMetabox.php') continue;
        require_once $file;
        $class = '\\App\\Metaboxes\\' . basename($file, '.php');
        if (class_exists($class) && is_subclass_of($class, '\\App\\Metaboxes\\BaseMetabox')) {
            $meta_boxes = $class::addMetabox($meta_boxes);
        }
    }
    return $meta_boxes;
}, 20);

add_filter('default_hidden_meta_boxes', function ($hidden, $screen) {
    if (isset($screen->post_type)) {
        $metabox_ids = \App\Metaboxes\BaseMetabox::getRegisteredIds($screen->post_type);
        $hidden = array_diff($hidden, $metabox_ids);
    }
    return $hidden;
}, 10, 2);

/** === QUERY HELPER === */
require_once get_theme_file_path('app/Helpers/QueryHelper.php');

/** === CACHE SYSTEM 11/10 – DATA CACHE GLOBAL + HTML CACHE === */
require_once get_theme_file_path('app/Helpers/CacheHelper.php');
\App\Helpers\CacheHelper::init();

require_once get_theme_file_path('app/Helpers/DataCache.php');
\App\Helpers\DataCache::init();

require_once get_theme_file_path('app/Helpers/QueryCache.php');
\App\Helpers\QueryCache::init();

require_once get_theme_file_path('app/ViewCache/ViewCache.php');
\App\ViewCache\ViewCache::init();

// === HTML MINIFIER – Tăng tốc độ load 20-40% ===
require_once get_theme_file_path('app/Helpers/HtmlMinifier.php');
\App\Helpers\HtmlMinifier::init();

// === OUTPUT BUFFERING – Minify toàn bộ HTML output ===
add_action('template_redirect', function () {
    // Không chạy trong admin, ajax, feed, cron, robots...
    if (is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed() || is_robots()) {
        return;
    }

    // Chỉ minify các trang bạn muốn (có thể thêm is_category(), is_tag()...)
    if (is_front_page() || is_home() || is_single() || is_page() || is_archive() || is_search()) {
        ob_start([\App\Helpers\HtmlMinifier::class, 'minify']);
    }
}, 1);

add_action('admin_enqueue_scripts', function () {
    // Chỉ load khi ở admin
    wp_enqueue_style(
        'anpro-admin-styles',
        get_theme_file_uri('resources/css/admin/admin.css'),
        ['cmb2-styles'],
        '1.0.0'
    );
}, 99);

// === CMB2 MODULE ===
require_once get_theme_file_path('app/CMB2/Registrar.php');
\App\CMB2\Registrar::init();

// === MERGED POSTS QUERY MODULE 10/10 TỐI ƯU ===
require_once get_theme_file_path('app/Queries/MergedPostsQuery.php');
// Homepage (merge post + event)
\App\Queries\MergedPostsQuery::initHomepage(['posts_per_page' => 1]);
// Archive CPT (thêm bao nhiêu CPT cũng được)
\App\Queries\MergedPostsQuery::initArchive('event',   ['posts_per_page' => 2]);
// \App\Queries\MergedPostsQuery::initArchive('project', ['posts_per_page' => 9]);
// \App\Queries\MergedPostsQuery::initArchive('news',    ['posts_per_page' => 15]);

/**
 * ===============================================
 * HOMEPAGE MERGE 'post' + 'event' - PAGINATION 404 FIX
 * Cách WordPress chuẩn nhất (không override object, không bị redirect_canonical)
 * ===============================================
 */
add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query->is_main_query() || !(is_home() || is_front_page())) {
        return;
    }

    $paged = max(1, (int) get_query_var('paged', 1));

    error_log("[HOMEPAGE_FINAL] Main query modified | paged = {$paged}");

    $query->set('post_type', ['post', 'event']);
    $query->set('posts_per_page', 1);           // ← Thay số này nếu bạn muốn 5-10 bài/trang
    $query->set('orderby', 'date');
    $query->set('order', 'DESC');
    $query->set('post_status', 'publish');
    $query->set('no_found_rows', false);        // BẮT BUỘC cho pagination chính xác
    $query->set('suppress_filters', false);     // Để CustomTableManager + meta flags chạy
    $query->set('update_post_meta_cache', false);
    $query->set('update_post_term_cache', false);

}, 1); // priority 1 - chạy cực sớm

// Block redirect_canonical triệt để (nguyên nhân chính gây lỗi)
add_filter('redirect_canonical', function ($redirect_url) {
    if (is_home() || is_front_page()) {
        error_log("[HOMEPAGE_FINAL] Blocked redirect_canonical");
        return false; // Không redirect /page/2/ về /
    }
    return $redirect_url;
}, 10);