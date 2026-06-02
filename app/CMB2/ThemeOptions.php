<?php
namespace App\CMB2;

/**
 * THEME OPTIONS – PHIÊN BẢN TAB RÕ RÀNG & CHUYÊN NGHIỆP (ĐÃ FIX get())
 * Tab Header + Tab Widgets (2 khối)
 */
class ThemeOptions
{
    /**
     * Đăng ký Theme Options
     */
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

        $cmb->add_field(['name' => 'Logo chính',      'id' => 'logo',           'type' => 'file', 'tab' => 'header']);
        $cmb->add_field(['name' => 'Favicon',         'id' => 'favicon',        'type' => 'file', 'tab' => 'header']);
        $cmb->add_field(['name' => 'Màu chính',       'id' => 'primary_color',  'type' => 'colorpicker', 'tab' => 'header']);
        $cmb->add_field(['name' => 'Bật Sticky Header','id' => 'header_sticky',  'type' => 'checkbox', 'default' => 'on', 'tab' => 'header']);
        $cmb->add_field(['name' => 'Hiển thị tìm kiếm','id' => 'header_search',  'type' => 'checkbox', 'default' => 'on', 'tab' => 'header']);

        // ==================== TAB: WIDGETS ====================
        $cmb->add_field([
            'name' => 'Cấu hình Widget',
            'id'   => 'tab_widgets',
            'type' => 'title',
            'tab'  => 'widgets',
        ]);

        // Khối Widget 1
        $cmb->add_field([
            'name'       => 'Khối Widget 1',
            'id'         => 'widget_block_1',
            'type'       => 'group',
            'tab'        => 'widgets',
            'repeatable' => false,
            'options'    => ['group_title' => 'Khối Widget 1'],
            'fields'     => [
                ['name' => 'Tiêu đề khối', 'id' => 'title', 'type' => 'text'],
                ['name' => 'Ảnh',          'id' => 'image', 'type' => 'file', 'options' => ['url' => false]],
                ['name' => 'Link (URL)',   'id' => 'link',  'type' => 'text_url'],
            ],
        ]);

        // Khối Widget 2
        $cmb->add_field([
            'name'       => 'Khối Widget 2',
            'id'         => 'widget_block_2',
            'type'       => 'group',
            'tab'        => 'widgets',
            'repeatable' => false,
            'options'    => ['group_title' => 'Khối Widget 2'],
            'fields'     => [
                ['name' => 'Tiêu đề khối', 'id' => 'title', 'type' => 'text'],
                ['name' => 'Ảnh',          'id' => 'image', 'type' => 'file', 'options' => ['url' => false]],
                ['name' => 'Link (URL)',   'id' => 'link',  'type' => 'text_url'],
            ],
        ]);
    }

    /**
     * LẤY GIÁ TRỊ THEME OPTION (fix lỗi get())
     */
    public static function get(string $key, $default = null)
    {
        $options = get_option('theme_options', []);
        return $options[$key] ?? $default;
    }
}