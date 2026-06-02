<?php

/**
 * Data layer: custom meta tables, CPT/Taxonomy registration, Meta Box integration.
 */

use App\Database\CustomTableManager;

// ─── Custom meta tables ───────────────────────────────────────────────────────
CustomTableManager::init();

CustomTableManager::register('post', [
    'subtitle', 'lead', 'reading_time', 'article_type',
    'flags', 'priority', 'is_pinned', 'pinned_until', 'is_sponsored',
    'custom_author', 'source', 'source_url', 'is_redirect', 'redirect_url',
    '*',
]);
CustomTableManager::register('event', [
    'subtitle', 'lead', 'reading_time',
    'event_start', 'event_end', 'event_status',
    'venue', 'address',
    'flags', 'priority', 'is_pinned', 'pinned_until',
    'organizer', 'ticket_link', 'ticket_price', 'is_redirect', 'redirect_url',
    '*',
]);
CustomTableManager::register('guide',                ['*']);
CustomTableManager::register('review',               ['*']);
CustomTableManager::register('recipe',               ['*']);
CustomTableManager::register('happy-family',         ['*']);
CustomTableManager::register('violence-prevention',  ['*']);
CustomTableManager::register('family-values',        ['*']);

// To add a new CPT: CustomTableManager::register('project', ['flags', 'budget', '*']);

// ─── Archive + admin column modules ──────────────────────────────────────────
\App\Archives\EventArchive::init();
\App\Admin\EventColumns::init();

// ─── Auto-register all CPT and Taxonomy subclasses ───────────────────────────
add_action('init', function () {
    foreach (sage_get_files(get_theme_file_path('app/PostTypes'), 'BasePostType.php') as $file) {
        $class = '\\App\\PostTypes\\' . basename($file, '.php');
        if (class_exists($class) && is_subclass_of($class, '\\App\\PostTypes\\BasePostType')) {
            (new $class())->register();
        }
    }

    foreach (sage_get_files(get_theme_file_path('app/Taxonomies'), 'BaseTaxonomy.php') as $file) {
        $class = '\\App\\Taxonomies\\' . basename($file, '.php');
        if (class_exists($class) && is_subclass_of($class, '\\App\\Taxonomies\\BaseTaxonomy')) {
            (new $class())->register();
        }
    }

    if (defined('WP_DEBUG') && WP_DEBUG && ! get_option('sage_rewrite_flushed')) {
        flush_rewrite_rules();
        update_option('sage_rewrite_flushed', true);
    }
}, 5);

// ─── Meta Box plugin boot ─────────────────────────────────────────────────────
add_action('after_setup_theme', function () {
    if (file_exists(get_theme_file_path('vendor/wpmetabox/meta-box/meta-box.php'))) {
        require_once get_theme_file_path('vendor/wpmetabox/meta-box/meta-box.php');
    }
}, 5);

// ─── Auto-register all Metabox subclasses ────────────────────────────────────
add_filter('rwmb_meta_boxes', function (array $meta_boxes): array {
    $path = get_theme_file_path('app/Metaboxes');
    if (! is_dir($path)) {
        return $meta_boxes;
    }

    foreach (glob($path . '/*.php') as $file) {
        if (basename($file) === 'BaseMetabox.php') {
            continue;
        }
        $class = '\\App\\Metaboxes\\' . basename($file, '.php');
        if (class_exists($class) && is_subclass_of($class, '\\App\\Metaboxes\\BaseMetabox')) {
            $meta_boxes = $class::addMetabox($meta_boxes);
        }
    }

    return $meta_boxes;
}, 20);

add_filter('default_hidden_meta_boxes', function ($hidden, $screen) {
    if (isset($screen->post_type)) {
        $hidden = array_diff($hidden, \App\Metaboxes\BaseMetabox::getRegisteredIds($screen->post_type));
    }
    return $hidden;
}, 10, 2);
