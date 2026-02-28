<?php
namespace App\Metaboxes;

class NewsPostMetabox extends BaseMetabox
{
    protected string $title = 'Cấu hình bài đăng tin tức';
    protected array $post_types = ['post'];
    protected string $context = 'normal';
    protected string $priority = 'high';

    protected function getFields(): array
    {
        return [
            // 1. THÔNG TIN HIỂN THỊ CHÍNH
            [
                'type' => 'heading',
                'name' => 'Thông tin hiển thị chính',
            ],
            [
                'name'        => 'Tiêu đề phụ (Subtitle)',
                'id'          => 'subtitle',
                'type'        => 'text',
                'desc'        => 'Hiển thị dưới title chính – cực quan trọng cho UX & CTR',
                'placeholder' => 'Cập nhật nóng nhất hôm nay...',
            ],
            [
                'name' => 'Đoạn mở đầu (Lead paragraph)',
                'id'   => 'lead',
                'type' => 'textarea',
                'rows' => 3,
                'desc' => 'In đậm đầu bài – tăng thời gian đọc & SEO',
            ],
            [
                'name' => 'Thời gian đọc (phút)',
                'id'   => 'reading_time',
                'type' => 'number',
                'min'  => 1,
                'std'  => 5,
            ],
            [
                'name'    => 'Loại bài viết',
                'id'      => 'article_type',
                'type'    => 'select',
                'options' => [
                    'standard'   => 'Tin thường',
                    'review'     => 'Review / Đánh giá',
                    'live'       => 'Live blog',
                    'opinion'    => 'Ý kiến / Bình luận',
                    'interview'  => 'Phỏng vấn',
                    'infographic'=> 'Infographic',
                    'video'      => 'Video chính',
                ],
                'std' => 'standard',
            ],

            // 3. ĐÁNH DẤU & ƯU TIÊN (QUAN TRỌNG NHẤT CHO LỌC)
            [
                'type' => 'heading',
                'name' => 'Đánh dấu & Ưu tiên',
            ],
            [
                'name'    => 'Nhãn nổi bật',
                'id'      => 'flags',
                'type'    => 'checkbox_list',
                'options' => [
                    'hot'          => '🔥 Tin nóng',
                    'featured'     => '⭐ Nổi bật',
                    'breaking'     => '🚨 Tin khẩn',
                    'trending'     => '📈 Quan tâm nhất',
                    'sponsored'    => '📣 Tài trợ',
                    'live'         => '🔴 Live',
                    'exclusive'    => '🔒 Độc quyền',
                    'editors_pick' => '✍️ Biên tập chọn',
                ],
            ],
            [
                'name' => 'Mức độ ưu tiên (0-100)',
                'id'   => 'priority',
                'type' => 'number',
                'min'  => 0,
                'max'  => 100,
                'std'  => 50,
            ],
            [
                'name' => 'Ghim bài viết',
                'id'   => 'is_pinned',
                'type' => 'checkbox',
            ],
            [
                'name' => 'Ghim đến ngày',
                'id'   => 'pinned_until',
                'type' => 'date',
            ],
            [
                'name' => 'Bài viết tài trợ',
                'id'   => 'is_sponsored',
                'type' => 'checkbox',
            ],

            // 4. TÁC GIẢ & NGUỒN TIN
            [
                'type' => 'heading',
                'name' => 'Tác giả & Nguồn tin',
            ],
            [
                'name' => 'Tên tác giả tùy chỉnh',
                'id'   => 'custom_author',
                'type' => 'text',
            ],
            [
                'name' => 'Nguồn tin',
                'id'   => 'source',
                'type' => 'text',
                'placeholder' => 'VnExpress, Reuters, TTXVN...',
            ],
            [
                'name' => 'Link nguồn gốc',
                'id'   => 'source_url',
                'type' => 'url',
            ],
            [
                'name' => 'Chuyển hướng',
                'id'   => 'is_redirect',
                'type' => 'checkbox',
            ],
        ];
    }
}