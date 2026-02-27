<?php
namespace App\CMB2;

/**
 * THEME OPTIONS – PHIÊN BẢN CHUYÊN NGHIỆP 10/10
 * Pure CMB2 syntax, tối ưu hiệu suất, dễ mở rộng
 */
class ThemeOptions
{
    public static function register(): void
    {
        $cmb = \new_cmb2_box([
            'id'            => 'theme_options',
            'title'         => 'Cài đặt Theme',
            'object_types'  => ['options-page'],
            'option_key'    => 'theme_options',
            'menu_title'    => 'Theme Options',
            'parent_slug'   => null,           // null = menu chính
            'tab_group'     => 'theme_options_group',
        ]);

        // ==================== TAB: CHUNG ====================
        $cmb->add_field([
            'name' => 'Logo chính',
            'id'   => 'logo',
            'type' => 'file',
            'tab'  => 'general',
        ]);

        $cmb->add_field([
            'name' => 'Favicon',
            'id'   => 'favicon',
            'type' => 'file',
            'tab'  => 'general',
        ]);

        $cmb->add_field([
            'name' => 'Màu chính của theme',
            'id'   => 'primary_color',
            'type' => 'colorpicker',
            'tab'  => 'general',
        ]);

        // ==================== TAB: HEADER ====================
        $cmb->add_field([
            'name' => 'Bật Sticky Header',
            'id'   => 'header_sticky',
            'type' => 'checkbox',
            'default' => 'on',
            'tab'  => 'header',
        ]);

        $cmb->add_field([
            'name' => 'Hiển thị ô tìm kiếm',
            'id'   => 'header_search',
            'type' => 'checkbox',
            'default' => 'on',
            'tab'  => 'header',
        ]);

        // ==================== TAB: FOOTER ====================
        $cmb->add_field([
            'name' => 'Copyright Text',
            'id'   => 'footer_copyright',
            'type' => 'textarea_small',
            'tab'  => 'footer',
        ]);

        // ==================== TAB: SINGLE POST ====================
        $cmb->add_field([
            'name' => 'Hiển thị Author Box',
            'id'   => 'single_author_box',
            'type' => 'checkbox',
            'default' => 'on',
            'tab'  => 'single',
        ]);

        $cmb->add_field([
            'name' => 'Hiển thị Related Posts',
            'id'   => 'single_related',
            'type' => 'checkbox',
            'default' => 'on',
            'tab'  => 'single',
        ]);

        // ==================== TAB: SOCIAL MEDIA ====================
        $socials = ['facebook', 'youtube', 'instagram', 'tiktok', 'linkedin'];

        foreach ($socials as $social) {
            $cmb->add_field([
                'name' => ucfirst($social) . ' URL',
                'id'   => 'social_' . $social,
                'type' => 'text_url',
                'tab'  => 'social',
            ]);
        }
    }
}