<?php

namespace App\PostTypes;

abstract class BasePostType
{
    abstract protected function getPostTypeKey(): string;
    abstract protected function getSingular(): string;
    abstract protected function getPlural(): string;

    public function register()
    {
        register_post_type($this->getPostTypeKey(), $this->getArgs());
    }

    protected function getArgs(): array
    {
        return [
            'labels'              => $this->getLabels(),
            'public'              => true,
            'show_in_rest'        => false,           // Tắt vì dùng Classic Editor
            'has_archive'         => true,
            'rewrite'             => ['slug' => $this->getPostTypeKey()],
            'supports'            => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions', 'page-attributes'],
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-megaphone',
            'capability_type'     => 'post',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
        ];
    }

    protected function getLabels(): array
    {
        $singular = $this->getSingular();
        $plural   = $this->getPlural();

        return [
            'name'                  => $plural,
            'singular_name'         => $singular,
            'menu_name'             => $plural,
            'add_new'               => 'Thêm mới',
            'add_new_item'          => "Thêm $singular",
            'edit_item'             => "Sửa $singular",
            'new_item'              => "$singular mới",
            'view_item'             => "Xem $singular",
            'search_items'          => "Tìm $plural",
            'not_found'             => "Không tìm thấy $plural",
            'not_found_in_trash'    => "Không có $plural trong thùng rác",
        ];
    }
}