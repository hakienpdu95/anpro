<?php

namespace App\PostTypes;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use function Extended_CPTs\register_extended_post_type;  

abstract class BasePostType
{
    protected string $post_type;
    protected string $singular;
    protected string $plural;

    public function __construct()
    {
        $this->post_type = $this->getPostTypeKey();
        $this->singular  = $this->getSingular();
        $this->plural    = $this->getPlural();
    }

    abstract protected function getPostTypeKey(): string;
    abstract protected function getSingular(): string;
    abstract protected function getPlural(): string;

    public function register()
    {
        // Sử dụng FULL NAMESPACE + CHECK AN TOÀN (không crash nữa)
        if (function_exists('\\Extended_CPTs\\register_extended_post_type')) {
            \Extended_CPTs\register_extended_post_type(
                $this->post_type,
                $this->getArgs(),
                $this->getLabels()
            );
        } else {
            // Fallback an toàn + log lỗi
            error_log('Extended CPTs chưa load. Vui lòng chạy: composer install trong thư mục theme.');
            // Có thể dùng native register_post_type tạm thời nếu muốn
            register_post_type($this->post_type, $this->getArgs());
        }

        // Register metabox (vẫn giữ conditional để performance cao)
        add_action('carbon_fields_register_fields', [$this, 'registerMetaboxes']);
    }

    protected function getArgs(): array
    {
        return [
            'public'       => true,
            'show_in_rest' => false,        // Tắt vì anh dùng Classic Editor
            'has_archive'  => true,
            'rewrite'      => ['slug' => $this->post_type],
            'supports'     => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions', 'page-attributes'],
            'menu_position' => 5,
        ];
    }

    protected function getLabels(): array
    {
        return []; // Extended CPTs tự generate label tiếng Việt đẹp
    }

    public function registerMetaboxes()
    {
        Container::make('post_meta', 'Thông tin bổ sung')
            ->where('post_type', '=', $this->post_type)
            ->add_tabs($this->getMetaboxTabs());
    }

    abstract protected function getMetaboxTabs(): array;
}