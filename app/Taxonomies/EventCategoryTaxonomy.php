<?php

namespace App\Taxonomies;

class EventCategoryTaxonomy extends BaseTaxonomy
{
    protected function getTaxonomyKey(): string { return 'event-categories'; }
    protected function getSingular(): string    { return 'Danh mục sự kiện'; }
    protected function getPlural(): string      { return 'Danh mục sự kiện'; }
    protected function getPostTypes(): array    { return ['event']; }   // áp dụng cho nhiều post type

    protected function getArgs(): array
    {
        $args = parent::getArgs();
        $args['hierarchical'] = true;   // như category
        return $args;
    }
}