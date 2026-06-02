<?php
namespace App\Metaboxes;

class EventMetabox extends BaseMetabox
{
    protected string $title = 'Cấu hình bài đăng sự kiện';
    protected array $post_types = ['event'];
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
                'desc'        => 'Hiển thị dưới title chính',
                'placeholder' => 'Sự kiện đặc biệt mùa hè 2026...',
            ],
            [
                'name' => 'Đoạn mở đầu (Lead paragraph)',
                'id'   => 'lead',
                'type' => 'textarea',
                'rows' => 3,
            ],

            // 2. THÔNG TIN SỰ KIỆN CHÍNH (đặc thù Event)
            [
                'type' => 'heading',
                'name' => 'Thời gian & Thông tin sự kiện',
            ],
            [
                'name' => 'Thời gian bắt đầu',
                'id'   => 'event_start',
                'type' => 'datetime',
                'desc' => 'Ngày + giờ bắt đầu',
            ],
            [
                'name' => 'Thời gian kết thúc',
                'id'   => 'event_end',
                'type' => 'datetime',
            ],

            // 3. ĐỊA ĐIỂM & BẢN ĐỒ
            [
                'type' => 'heading',
                'name' => 'Địa điểm',
            ],
            [
                'name' => 'Tên địa điểm',
                'id'   => 'venue',
                'type' => 'text',
                'placeholder' => 'Hội trường ABC, Quận 1, TP.HCM',
            ],
            [
                'name' => 'Địa chỉ chi tiết',
                'id'   => 'address',
                'type' => 'text',
            ],

            // 4. ĐÁNH DẤU & ƯU TIÊN (giữ giống post)
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
                'name' => 'Ghim sự kiện',
                'id'   => 'is_pinned',
                'type' => 'checkbox',
            ],
            [
                'name' => 'Ghim đến ngày',
                'id'   => 'pinned_until',
                'type' => 'date',
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