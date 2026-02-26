<?php

namespace App\Optimizations;

use Illuminate\Support\Arr;

/**
 * EDITOR OPTIMIZER 12/10
 *
 * - Force Classic Editor + Disable Gutenberg hoàn toàn
 * - Tắt tất cả block styles, block templates, widgets block
 * - Configurable theo post type (rất linh hoạt cho dự án tin tức lớn)
 * - Early return + debug log + không ảnh hưởng performance
 */
class EditorOptimizer
{
    private static array $config = [
        'enabled'                => true,                    // Tắt nhanh nếu cần test Gutenberg
        'force_classic_editor'   => true,                    // Force Classic Editor
        'post_types'             => ['*'],                   // '*' = tất cả | hoặc ['post', 'event', 'project']
        'disable_block_styles'   => true,                    // Dequeue toàn bộ block CSS
        'remove_block_templates' => true,                    // Tắt FSE (Full Site Editing)
        'support_classic_editor' => true,                    // add_post_type_support('editor')
    ];

    public static function init(): void
    {
        if (!self::config('enabled')) {
            return;
        }

        // Force Classic Editor
        if (self::config('force_classic_editor')) {
            add_filter('use_block_editor_for_post', '__return_false', 9999);
            add_filter('use_block_editor_for_post_type', [self::class, 'disableBlockEditorForPostType'], 9999, 2);
        }

        // Hỗ trợ Classic Editor cho các post type
        if (self::config('support_classic_editor')) {
            add_action('init', [self::class, 'addClassicEditorSupport'], 5);
        }

        // Tắt Gutenberg Widgets + Block Templates
        if (self::config('remove_block_templates')) {
            remove_theme_support('block-templates');
            remove_theme_support('block-template-parts');
        }

        add_filter('gutenberg_use_widgets_block_editor', '__return_false', 9999);
        add_filter('use_widgets_block_editor', '__return_false', 9999);
        add_filter('should_load_block_editor_scripts_and_styles', '__return_false', 9999);

        // Dequeue block styles
        if (self::config('disable_block_styles')) {
            add_action('wp_enqueue_scripts', [self::class, 'dequeueBlockStyles'], 100);
            add_action('admin_enqueue_scripts', [self::class, 'dequeueBlockStyles'], 100);
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('🚀 [EditorOptimizer 12/10] Initialized - Classic Editor Forced');
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

    public static function disableBlockEditorForPostType(bool $canEdit, string $postType): bool
    {
        $allowed = self::config('post_types');
        if ($allowed === ['*'] || in_array($postType, $allowed, true)) {
            return false; // Tắt Gutenberg
        }
        return $canEdit;
    }

    public static function addClassicEditorSupport(): void
    {
        $allowed = self::config('post_types');
        if ($allowed === ['*']) {
            // Tất cả post type
            $postTypes = get_post_types(['public' => true]);
        } else {
            $postTypes = $allowed;
        }

        foreach ($postTypes as $type) {
            add_post_type_support($type, 'editor');
        }
    }

    public static function dequeueBlockStyles(): void
    {
        wp_dequeue_style([
            'wp-block-library',
            'wp-block-library-theme',
            'global-styles',
            'classic-theme-styles',
            'wp-edit-blocks',
            'wp-block-editor',
            'wc-block-style',
        ]);
    }
}