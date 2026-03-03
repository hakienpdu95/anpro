<?php namespace App\Helpers;

class PaginationHelper {

    /**
     * Number pagination - Class "current" nằm trực tiếp trong thẻ <li>
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

        // Fix đặc biệt cho homepage
        if (is_front_page() || is_home()) {
            $base   = trailingslashit(home_url()) . 'page/%#%/';
            $format = '';
        } else {
            $base   = str_replace($big, '%#%', esc_url(get_pagenum_link($big)));
            $format = '';
        }

        // Lấy links dưới dạng array để tùy chỉnh
        $links = paginate_links([
            'base'               => $base,
            'format'             => $format,
            'current'            => max(1, $query->get('paged')),
            'total'              => $query->max_num_pages,
            'mid_size'           => 3,
            'end_size'           => 1,
            'prev_text'          => '‹ Trước',
            'next_text'          => 'Sau ›',
            'type'               => 'array',        
            'before_page_number' => '<span class="sr-only">Trang </span>',
        ]);

        if (empty($links)) {
            return '';
        }

        $output = '<div class="pagerblock flex justify-center mt-3">';
        $output .= '<ul class="page-numbers">';

        foreach ($links as $link) {
            if (strpos($link, 'aria-current="page"') !== false || strpos($link, 'current') !== false) {
                $output .= '<li class="current">' . $link . '</li>';
            } else {
                $output .= '<li>' . $link . '</li>';
            }
        }

        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}