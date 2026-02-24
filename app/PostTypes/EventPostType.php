<?php

namespace App\PostTypes;

class EventPostType extends BasePostType
{
    protected function getPostTypeKey(): string { return 'event'; }   // hoặc 'portfolio'
    protected function getSingular(): string    { return 'Sự kiện'; }
    protected function getPlural(): string      { return 'Sự kiện'; }

    protected function getArgs(): array
    {
        return array_merge(parent::getArgs(), [
            'menu_icon'     => 'dashicons-megaphone',
            'menu_position' => 6,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments'],
        ]);
    }
}