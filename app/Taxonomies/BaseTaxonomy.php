<?php

namespace App\Taxonomies;

abstract class BaseTaxonomy
{
    abstract protected function getTaxonomyKey(): string;
    abstract protected function getSingular(): string;
    abstract protected function getPlural(): string;
    abstract protected function getPostTypes(): array;

    public function register()
    {
        register_taxonomy($this->getTaxonomyKey(), $this->getPostTypes(), $this->getArgs());
    }

    protected function getArgs(): array
    {
        return [
            'labels'            => $this->getLabels(),
            'public'            => true,
            'show_in_rest'      => false,
            'hierarchical'      => true,     // true = category, false = tag
            'show_ui'           => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => $this->getTaxonomyKey()],
        ];
    }

    protected function getLabels(): array
    {
        $singular = $this->getSingular();
        $plural   = $this->getPlural();

        return [
            'name'              => $plural,
            'singular_name'     => $singular,
            'menu_name'         => $plural,
            'add_new_item'      => "Thêm $singular mới",
            'edit_item'         => "Sửa $singular",
            'search_items'      => "Tìm $plural",
        ];
    }
}