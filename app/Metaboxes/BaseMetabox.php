<?php

namespace App\Metaboxes;

abstract class BaseMetabox
{
    protected string $id;
    protected string $title = 'Thông tin bổ sung';
    protected array $post_types = ['post'];
    protected string $context = 'normal';
    protected string $priority = 'high';

    // Registry tự động để quản lý tất cả metabox IDs theo post type
    protected static array $registry = [];

    public function __construct()
    {
        $this->id = $this->getId();
    }

    protected function getId(): string
    {
        $class = (new \ReflectionClass($this))->getShortName();
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $class));
    }

    public static function addMetabox(array $meta_boxes): array
    {
        $instance = new static();

        $meta_boxes[] = [
            'id'         => $instance->id,
            'title'      => $instance->title,
            'post_types' => $instance->post_types,
            'context'    => $instance->context,
            'priority'   => $instance->priority,
            'autosave'   => true,
            'fields'     => $instance->getFields(),
        ];

        // Tự động đăng ký ID vào registry (không cần hardcode nữa)
        foreach ($instance->post_types as $pt) {
            self::$registry[$pt][] = $instance->id;
        }

        return $meta_boxes;
    }

    // Lấy danh sách metabox IDs theo post type
    public static function getRegisteredIds(string $post_type): array
    {
        return self::$registry[$post_type] ?? [];
    }

    abstract protected function getFields(): array;
}