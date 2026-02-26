<?php
namespace App\Helpers;

class PaginationHelper {
    /**
     * Number pagination - ĐÃ FIX 404 CHO HOMEPAGE + CUSTOM QUERY
     */
    public static function numberPagination(?\WP_Query $query = null): string
    {
        if (!$query) {
            global $wp_query;
            $query = $wp_query;
        }

        if ($query->max_num_pages <= 1) {
            return '';
        }

        $big = 999999999;

        // === FIX ĐẶC BIỆT CHO HOMEPAGE ===
        if (is_front_page() || is_home()) {
            $base   = trailingslashit(home_url()) . 'page/%#%/';
            $format = '';
        } else {
            $base   = str_replace($big, '%#%', esc_url(get_pagenum_link($big)));
            $format = '';
        }

        return paginate_links([
            'base'      => $base,
            'format'    => $format,
            'current'   => max(1, $query->get('paged')),
            'total'     => $query->max_num_pages,
            'mid_size'  => 3,
            'end_size'  => 1,
            'prev_text' => '‹ Trước',
            'next_text' => 'Sau ›',
            'type'      => 'list',
            'before_page_number' => '<span class="sr-only">Trang </span>',
        ]);
    }
}