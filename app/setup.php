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

/**
 * === FORCE CLASSIC EDITOR + DISABLE GUTENBERG HOÀN TOÀN (ĐÃ FIX) ===
 */

// 1. Buộc dùng Classic Editor cho tất cả post type (post, page, CPT...)
add_filter('use_block_editor_for_post', '__return_false', 9999);
add_filter('use_block_editor_for_post_type', '__return_false', 9999, 2);

// 2. Tắt Block Editor cho Widgets
add_filter('gutenberg_use_widgets_block_editor', '__return_false', 9999);
add_filter('use_widgets_block_editor', '__return_false', 9999);

// 3. Tắt Full Site Editing (FSE) và Block Templates
remove_theme_support('block-templates');
remove_theme_support('block-template-parts');
add_filter('should_load_block_editor_scripts_and_styles', '__return_false', 9999);

// 4. Xóa toàn bộ CSS/JS Gutenberg (dùng closure để tránh lỗi "function not found")
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
    wp_dequeue_style('classic-theme-styles');
    wp_dequeue_style('wp-edit-blocks');
    wp_dequeue_style('wp-block-editor');
    wp_dequeue_style('wc-block-style'); // nếu có Woo
}, 100);

add_action('admin_enqueue_scripts', function () {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
    wp_dequeue_style('classic-theme-styles');
    wp_dequeue_style('wp-edit-blocks');
    wp_dequeue_style('wp-block-editor');
    wp_dequeue_style('wc-block-style');
}, 100);

add_action('enqueue_block_editor_assets', function () {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-edit-blocks');
}, 100);

// 5. Ẩn notice "Try Gutenberg"
add_action('admin_init', function () {
    remove_action('try_gutenberg_panel', 'wp_try_gutenberg_panel');
}, 999);

/**
 * === TỐI ƯU 10/10: AUTO REGISTER CPT + TAXONOMY + METABOX ===
 * Chỉ load khi cần, cache file list, chỉ chạy trong admin khi có thể
 */

// Require Composer autoloader (chỉ 1 lần)
if (file_exists(get_theme_file_path('vendor/autoload.php'))) {
    require_once get_theme_file_path('vendor/autoload.php');
}

// Helper cache file list (static + transient)
function sage_get_files($folder, $exclude = '') {
    static $cache = [];
    $key = md5($folder . $exclude);
    if (isset($cache[$key])) return $cache[$key];

    if (!is_dir($folder)) return [];

    $files = glob($folder . '/*.php');
    if ($exclude) {
        $files = array_filter($files, fn($f) => basename($f) !== $exclude);
    }

    $cache[$key] = $files;
    return $files;
}

/**
 * REGISTER CPT + TAXONOMY (chỉ cần chạy 1 lần trên init)
 */
add_action('init', function () {
    // CPT
    foreach (sage_get_files(get_theme_file_path('app/PostTypes'), 'BasePostType.php') as $file) {
        require_once $file;
        $class = '\\App\\PostTypes\\' . basename($file, '.php');
        if (class_exists($class) && is_subclass_of($class, '\\App\\PostTypes\\BasePostType')) {
            (new $class())->register();
        }
    }

    // Taxonomy
    foreach (sage_get_files(get_theme_file_path('app/Taxonomies'), 'BaseTaxonomy.php') as $file) {
        require_once $file;
        $class = '\\App\\Taxonomies\\' . basename($file, '.php');
        if (class_exists($class) && is_subclass_of($class, '\\App\\Taxonomies\\BaseTaxonomy')) {
            (new $class())->register();
        }
    }

    // Chỉ flush rewrite khi đang dev (thêm CPT mới)
    if (defined('WP_DEBUG') && WP_DEBUG && !get_option('sage_rewrite_flushed')) {
        flush_rewrite_rules();
        update_option('sage_rewrite_flushed', true);
    }
}, 5);

/**
 * REGISTER METABOX (chỉ load trong admin → tiết kiệm hiệu suất)
 */
add_action('admin_init', function () {
    foreach (sage_get_files(get_theme_file_path('app/Metaboxes'), 'BaseMetabox.php') as $file) {
        require_once $file;
        $class = '\\App\\Metaboxes\\' . basename($file, '.php');
        if (class_exists($class) && is_subclass_of($class, '\\App\\Metaboxes\\BaseMetabox')) {
            (new $class())->register();
        }
    }
}, 20);

/**
 * Helper lấy meta siêu dễ trong Blade
 * Ví dụ: {{ cmeta('subtitle') }}
 */
function cmeta($key, $post_id = null) {
    $post_id = $post_id ?? get_the_ID();
    return get_post_meta($post_id, $key, true);
}