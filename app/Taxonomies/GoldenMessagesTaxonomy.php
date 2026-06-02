<?php namespace App\Taxonomies;

class GoldenMessagesTaxonomy extends BaseTaxonomy
{
    protected function getTaxonomyKey(): string
    {
        return 'golden-messages';
    }

    protected function getSingular(): string
    {
        return 'Thông điệp vàng';
    }

    protected function getPlural(): string
    {
        return 'Thông điệp vàng';
    }

    protected function getPostTypes(): array
    {
        return ['happy-family', 'violence-prevention', 'family-values'];
    }

    protected function getArgs(): array
    {
        $args = parent::getArgs();
        $args['hierarchical'] = true;          
        $args['rewrite'] = ['slug' => 'thong-diep-vang'];
        return $args;
    }
}