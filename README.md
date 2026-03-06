<p align="center">
  <a href="https://roots.io/sage/"><img alt="Sage" src="https://cdn.roots.io/app/uploads/logo-sage.svg" height="100"></a>
</p>

<p align="center">
  <a href="https://packagist.org/packages/roots/sage"><img alt="Packagist Installs" src="https://img.shields.io/packagist/dt/roots/sage?label=projects%20created&colorB=2b3072&colorA=525ddc&style=flat-square"></a>
  <a href="https://github.com/roots/sage/actions/workflows/main.yml"><img alt="Build Status" src="https://img.shields.io/github/actions/workflow/status/roots/sage/main.yml?branch=main&logo=github&label=CI&style=flat-square"></a>
  <a href="https://bsky.app/profile/roots.dev"><img alt="Follow roots.dev on Bluesky" src="https://img.shields.io/badge/follow-@roots.dev-0085ff?logo=bluesky&style=flat-square"></a>
</p>

# Sage

**Advanced hybrid WordPress starter theme with Laravel Blade and Tailwind CSS**
## Xóa cache transient (nếu cần): wp transient delete --all
## Chỉ chạy lệnh này khi muốn trở về git gần nhất trước đó, bỏ qua các thay đổi đang làm hiện tại
git restore .
git clean -fd

composer dump-autoload -o
npm run build

## Cách dùng repeater trong Blade:
@php $gallery = cmeta('gallery'); @endphp
@if ($gallery)
    @foreach ($gallery as $item)
        <img src="{{ wp_get_attachment_url($item['image']) }}" alt="{{ $item['caption'] ?? '' }}" loading="lazy">
    @endforeach
@endif

## Xóa cache Vite + build lại (bắt buộc) file vite
rm -rf node_modules/.vite
rm -rf public/build
npm install
npm run build

## WP-COnfig File
define('FORCE_HTML_MINIFY', true);   
define('WP_REDIS_HOST', '127.0.0.1');
define('WP_REDIS_PORT', 6379);
define('WP_REDIS_TIMEOUT', 1);
define('WP_REDIS_READ_TIMEOUT', 1);
define('WP_REDIS_DATABASE', 0); // thay đổi nếu bạn có nhiều site
define('WP_REDIS_PERSISTENT', true);

## Cách dùng cho trang khác (archive, custom page…)
@php
    $paged = get_query_var('paged') ?: 1;
    $query = \App\Queries\MergedPostsQuery::get([
        'posts_per_page' => 12,
        'paged'          => $paged,
        // 'tax_query' => [...],
        // 'use_cache' => false, // test
    ]);
@endphp

@include('partials.content-listing', ['query' => $query])
{!! \App\Helpers\PaginationHelper::numberPagination($query) !!}

## Hoặc dùng reusable get() ở bất kỳ đâu (sidebar, related, custom page…):
@php
    $query = \App\Queries\MergedPostsQuery::get([
        'post_types'     => ['event'],
        'posts_per_page' => 12,
        'paged'          => get_query_var('paged', 1),
        // 'tax_query' => [...],
    ]);
@endphp


## Cách dùng trong Blade để gọi các field trong term
@php
    $term_id = get_queried_object_id();
@endphp

<h1>{{ get_term_meta($term_id, 'general_title', true) ?: single_term_title('', false) }}</h1>

<p>{!! wpautop(get_term_meta($term_id, 'general_description', true)) !!}</p>

<!-- Banner 1 -->
@if ($img1 = get_term_meta($term_id, 'banner_1_image', true))
    <a href="{{ get_term_meta($term_id, 'banner_1_link', true) }}">
        <img src="{{ esc_url($img1) }}" alt="{{ get_term_meta($term_id, 'banner_1_title', true) }}">
    </a>
@endif


## Cách dùng trong Blade gọi theme option
<img src="{{ theme_option('logo') }}" alt="Logo">

@if (theme_option('header_sticky'))
    <header class="sticky">...</header>
@endif

<p>{{ theme_option('footer_copyright') }}</p>

## Gọi soccial 
{!! sage_social_icons('social_navigation', 'flex items-center gap-6 text-3xl') !!}

## Phiên bản Full Card Wrapper sử dụng helper bọc link có redirect
{!! sage_post_link_open() !!}
    <div class="card bg-white rounded-2xl overflow-hidden shadow hover:shadow-2xl transition-all duration-300 group">
        <div class="relative">
            {!! get_the_post_thumbnail(null, 'large', ['class' => 'w-full h-56 object-cover']) !!}
            @if (sage_post_link()['is_external'])
                <span class="absolute top-4 right-4 bg-red-600 text-white text-xs px-3 py-1 rounded-full font-medium">↗ External</span>
            @endif
        </div>
        <div class="p-6">
            <h3 class="text-2xl font-semibold mb-3 line-clamp-2 group-hover:text-primary">
                {!! get_the_title() !!}
            </h3>
            <div class="text-gray-600 line-clamp-3">
                @php(the_excerpt())
            </div>
        </div>
    </div>
{!! sage_post_link_close() !!}

