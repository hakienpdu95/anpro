<?php
namespace App\Helpers;

class PaginationHelper {
    /**
     * Number pagination đẹp cho custom WP_Query (đã fix type hint)
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

        return paginate_links([
            'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'format'    => '/page/%#%',
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