<?php
namespace App\CMB2\Taxonomy;

use CMB2;

/**
 * ===============================================
 * EVENT CATEGORY FIELDS - MODULE HÓA CAO CẤP
 * Dễ mở rộng: thêm banner, thêm field, thêm taxonomy mới
 * ===============================================
 */
class EventCategoryFields
{
    public static function register(): void
    {
        $cmb = \new_cmb2_box([
            'id'            => 'event_category_advanced',
            'title'         => 'Thông tin nâng cao cho danh mục',
            'object_types'  => ['term'],
            'taxonomies'    => ['event-categories'],   // ← Đổi slug taxonomy nếu cần
            'context'       => 'normal',
            'priority'      => 'high',
        ]);

        // ==================== THÔNG TIN TỔNG QUAN ====================
        $cmb->add_field([
            'name' => 'Tiêu đề tổng quan',
            'id'   => 'general_title',
            'type' => 'text',
            'desc' => 'Tiêu đề sẽ ghi đè tên mặc định của danh mục (nếu có)',
        ]);

        $cmb->add_field([
            'name'    => 'Mô tả bổ sung',
            'id'      => 'general_description',
            'type'    => 'wysiwyg',
            'options' => [
                'textarea_rows' => 6,
                'media_buttons' => true,
            ],
            'desc' => 'Nội dung mô tả hiển thị ở đầu trang danh mục',
        ]);

        // ==================== BANNER 1 ====================
        $group1 = $cmb->add_field([
            'id'         => 'banner_1',
            'type'       => 'group',
            'name'       => 'Banner 1',
            'repeatable' => false,   // Không cho phép lặp lại (cố định 2 banner)
            'options'    => [
                'group_title' => 'Banner 1',
            ],
        ]);

        $cmb->add_group_field($group1, [
            'name' => 'Tiêu đề Banner 1',
            'id'   => 'title',
            'type' => 'text',
        ]);

        $cmb->add_group_field($group1, [
            'name' => 'Ảnh Banner 1',
            'id'   => 'image',
            'type' => 'file',
        ]);

        $cmb->add_group_field($group1, [
            'name' => 'Link liên kết Banner 1',
            'id'   => 'link',
            'type' => 'text_url',
        ]);

        // ==================== BANNER 2 ====================
        $group2 = $cmb->add_field([
            'id'         => 'banner_2',
            'type'       => 'group',
            'name'       => 'Banner 2',
            'repeatable' => false,
            'options'    => [
                'group_title' => 'Banner 2',
            ],
        ]);

        $cmb->add_group_field($group2, [
            'name' => 'Tiêu đề Banner 2',
            'id'   => 'title',
            'type' => 'text',
        ]);

        $cmb->add_group_field($group2, [
            'name' => 'Ảnh Banner 2',
            'id'   => 'image',
            'type' => 'file',
        ]);

        $cmb->add_group_field($group2, [
            'name' => 'Link liên kết Banner 2',
            'id'   => 'link',
            'type' => 'text_url',
        ]);

        // ==================== HƯỚNG DẪN MỞ RỘNG SAU NÀY ====================
        // Muốn thêm Banner 3? Copy từ $group2 và đổi tên thành banner_3
        // Muốn làm nhiều banner động? Thay 'repeatable' => true cho 1 group duy nhất
    }
}