<?php

/**
 * Theme support, navigation, image sizes, sidebars, and editor integration.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

// ─── Block editor: inject Vite CSS ───────────────────────────────────────────
add_filter('block_editor_settings_all', function ($settings) {
    $settings['styles'][] = ['css' => "@import url('" . Vite::asset('resources/css/editor.css') . "')"];
    return $settings;
});

// ─── Block editor: inject Vite JS ────────────────────────────────────────────
add_filter('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    foreach (json_decode(Vite::content('editor.deps.json')) as $dependency) {
        if (! wp_script_is($dependency)) {
            wp_enqueue_script($dependency);
        }
    }

    echo Vite::withEntryPoints(['resources/js/editor.js'])->toHtml();
});

// ─── Use the Vite-built theme.json ───────────────────────────────────────────
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json' ? public_path('build/assets/theme.json') : $path;
}, 10, 2);

// ─── Core theme setup ────────────────────────────────────────────────────────
add_action('after_setup_theme', function () {
    remove_theme_support('block-templates');
    remove_theme_support('core-block-patterns');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('customize-selective-refresh-widgets');
    add_theme_support('html5', [
        'caption', 'comment-form', 'comment-list',
        'gallery', 'search-form', 'script', 'style',
    ]);

    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
        'footer_column_1'    => __('Footer Column 1', 'sage'),
        'footer_column_2'    => __('Footer Column 2', 'sage'),
        'footer_column_3'    => __('Footer Column 3', 'sage'),
        'footer_column_4'    => __('Footer Column 4', 'sage'),
        'mobile_navigation'  => __('Mobile Navigation', 'sage'),
        'social_navigation'  => __('Social Navigation', 'sage'),
    ]);

    // Custom image sizes (16:9)
    add_image_size('thumb-small',  400,  225,  true);
    add_image_size('thumb-medium', 750,  422,  true);
    add_image_size('thumb-large',  1200, 675,  true);
    add_image_size('thumb-xl',     1600, 900,  true);

    add_filter('max_srcset_image_width', fn() => 2000);
    add_filter('big_image_size_threshold', '__return_false');

    add_filter('intermediate_image_sizes_advanced', function ($sizes) {
        unset($sizes['medium'], $sizes['medium_large'], $sizes['large'],
              $sizes['1536x1536'], $sizes['2048x2048']);
        return $sizes;
    });

    add_filter('intermediate_image_sizes', fn() => [
        'thumbnail', 'thumb-small', 'thumb-medium', 'thumb-large', 'thumb-xl',
    ]);

    // TinyMCE table plugin
    $table_url = get_theme_file_uri('/plugins/table/plugin.min.js');
    add_filter('mce_external_plugins', function ($plugins) use ($table_url) {
        $plugins['table'] = $table_url;
        return $plugins;
    }, 999);

    add_filter('mce_buttons_2', function ($buttons) {
        array_push($buttons,
            'table', 'tableprops', 'tabledelete', '|',
            'tableinsertrowbefore', 'tableinsertrowafter', 'tabledeleterow', '|',
            'tableinsertcolbefore', 'tableinsertcolafter', 'tabledeletecol'
        );
        return $buttons;
    }, 999);
}, 20);

// ─── Zero out default WP sizes on theme activation ───────────────────────────
add_action('after_switch_theme', function () {
    update_option('medium_size_w', 0);
    update_option('medium_size_h', 0);
    update_option('large_size_w', 0);
    update_option('large_size_h', 0);
    update_option('medium_large_size_w', 0);
});

// ─── Sidebars ─────────────────────────────────────────────────────────────────
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ];
    register_sidebar(['name' => __('Primary', 'sage'), 'id' => 'sidebar-primary'] + $config);
    register_sidebar(['name' => __('Footer', 'sage'),  'id' => 'sidebar-footer']  + $config);
});

// ─── Image size labels in media uploader ─────────────────────────────────────
add_filter('image_size_names_choose', fn() => [
    'thumbnail'    => __('Thumbnail (Admin)', 'sage'),
    'thumb-small'  => __('Thumb Small – Mobile', 'sage'),
    'thumb-medium' => __('Thumb Medium – Default', 'sage'),
    'thumb-large'  => __('Thumb Large – Desktop', 'sage'),
    'thumb-xl'     => __('Thumb XL – Hero', 'sage'),
]);
