<?php

// namespace App\Metaboxes;

// class PostMainInfoMetabox extends BaseMetabox
// {
//     protected string $title = 'Thông tin bài viết';
//     protected array $post_types = ['post'];

//     protected function getFields(): array
//     {
//         return [
//             [
//                 'name' => 'Tiêu đề phụ',
//                 'id'   => 'subtitle',
//                 'type' => 'text',
//             ],
//             [
//                 'name' => 'Thời gian đọc (phút)',
//                 'id'   => 'reading_time',
//                 'type' => 'number',
//                 'min'  => 0,
//             ],
//             [
//                 'name'    => 'Đánh dấu đặc biệt',
//                 'id'      => 'flags',
//                 'type'    => 'checkbox_list',
//                 'options' => [
//                     'hot'      => '🔥 Tin nóng',
//                     'featured' => '⭐ Nổi bật',
//                     'breaking' => '🚨 Khẩn cấp',
//                 ],
//             ],
//             // Repeater đã tối ưu hoàn toàn để tránh warning "clone"
//             [
//                 'name'         => 'Thư viện ảnh (Repeater)',
//                 'id'           => 'gallery',
//                 'type'         => 'group',
//                 'clone'        => true,
//                 'sort_clone'   => true,
//                 'collapsible'  => true,
//                 'group_title'  => 'Ảnh {#} - {caption}',
//                 'add_button'   => '+ Thêm ảnh',
//                 'fields'       => [
//                     [
//                         'name' => 'Ảnh',
//                         'id'   => 'image',
//                         'type' => 'image',           // Dùng 'image' thay image_advanced
//                         'clone' => false,            // Fix warning
//                     ],
//                     [
//                         'name' => 'Chú thích ảnh',
//                         'id'   => 'caption',
//                         'type' => 'text',
//                         'clone' => false,            // Fix warning
//                     ],
//                 ],
//             ],
//         ];
//     }
// }