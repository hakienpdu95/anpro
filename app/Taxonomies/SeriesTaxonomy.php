<?php namespace App\Taxonomies;

class SeriesTaxonomy extends BaseTaxonomy
{
    protected function getTaxonomyKey(): string
    {
        return 'series';
    }

    protected function getSingular(): string
    {
        return 'Chuỗi series';
    }

    protected function getPlural(): string
    {
        return 'Chuỗi series';
    }

    protected function getPostTypes(): array
    {
        return ['happy-family', 'violence-prevention', 'event'];
    }

    protected function getArgs(): array
    {
        $args = parent::getArgs();
        $args['hierarchical'] = true;     
        $args['rewrite'] = ['slug' => 'chuoi-series'];
        return $args;
    }
}