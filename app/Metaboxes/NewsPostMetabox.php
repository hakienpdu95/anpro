<?php
namespace App\Metaboxes;

class NewsPostMetabox extends BaseMetabox
{
    protected string $title = 'Cấu hình bài đăng';
    protected array $post_types = ['post', 'happy-family', 'family-values', 'violence-prevention'];
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
                    'featured'     => 'Nổi bật',
                    'latest'       => 'Mới nhất',
                    'live'         => 'Live',
                    'exclusive'    => 'Độc quyền',
                    'editors_pick' => 'Biên tập chọn',
                    'sponsored'    => 'Tài trợ',
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

            // ====================== CHUYỂN HƯỚNG ======================
            [
                'type' => 'heading',
                'name' => 'Chuyển hướng (Redirect)',
            ],
            [
                'name' => 'Bật chuyển hướng',
                'id'   => 'is_redirect',
                'type' => 'checkbox',
                'desc' => 'Nếu bật, bài viết sẽ dẫn ra link ngoài (mở tab mới)',
            ],
            [
                'name'    => 'URL chuyển hướng',
                'id'      => 'redirect_url',
                'type'    => 'url',
                'visible' => ['is_redirect', '=', 1],   // Ẩn/hiện thông minh
                'desc'    => 'Ví dụ: Ví dụ: https://affiliate.com/san-pham-hoac-bai-khac',
                'placeholder' => 'https://',
            ],
        ];
    }
}