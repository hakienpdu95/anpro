<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

// ==================== IMAGE LINK CARD ====================
Block::make('image_link_card', 'Card Ảnh & Link')
    ->set_category('common', 'Common', 'flag')
    ->set_icon('format-image')
    ->add_fields([
        Field::make('image', 'image', 'Ảnh đại diện')
             ->set_value_type('id'),

        Field::make('text', 'title', 'Tiêu đề')
             ->set_required(true)
             ->set_default_value('Tiêu đề card'),

        Field::make('text', 'link', 'Link')
             ->set_attribute('type', 'url') 
             ->set_required(true),

        Field::make('text', 'button_text', 'Text nút')
             ->set_default_value('Xem chi tiết'),
    ])
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        echo view('blocks.image-link-card', [
            'fields'     => $fields,
            'attributes' => $attributes,
        ])->render();
    });