## REVISION OPTIMIZER
define('WP_POST_REVISIONS', 3);        // Giữ tối đa 3 revision gần nhất (rất đủ cho site tin tức)
define('AUTOSAVE_INTERVAL', 180);      // Tăng thời gian autosave lên 3 phút (giảm tạo revision)
// define('WP_POST_REVISIONS', false); // Tắt hoàn toàn revision (nếu bạn không cần khôi phục)

## Link_type gợi ý (dùng trong GTM để phân biệt)

Trang chủ / archive / category / tag → 'archive'
Related posts → 'related'
Bài nổi bật / featured → 'featured'
Tìm kiếm → 'search'
Menu / widget → 'menu'

## 'pinned_first' => true là gì & hoạt động thế nào?

Mục đích:
Ưu tiên bài viết được admin “Ghim nổi bật” (is_pinned = 1) lên đầu tiên trong slider/tab, dù bài đó đăng lâu hơn các bài khác.
Đây là tính năng rất hay cho tin tức/hot news (bài ghim luôn ở vị trí top).
Cách hoạt động thực tế:
Nếu bật pinned_first => true:
Tự động lọc chỉ những bài có:
is_pinned = '1'
pinned_until >= thời gian hiện tại (chưa hết hạn)

Sắp xếp: Tất cả bài ghim lên đầu → sau đó là bài mới nhất (date DESC).

Nếu không bật → sắp xếp bình thường theo date.

Nó hoàn toàn tự động, không làm chậm hệ thống nhờ cache version của bạn.

## Thêm Index DB (chạy 1 lần – tăng tốc pinned x15–25 lần)

-- Cho bảng event
ALTER TABLE `wp_event_meta` 
ADD INDEX `idx_pinned_flags` (`meta_key`(50), `meta_value`(20), `post_id`);

-- Cho bảng post mặc định
ALTER TABLE `wp_post_custom_meta` 
ADD INDEX `idx_pinned_flags` (`meta_key`(50), `meta_value`(20), `post_id`);

## CHẠY 1 LẦN – TẠO INDEX DB (TĂNG TỐC 10–20 LẦN)
-- Cho CPT event
ALTER TABLE `wp_event_meta` 
ADD INDEX `idx_search_meta` (`meta_key`(50), `meta_value`(191)),
ADD INDEX `idx_search_post` (`post_id`, `meta_key`(50));

-- Cho CPT post mặc định
ALTER TABLE `wp_post_custom_meta` 
ADD INDEX `idx_search_meta` (`meta_key`(50), `meta_value`(191)),
ADD INDEX `idx_search_post` (`post_id`, `meta_key`(50));

ALTER TABLE `wp_event_meta` 
  ADD INDEX `idx_post_views` (`meta_key`(20), `meta_value`(20), `post_id`);

ALTER TABLE `wp_post_custom_meta` 
  ADD INDEX `idx_post_views` (`meta_key`(20), `meta_value`(20), `post_id`);


## Cách dùng mới (khuyến nghị – ngắn gọn nhất)
'slide' => [
    'post_type'      => 'event',
    'flags'          => ['hot'],
    'category'       => 'medical-device',     // slug
    // 'category'    => 45,                    // term_id (tag_id)
    // 'event-categories' => [12, 34],         // nhiều term_id
    // 'post_tag'    => 'tag-slug',            // tag
    'pinned_first'   => true,
    'posts_per_page' => 8,
],

## Cách cũ (vẫn giữ nguyên, đầy đủ hơn)
'tax_query' => [
    [
        'taxonomy' => 'category',      // hoặc 'event-categories', 'post_tag'...
        'field'    => 'term_id',       // hoặc 'slug', 'id', 'name'
        'terms'    => 45,              // hoặc ['medical-device'], hoặc [12,34]
        'operator' => 'IN'
    ]
],

Muốn nhiều danh mục cùng lúc?
Ví dụ: 'event-categories' => ['medical-device', 'first-aid']
Muốn dùng tax_query cũ (nếu cần phức tạp hơn)?
Vẫn giữ nguyên như trước: 'tax_query' => [ ['taxonomy' => 'event-categories', 'field' => 'slug', 'terms' => 'medical-device'] ]

## Hiển thị số view (dùng ở bất kỳ đâu)
{!! sage_hot_badge($post) !!}
<span class="text-sm text-gray-500">👁️ {{ sage_views($post) }} lượt xem</span>

## File: partials/content.blade.php (nếu bạn có hiển thị tác giả ở đây)
<li>{!! sage_post_author_link($post, 'text-sm') !!}</li>

## Cách sử dụng gọi khối data từ theme-options
@php
$block1 = theme_option('widget_block_1');
$block2 = theme_option('widget_block_2');
@endphp

<!-- Khối 1 -->
@if (!empty($block1['image']))
    <a href="{{ $block1['link'] ?? '#' }}" {{ !empty($block1['new_tab']) ? 'target="_blank"' : '' }}>
        <img src="{{ wp_get_attachment_url($block1['image']) }}" alt="{{ $block1['title'] ?? '' }}">
    </a>
@endif

<!-- Khối 2 tương tự -->