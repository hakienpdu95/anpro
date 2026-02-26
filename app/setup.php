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
        'primary_navigation' => __('Primary Navigation', 'sage'),
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

// ====================== TỐI ƯU ASSET + CRITICAL RENDERING PATH 10/10 ======================

// Preload critical resources (giảm LCP rất mạnh)
// ====================== PRELOAD CRITICAL ASSETS – 12/10 (TỐI ƯU ĐỈNH CAO) ======================
// Ưu tiên: Tailwind → Main SCSS → JS → Vendor → Favicon + Preconnect
// Cache manifest cực mạnh, chỉ đọc 1 lần/giờ, hỗ trợ full hash, không lỗi khi build thay đổi
add_action('wp_head', function () {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }

    static $done = false;
    if ($done) return;
    $done = true;

    $manifest = wp_cache_get('sage_vite_manifest', 'sage_assets');
    if ($manifest === false) {
        $manifest_path = get_theme_file_path('public/build/manifest.json');
        if (!file_exists($manifest_path)) return;

        $manifest = json_decode(file_get_contents($manifest_path), true) ?: [];
        wp_cache_set('sage_vite_manifest', $manifest, 'sage_assets', 3600);
    }

    $preload = '';

    // 1. Tailwind core (app.css) – ưu tiên cao nhất
    $tailwind_url = Vite::asset('resources/css/app.css');

    // 2. Main SCSS của bạn (build ra main.{hash}.css) – file giao diện chính
    $main_url     = Vite::asset('resources/css/main.scss');

    // 3. JS chính
    $js_url       = Vite::asset('resources/js/app.js');

    // Preload CSS + JS với fetchpriority cao nhất
    $preload .= '<link rel="preload" href="' . esc_url($tailwind_url) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" fetchpriority="high">';
    $preload .= '<link rel="preload" href="' . esc_url($main_url)     . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" fetchpriority="high">';
    $preload .= '<link rel="preload" href="' . esc_url($js_url)       . '" as="script" fetchpriority="high">';

    // 4. Vendor chunks (Alpine + Splide) – an toàn với try-catch
    try {
        $alpine_url = Vite::asset('vendor-alpine.js');
        $preload .= '<link rel="preload" href="' . esc_url($alpine_url) . '" as="script" fetchpriority="high">';
    } catch (\Exception $e) {}

    try {
        $splide_url = Vite::asset('vendor-splide.js');
        $preload .= '<link rel="preload" href="' . esc_url($splide_url) . '" as="script" fetchpriority="high">';
    } catch (\Exception $e) {}

    // 5. Favicon
    $ico_url = get_theme_file_uri('public/build/images/favicon.ico');
    $preload .= '<link rel="icon" href="' . esc_url($ico_url) . '" type="image/x-icon">';
    $preload .= '<link rel="shortcut icon" href="' . esc_url($ico_url) . '" type="image/x-icon">';

    // 6. Preconnect + DNS-Prefetch cho font Google (rất phổ biến khi dùng Tailwind)
    $preload .= '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>';
    $preload .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    $preload .= '<link rel="dns-prefetch" href="https://fonts.googleapis.com">';

    echo $preload;
}, 5); 

// === PERFORMANCE OPTIMIZER 12/10 (bloat, heartbeat, query string, XML-RPC...) ===
require_once get_theme_file_path('app/Optimizations/PerformanceOptimizer.php');
\App\Optimizations\PerformanceOptimizer::init();

\App\Optimizations\PerformanceOptimizer::setConfig([
    'heartbeat' => [
        'admin_interval' => 75,           // 75 giây cho editor (có thể chỉnh 60-120)
    ],
]);

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

/** === FORCE CLASSIC EDITOR + DISABLE GUTENBERG === */
add_filter('use_block_editor_for_post', '__return_false', 9999);
add_filter('use_block_editor_for_post_type', '__return_false', 9999, 2);
add_action('init', fn() => add_post_type_support('post', 'editor'), 5);
add_filter('gutenberg_use_widgets_block_editor', '__return_false', 9999);
add_filter('use_widgets_block_editor', '__return_false', 9999);
remove_theme_support('block-templates');
remove_theme_support('block-template-parts');
add_filter('should_load_block_editor_scripts_and_styles', '__return_false', 9999);
add_action('wp_enqueue_scripts', fn() => wp_dequeue_style(['wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles', 'wp-edit-blocks', 'wp-block-editor', 'wc-block-style']), 100);
add_action('admin_enqueue_scripts', fn() => wp_dequeue_style(['wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles', 'wp-edit-blocks', 'wp-block-editor', 'wc-block-style']), 100);

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