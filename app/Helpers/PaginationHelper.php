<?php namespace App\Helpers;

class PaginationHelper {

    /**
     * SMART PAGINATION 10/10 – Tối ưu cho 5 đến 5000+ trang
     * - ≤ 7 trang   → Hiển thị hết (1 2 3 4 5 6 7)
     * - ≥ 8 trang   → Thu gọn thông minh: 1 2 3 ... 8 9 10
     */
    public static function numberPagination(?\WP_Query $query = null): string
    {
        if (!$query) {
            global $wp_query;
            $query = $wp_query;
        }

        $total   = (int) $query->max_num_pages;
        $current = max(1, (int) $query->get('paged'));

        if ($total <= 1) return '';

        $output = '<div class="pagerblock flex justify-center mt-3"><ul class="page-numbers">';

        // Nút Trước
        if ($current > 1) {
            $output .= '<li>' . self::getLink($current - 1, '‹ Trước', 'prev page-numbers') . '</li>';
        }

        if ($total <= 7) {
            // Ít trang → hiển thị hết
            for ($i = 1; $i <= $total; $i++) {
                $output .= self::getPageItem($i, $current);
            }
        } else {
            // Nhiều trang → Smart Pagination
            $output .= self::buildSmartPagination($current, $total);
        }

        // Nút Sau
        if ($current < $total) {
            $output .= '<li>' . self::getLink($current + 1, 'Sau ›', 'next page-numbers') . '</li>';
        }

        $output .= '</ul></div>';
        return $output;
    }

    private static function buildSmartPagination(int $current, int $total): string
    {
        $output = '';

        $end_size = 2;   // Số trang đầu & cuối
        $mid_size = 2;   // Số trang hai bên trang hiện tại (đã giảm xuống 2 để gọn hơn với 10 trang)

        // Phần đầu
        for ($i = 1; $i <= min($end_size, $total); $i++) {
            $output .= self::getPageItem($i, $current);
        }

        // Ellipsis đầu
        if ($current > $end_size + $mid_size + 1) {
            $output .= '<li><span class="page-numbers dots">…</span></li>';
        }

        // Phần giữa (đối xứng quanh current)
        $start = max($end_size + 1, $current - $mid_size);
        $end   = min($total - $end_size, $current + $mid_size);

        for ($i = $start; $i <= $end; $i++) {
            $output .= self::getPageItem($i, $current);
        }

        // Ellipsis cuối
        if ($current + $mid_size < $total - $end_size) {
            $output .= '<li><span class="page-numbers dots">…</span></li>';
        }

        // Phần cuối
        $start_end = max($end + 1, $total - $end_size + 1);
        for ($i = $start_end; $i <= $total; $i++) {
            $output .= self::getPageItem($i, $current);
        }

        return $output;
    }

    private static function getPageItem(int $page, int $current): string
    {
        if ($page === $current) {
            return '<li class="current"><span aria-current="page" class="page-numbers">' . $page . '</span></li>';
        }
        return '<li>' . self::getLink($page, (string)$page) . '</li>';
    }

    private static function getLink(int $page, string $label, string $extra_class = ''): string
    {
        $base = (is_front_page() || is_home())
            ? trailingslashit(home_url()) . 'page/%#%/'
            : str_replace('999999999', '%#%', esc_url(get_pagenum_link(999999999)));

        $url = str_replace('%#%', $page, $base);
        $class = 'page-numbers';
        if ($extra_class) $class .= ' ' . $extra_class;

        return '<a class="' . $class . '" href="' . esc_url($url) . '">' . $label . '</a>';
    }
}