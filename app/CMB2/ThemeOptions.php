<?php
namespace App\CMB2;

/**
 * THEME OPTIONS – PHIÊN BẢN TAB RÕ RÀNG & CHUYÊN NGHIỆP
 * Tab Header + Tab Widgets (2 khối)
 */
class ThemeOptions
{
    public static function register(): void
    {
        $cmb = new_cmb2_box([
            'id'           => 'theme_options',
            'title'        => 'Cài đặt Theme',
            'object_types' => ['options-page'],
            'option_key'   => 'theme_options',
            'menu_title'   => 'Theme Options',
            'parent_slug'  => null,
            'tab_group'    => 'theme_options_group',
        ]);

        // ==================== TAB: HEADER ====================
        $cmb->add_field([
            'name' => 'Cấu hình Header',
            'id'   => 'tab_header',
            'type' => 'title',
            'tab'  => 'header',
        ]);

        $cmb->add_field([
            'name' => 'Logo chính',
            'id'   => 'logo',
            'type' => 'file',
            'tab'  => 'header',
        ]);

        $cmb->add_field([
            'name' => 'Favicon',
            'id'   => 'favicon',
            'type' => 'file',
            'tab'  => 'header',
        ]);

        // ==================== TAB: WIDGETS (2 KHỐI) ====================
        $cmb->add_field([
            'name' => 'Cấu hình Widget',
            'id'   => 'tab_widgets',
            'type' => 'title',
            'tab'  => 'widgets',
        ]);

        // ── Khối Widget 1 ──
        $cmb->add_field([
            'name'       => 'Khối Widget 1',
            'id'         => 'widget_block_1',
            'type'       => 'group',
            'tab'        => 'widgets',
            'repeatable' => false,
            'options'    => ['group_title' => 'Khối Widget 1'],
            'fields'     => [
                [
                    'name' => 'Tiêu đề khối',
                    'id'   => 'title',
                    'type' => 'text',
                ],
                [
                    'name'    => 'Ảnh',
                    'id'      => 'image',
                    'type'    => 'file',
                    'options' => ['url' => false],
                ],
                [
                    'name' => 'Link (URL)',
                    'id'   => 'link',
                    'type' => 'text_url',
                ],
            ],
        ]);

        // ── Khối Widget 2 ──
        $cmb->add_field([
            'name'       => 'Khối Widget 2',
            'id'         => 'widget_block_2',
            'type'       => 'group',
            'tab'        => 'widgets',
            'repeatable' => false,
            'options'    => ['group_title' => 'Khối Widget 2'],
            'fields'     => [
                [
                    'name' => 'Tiêu đề khối',
                    'id'   => 'title',
                    'type' => 'text',
                ],
                [
                    'name'    => 'Ảnh',
                    'id'      => 'image',
                    'type'    => 'file',
                    'options' => ['url' => false],
                ],
                [
                    'name' => 'Link (URL)',
                    'id'   => 'link',
                    'type' => 'text_url',
                ],
            ],
        ]);
    }
}