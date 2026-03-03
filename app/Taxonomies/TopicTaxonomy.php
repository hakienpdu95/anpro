<?php namespace App\Taxonomies;

class TopicTaxonomy extends BaseTaxonomy
{
    protected function getTaxonomyKey(): string
    {
        return 'topic';
    }

    protected function getSingular(): string
    {
        return 'Chủ đề';
    }

    protected function getPlural(): string
    {
        return 'Chủ đề';
    }

    protected function getPostTypes(): array
    {
        return ['guide'];
    }

    protected function getArgs(): array
    {
        $args = parent::getArgs();
        $args['hierarchical'] = true;           // Cho phép tạo cấp con nếu cần (Ăn dặm → Ăn dặm BLW, Ăn dặm truyền thống...)
        $args['rewrite'] = ['slug' => 'chu-de'];
        return $args;
    }
}