<?php

namespace App\PostTypes;

class FamilyValuesPostType extends BasePostType
{
    protected function getPostTypeKey(): string { return 'family-values'; }
    protected function getSingular(): string    { return 'Giáo dục đạo đức'; }
    protected function getPlural(): string      { return 'Giáo dục đạo đức'; }

    protected function getArgs(): array
    {
        return array_merge(parent::getArgs(), [
            'menu_icon'     => 'dashicons-megaphone',
            'menu_position' => 7,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
        ]);
    }
}