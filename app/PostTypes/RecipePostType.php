<?php

namespace App\PostTypes;

class RecipePostType extends BasePostType
{
    protected function getPostTypeKey(): string { return 'recipe'; }
    protected function getSingular(): string    { return 'Công thức nấu ăn'; }
    protected function getPlural(): string      { return 'Công thức nấu ăn'; }

    protected function getArgs(): array
    {
        return array_merge(parent::getArgs(), [
            'menu_icon'     => 'dashicons-megaphone',
            'menu_position' => 10,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments'],
        ]);
    }
}