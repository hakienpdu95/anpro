<?php

namespace App\PostTypes;

class ViolencePreventionPostType extends BasePostType
{
    protected function getPostTypeKey(): string { return 'violence-prevention'; }
    protected function getSingular(): string    { return 'Phòng chống bạo lực'; }
    protected function getPlural(): string      { return 'Phòng chống bạo lực'; }

    protected function getArgs(): array
    {
        return array_merge(parent::getArgs(), [
            'menu_icon'     => 'dashicons-megaphone',
            'menu_position' => 6,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
        ]);
    }
}