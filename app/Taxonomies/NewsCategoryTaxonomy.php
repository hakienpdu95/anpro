<?php

namespace App\Taxonomies;

class NewsCategoryTaxonomy extends BaseTaxonomy
{
    protected function getTaxonomyKey(): string { return 'the-loai'; }
    protected function getSingular(): string    { return 'Thể loại'; }
    protected function getPlural(): string      { return 'Thể loại'; }
    protected function getPostTypes(): array    { return ['tin-tuc']; }   // áp dụng cho nhiều post type

    protected function getArgs(): array
    {
        $args = parent::getArgs();
        $args['hierarchical'] = true;   // như category
        return $args;
    }
}