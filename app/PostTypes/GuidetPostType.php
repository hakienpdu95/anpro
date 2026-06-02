<?php

namespace App\PostTypes;

class GuidetPostType extends BasePostType
{
    protected function getPostTypeKey(): string { return 'guide'; }
    protected function getSingular(): string    { return 'Hướng dẫn'; }
    protected function getPlural(): string      { return 'Hướng dẫn'; }

    protected function getArgs(): array
    {
        return array_merge(parent::getArgs(), [
            'menu_icon'     => 'dashicons-megaphone',
            'menu_position' => 8,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments'],
        ]);
    }
}