<?php
namespace App\CMB2;

class Registrar
{
    public static function init(): void
    {
        // Force load CMB2 (bắt buộc trong Sage/Acorn)
        self::loadCMB2();

        // Đăng ký tất cả fields
        add_action('cmb2_admin_init', [self::class, 'registerAll']);
    }

    /**
     * Force load CMB2 init.php (rất quan trọng trong Sage)
     */
    private static function loadCMB2(): void
    {
        $init_file = get_theme_file_path('vendor/cmb2/cmb2/init.php');

        if (file_exists($init_file)) {
            require_once $init_file;
        } else {
            // Log lỗi nếu không tìm thấy (chỉ hiện trong debug)
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('=== [CMB2 ERROR] init.php NOT FOUND ===');
            }
        }
    }

    public static function registerAll(): void
    {
        if (!is_admin()) {
            return;
        }

        // Đăng ký các module fields
        \App\CMB2\Taxonomy\EventCategoryFields::register();
        \App\CMB2\ThemeOptions::register();
    }
}