<?php

namespace App\Metaboxes;

use WP_Post;

abstract class BaseMetabox
{
    protected string $title;
    protected array $post_types = ['post'];
    protected string $context = 'normal';  // normal | advanced | side
    protected string $priority = 'high';   // high | core | default | low

    public function __construct()
    {
        $this->title = $this->getTitle();
    }

    abstract protected function getTitle(): string;
    abstract protected function getPostTypes(): array;

    public function register()
    {
        add_action('add_meta_boxes', [$this, 'addMetabox']);
        add_action('save_post', [$this, 'saveMetabox'], 10, 2);
    }

    public function addMetabox()
    {
        foreach ($this->getPostTypes() as $post_type) {
            add_meta_box(
                $this->getId(),
                $this->title,
                [$this, 'render'],
                $post_type,
                $this->context,
                $this->priority
            );
        }
    }

    abstract public function render(WP_Post $post);

    public function saveMetabox($post_id, $post)
    {
        if (!$this->canSave($post_id)) {
            return;
        }
        $this->saveFields($post_id);
    }

    protected function canSave($post_id): bool
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return false;
        if (!current_user_can('edit_post', $post_id)) return false;

        $nonce = $this->getId() . '_nonce';
        if (!isset($_POST[$nonce]) || !wp_verify_nonce($_POST[$nonce], $this->getId())) {
            return false;
        }
        return true;
    }

    abstract protected function saveFields($post_id): void;

    protected function getId(): string
    {
        return 'mb_' . sanitize_key(str_replace(['App\\Metaboxes\\', 'Metabox'], '', static::class));
    }
}