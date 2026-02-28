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
            [
                'name' => 'Thời gian đọc (phút)',
                'id'   => 'reading_time',
                'type' => 'number',
                'min'  => 1,
                'std'  => 5,
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
            [
                'name' => 'Trạng thái sự kiện',
                'id'   => 'event_status',
                'type' => 'select',
                'options' => [
                    'upcoming'  => 'Sắp diễn ra',
                    'ongoing'   => 'Đang diễn ra',
                    'ended'     => 'Đã kết thúc',
                    'cancelled' => 'Đã hủy',
                ],
                'std' => 'upcoming',
            ],

            // 3. ĐỊA ĐIỂM & BẢN ĐỒ
            [
                'type' => 'heading',
                'name' => 'Địa điểm & Bản đồ',
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
                    'hot'       => '🔥 Sự kiện hot',
                    'featured'  => '⭐ Nổi bật',
                    'breaking'  => '🚨 Quan trọng',
                    'trending'  => '📈 Đang quan tâm',
                    'live'      => '🔴 Live',
                    'exclusive' => '🔒 Độc quyền',
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

            // 5. TỔ CHỨC & ĐĂNG KÝ
            [
                'type' => 'heading',
                'name' => 'Tổ chức & Đăng ký',
            ],
            [
                'name' => 'Ban tổ chức / Người phụ trách',
                'id'   => 'organizer',
                'type' => 'text',
            ],
            [
                'name' => 'Link đăng ký / Mua vé',
                'id'   => 'ticket_link',
                'type' => 'url',
            ],
            [
                'name' => 'Giá vé (nếu có)',
                'id'   => 'ticket_price',
                'type' => 'text',
                'placeholder' => 'Miễn phí hoặc 500.000 VNĐ',
            ],
        ];
    }
}