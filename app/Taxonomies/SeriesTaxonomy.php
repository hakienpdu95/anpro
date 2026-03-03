<?php namespace App\Taxonomies;

class SeriesTaxonomy extends BaseTaxonomy
{
    protected function getTaxonomyKey(): string
    {
        return 'series';
    }

    protected function getSingular(): string
    {
        return 'Chuỗi hướng dẫn';
    }

    protected function getPlural(): string
    {
        return 'Chuỗi hướng dẫn';
    }

    protected function getPostTypes(): array
    {
        return ['guide'];
    }

    protected function getArgs(): array
    {
        $args = parent::getArgs();
        $args['hierarchical'] = false;          // Thường là flat (mỗi series là 1 nhóm riêng)
        $args['rewrite'] = ['slug' => 'chuoi-huong-dan'];
        return $args;
    }
}