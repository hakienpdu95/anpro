<?php

namespace App\Metaboxes;

use App\Database\CustomTableManager;

abstract class BaseMetabox
{
    protected string $id;
    protected string $title = 'Thông tin bổ sung';
    protected array $post_types = ['post'];
    protected string $context = 'normal';
    protected string $priority = 'high';

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

        $table_name = CustomTableManager::getTableName($instance->post_types[0]);

        $meta_boxes[] = [
            'id'            => $instance->id,
            'title'         => $instance->title,
            'post_types'    => $instance->post_types,
            'context'       => $instance->context,
            'priority'      => $instance->priority,
            'autosave'      => true,

            // LƯU TRỰC TIẾP VÀO BẢNG CUSTOM - KHÔNG LƯU VÀO wp_postmeta
            // 'storage_type'  => 'custom_table',
            // 'table'         => $table_name,

            'fields'        => $instance->getFields(),
        ];

        // Lưu registry để force hiển thị metabox
        foreach ($instance->post_types as $pt) {
            self::$registry[$pt][] = $instance->id;
        }

        // Đăng ký để tạo bảng
        foreach ($instance->post_types as $pt) {
            CustomTableManager::register($pt);
        }

        return $meta_boxes;
    }

    public static function getRegisteredIds(string $post_type): array
    {
        return self::$registry[$post_type] ?? [];
    }

    abstract protected function getFields(): array;
}