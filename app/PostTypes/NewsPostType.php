<?php

namespace App\PostTypes;

class NewsPostType extends BasePostType
{
    protected function getPostTypeKey(): string { return 'tin-tuc'; }   // hoặc 'portfolio'
    protected function getSingular(): string    { return 'Tin tức'; }
    protected function getPlural(): string      { return 'Tin tức'; }

    protected function getArgs(): array
    {
        return array_merge(parent::getArgs(), [
            'menu_icon'     => 'dashicons-megaphone',
            'menu_position' => 6,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments'],
        ]);
    }
}