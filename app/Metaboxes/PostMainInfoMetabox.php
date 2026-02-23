<?php

namespace App\Metaboxes;

use WP_Post;

class PostMainInfoMetabox extends BaseMetabox
{
    protected function getTitle(): string { return 'Thông tin bài viết'; }
    protected function getPostTypes(): array { return ['post']; }   // thay ['post', 'tin-tuc'] nếu cần

    public function render(WP_Post $post)
    {
        wp_nonce_field($this->getId(), $this->getId() . '_nonce');
        ?>
        <p>
            <label><strong>Tiêu đề phụ</strong></label><br>
            <input type="text" name="subtitle" value="<?php echo esc_attr(get_post_meta($post->ID, 'subtitle', true)); ?>" class="widefat">
        </p>
        <p>
            <label><strong>Thời gian đọc (phút)</strong></label><br>
            <input type="number" name="reading_time" value="<?php echo esc_attr(get_post_meta($post->ID, 'reading_time', true)); ?>" style="width:150px">
        </p>
        <p>
            <label><strong>Tóm tắt ngắn</strong></label><br>
            <textarea name="summary" rows="5" class="widefat"><?php echo esc_textarea(get_post_meta($post->ID, 'summary', true)); ?></textarea>
        </p>
        <?php
    }

    protected function saveFields($post_id): void
    {
        update_post_meta($post_id, 'subtitle', sanitize_text_field($_POST['subtitle'] ?? ''));
        update_post_meta($post_id, 'reading_time', absint($_POST['reading_time'] ?? 0));
        update_post_meta($post_id, 'summary', sanitize_textarea_field($_POST['summary'] ?? ''));
    }
}