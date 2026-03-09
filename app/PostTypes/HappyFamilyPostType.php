<?php

namespace App\PostTypes;

class HappyFamilyPostType extends BasePostType
{
    protected function getPostTypeKey(): string { return 'happy-family'; }
    protected function getSingular(): string    { return 'Gia đình hạnh phúc'; }
    protected function getPlural(): string      { return 'Gia đình hạnh phúc'; }

    protected function getArgs(): array
    {
        return array_merge(parent::getArgs(), [
            'menu_icon'     => 'dashicons-megaphone',
            'menu_position' => 5,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
        ]);
    }
}