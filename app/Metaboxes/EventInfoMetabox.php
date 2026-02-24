<?php

namespace App\Metaboxes;

class EventInfoMetabox extends BaseMetabox
{
    protected string $title = 'Thông tin sự kiện';
    protected array $post_types = ['event'];

    protected function getFields(): array
    {
        return [
            [
                'name' => 'Tiêu đề phụ',
                'id'   => 'subtitle',
                'type' => 'text',
            ],
            [
                'name' => 'Thời gian đọc',
                'id'   => 'reading_time',
                'type' => 'number',
            ],
            [
                'name'    => 'Đánh dấu',
                'id'      => 'flags',
                'type'    => 'checkbox_list',
                'options' => [
                    'hot'      => '🔥 Tin nóng',
                    'featured' => '⭐ Nổi bật',
                    'breaking' => '🚨 Khẩn cấp',
                ],
            ],
        ];
    }
}