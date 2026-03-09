<?php namespace App\Taxonomies;

class PlanTasksTaxonomy extends BaseTaxonomy
{
    protected function getTaxonomyKey(): string
    {
        return 'plan-tasks';
    }

    protected function getSingular(): string
    {
        return 'Nhiệm vụ kế hoạch';
    }

    protected function getPlural(): string
    {
        return 'Nhiệm vụ kế hoạch';
    }

    protected function getPostTypes(): array
    {
        return ['happy-family', 'violence-prevention', 'family-values', 'event'];
    }

    protected function getArgs(): array
    {
        $args = parent::getArgs();
        $args['hierarchical'] = true;          
        $args['rewrite'] = ['slug' => 'nhiem-vu-ke-hoach'];
        return $args;
    }
}