<?php namespace App\Taxonomies;

class AgeStageTaxonomy extends BaseTaxonomy
{
    protected function getTaxonomyKey(): string
    {
        return 'age-stage';
    }

    protected function getSingular(): string
    {
        return 'Giai đoạn';
    }

    protected function getPlural(): string
    {
        return 'Giai đoạn';
    }

    protected function getPostTypes(): array
    {
        return ['guide'];
    }

    protected function getArgs(): array
    {
        $args = parent::getArgs();
        $args['hierarchical'] = true;           // Cho phép tạo cấp cha/con (Thai kỳ → Tam cá nguyệt 1, 2, 3...)
        $args['rewrite'] = ['slug' => 'giai-doan'];
        return $args;
    }
}