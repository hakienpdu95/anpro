<?php
namespace App\CMB2;

use CMB2;

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

        $cmb->add_field([
            'name' => 'Logo chính',
            'id'   => 'logo',
            'type' => 'file',
        ]);

        $cmb->add_field([
            'name' => 'Favicon',
            'id'   => 'favicon',
            'type' => 'file',
        ]);

        $cmb->add_field([
            'name' => 'Slogan',
            'id'   => 'slogan',
            'type' => 'text',
        ]);
    }
}