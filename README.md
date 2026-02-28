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