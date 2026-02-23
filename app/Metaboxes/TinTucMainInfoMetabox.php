<?php

namespace App\Metaboxes;

class TinTucMainInfoMetabox extends BaseMetabox
{
    protected string $title = 'Thông tin bài viết - Tin tức';
    protected array $post_types = ['tin-tuc'];   // CHỈ áp dụng cho 'tin-tuc'

    protected function getFields(): array
    {
        return [
            [
                'name' => 'Tiêu đề phụ (Tin tức)',
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
            [
                'name'        => 'Gallery ảnh (Repeater)',
                'id'          => 'gallery',
                'type'        => 'group',
                'clone'       => true,
                'sort_clone'  => true,
                'collapsible' => true,
                'group_title' => 'Ảnh {#}',
                'fields'      => [
                    [
                        'name' => 'Ảnh',
                        'id'   => 'image',
                        'type' => 'image',
                    ],
                    [
                        'name' => 'Chú thích',
                        'id'   => 'caption',
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }
}