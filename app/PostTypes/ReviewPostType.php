<?php

namespace App\PostTypes;

class ReviewPostType extends BasePostType
{
    protected function getPostTypeKey(): string { return 'review'; }
    protected function getSingular(): string    { return 'Review sản phẩm'; }
    protected function getPlural(): string      { return 'Review sản phẩm'; }

    protected function getArgs(): array
    {
        return array_merge(parent::getArgs(), [
            'menu_icon'     => 'dashicons-megaphone',
            'menu_position' => 9,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments'],
        ]);
    }
}