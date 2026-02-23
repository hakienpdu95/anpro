<?php

namespace App\Metaboxes;

use WP_Post;

class PostExtraInfoMetabox extends BaseMetabox
{
    protected function getTitle(): string { return 'Thông tin thêm'; }
    protected function getPostTypes(): array { return ['post']; }

    public function render(WP_Post $post)
    {
        wp_nonce_field($this->getId(), $this->getId() . '_nonce');
        ?>
        <p>
            <label><strong>Nguồn tin / Tác giả</strong></label><br>
            <input type="text" name="source" value="<?php echo esc_attr(get_post_meta($post->ID, 'source', true)); ?>" class="widefat">
        </p>
        <p>
            <label><strong>Ghi chú nội bộ</strong></label><br>
            <textarea name="internal_note" rows="4" class="widefat"><?php echo esc_textarea(get_post_meta($post->ID, 'internal_note', true)); ?></textarea>
        </p>
        <p>
            <label><strong>CSS tùy chỉnh cho bài này</strong></label><br>
            <textarea name="custom_css" rows="6" class="widefat"><?php echo esc_textarea(get_post_meta($post->ID, 'custom_css', true)); ?></textarea>
        </p>
        <?php
    }

    protected function saveFields($post_id): void
    {
        update_post_meta($post_id, 'source', sanitize_text_field($_POST['source'] ?? ''));
        update_post_meta($post_id, 'internal_note', sanitize_textarea_field($_POST['internal_note'] ?? ''));
        update_post_meta($post_id, 'custom_css', sanitize_textarea_field($_POST['custom_css'] ?? ''));
    }
}