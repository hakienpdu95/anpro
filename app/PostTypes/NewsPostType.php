<?php

namespace App\PostTypes;

use Carbon_Fields\Field;

class NewsPostType extends BasePostType
{
    protected function getPostTypeKey(): string { return 'tin-tuc'; }
    protected function getSingular(): string { return 'Tin tức'; }
    protected function getPlural(): string { return 'Tin tức'; }

    protected function getArgs(): array
    {
        return array_merge(parent::getArgs(), [
            'menu_icon'     => 'dashicons-megaphone',
            'menu_position' => 6,
        ]);
    }

    protected function getMetaboxTabs(): array
    {
        return [
            'Thông tin chính' => [
                Field::make('text', 'subtitle', 'Tiêu đề phụ')->set_width(70),
                Field::make('text', 'reading_time', 'Thời gian đọc (phút)')
                     ->set_attribute('type', 'number')
                     ->set_width(30),

                Field::make('rich_text', 'lead', 'Đoạn mở đầu nổi bật'),
            ],

            'Nguồn tin' => [
                Field::make('text', 'source', 'Nguồn tin'),
                Field::make('url', 'source_url', 'Link nguồn'),
                Field::make('text', 'author_name', 'Tên tác giả (ghi đè)'),
            ],

            'Media bổ sung' => [
                Field::make('oembed', 'featured_video', 'Video nổi bật (YouTube, Vimeo...)'),
                Field::make('complex', 'gallery', 'Thư viện ảnh')
                     ->add_fields([
                         Field::make('image', 'image', 'Ảnh'),
                         Field::make('text', 'caption', 'Chú thích'),
                     ]),
            ],

            'Cài đặt nâng cao' => [
                Field::make('set', 'flags', 'Đánh dấu đặc biệt')
                     ->add_options([
                         'hot'      => 'Tin nóng',
                         'featured' => 'Trang chủ nổi bật',
                         'breaking' => 'Tin nóng khẩn cấp',
                     ]),

                Field::make('association', 'related_posts', 'Bài viết liên quan')
                     ->set_types([['type' => 'post', 'post_type' => 'tin-tuc']])
                     ->set_max(5),
            ],
        ];
    }
}