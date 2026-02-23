<?php

namespace App\Watermark;

class WatermarkHandler
{
    private $watermark_path;

    private $position   = 'bottom-right';   // bottom-right, bottom-left, center...
    private $opacity    = 68;               // độ mờ 0-100
    private $margin     = 2;                // ← CHỈ CÁCH 2PX NHƯ YÊU CẦU
    private $scale      = 0.10;             // ← CHỈ RỘNG 20% SO VỚI ẢNH GỐC

    private $min_width  = 800;              // Chỉ watermark size >= 800px (tiết kiệm tài nguyên)

    private $post_types = ['post', 'tin-tuc'];

    public function __construct()
    {
        $paths = [
            get_theme_file_path('public/watermark.png'),
            get_theme_file_path('resources/images/watermark.png'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $this->watermark_path = $path;
                break;
            }
        }
    }

    public function register()
    {
        add_filter('wp_generate_attachment_metadata', [$this, 'addWatermark'], 999, 2);
    }

    public function addWatermark($metadata, $attachment_id)
    {
        if (empty($this->watermark_path) || !wp_attachment_is_image($attachment_id)) {
            return $metadata;
        }

        $parent_id = wp_get_post_parent_id($attachment_id);
        if ($parent_id && !in_array(get_post_type($parent_id), $this->post_types)) {
            return $metadata;
        }

        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            return $metadata;
        }

        // 1. LUÔN WATERMARK FILE GỐC
        if (!empty($metadata['width']) && $metadata['width'] >= $this->min_width) {
            $this->apply($file, $metadata['width'], $metadata['height'] ?? 0);
        }

        // 2. CHỈ WATERMARK CÁC SIZE LỚN (>= 800px)
        if (!empty($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size) {
                if ($size['width'] >= $this->min_width) {
                    $size_file = dirname($file) . '/' . $size['file'];
                    if (file_exists($size_file)) {
                        $this->apply($size_file, $size['width'], $size['height']);
                    }
                }
            }
        }

        return $metadata;
    }

    private function apply($image_path, $width, $height)
    {
        if (extension_loaded('imagick') && class_exists('Imagick')) {
            $this->applyWithImagick($image_path, $width, $height);
        } else {
            $this->applyWithGD($image_path, $width, $height);
        }
    }

    private function applyWithImagick($image_path, $width, $height)
    {
        try {
            $image = new \Imagick($image_path);
            $watermark = new \Imagick($this->watermark_path);

            $wm_width = (int)($width * $this->scale);
            $watermark->resizeImage($wm_width, 0, \Imagick::FILTER_LANCZOS, 1);
            $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $this->opacity / 100, \Imagick::CHANNEL_ALPHA);

            $x = $width - $watermark->getImageWidth() - $this->margin;
            $y = $height - $watermark->getImageHeight() - $this->margin;

            $image->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);
            $image->writeImage($image_path);

            $image->clear();
            $watermark->clear();
        } catch (\Exception $e) {
            error_log('[Watermark Imagick Error] ' . $e->getMessage());
        }
    }

    private function applyWithGD($image_path, $width, $height)
    {
        $ext = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        $image = ($ext === 'png') ? imagecreatefrompng($image_path) : imagecreatefromjpeg($image_path);
        $watermark = imagecreatefrompng($this->watermark_path);

        $wm_width = (int)($width * $this->scale);
        $wm_height = imagesy($watermark) * ($wm_width / imagesx($watermark));

        $resized = imagescale($watermark, $wm_width, $wm_height);

        $x = $width - $wm_width - $this->margin;
        $y = $height - $wm_height - $this->margin;

        imagealphablending($image, true);
        imagecopy($image, $resized, $x, $y, 0, 0, $wm_width, $wm_height);

        if ($ext === 'png') {
            imagepng($image, $image_path, 9);
        } else {
            imagejpeg($image, $image_path, 93);
        }

        imagedestroy($image);
        imagedestroy($watermark);
        imagedestroy($resized);
    }
}