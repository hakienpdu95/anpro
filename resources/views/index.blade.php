@extends('layouts.app')

@section('content')
  @include('partials.page-header')

  @if (! have_posts())
    <x-alert type="warning">
      {!! __('Sorry, no results were found.', 'sage') !!}
    </x-alert>

    {!! get_search_form(false) !!}
  @endif

  <h1 class="text-5xl font-bold text-center mb-16">Trang Chủ Demo – Sage 10/10</h1>

    {{-- BLOCK TABS --}}
    @include('partials.block-tabs')

    {{-- Demo Slider 3 item --}}
    @include('partials.block-slide', [
        'title' => '🔥 Tin nóng nổi bật hôm nay',
        'perPage' => 3,
    ])

    {{-- Demo Slider 1 item (full width) --}}
    @include('partials.block-slide', [
        'title' => 'Banner chính',
        'perPage' => 1,
        'arrows' => false,
        'pagination' => true,
        'interval' => 5000,
    ])

    {{-- 1. Tin nóng (flags = breaking) --}}
    @include('partials.block-slide-dynamic', [
        'title' => '🔥 Tin nóng hôm nay',
        'meta_query' => [
            ['key' => 'flags', 'value' => 'breaking', 'compare' => 'LIKE']
        ],
        'posts_per_page' => 6,
        'perPage' => 3,
    ])

    {{-- 2. Tin theo danh mục "event_cat" --}}
    @include('partials.block-slide-dynamic', [
        'title' => '📈 Kinh tế',
        'tax_query' => [
            ['taxonomy' => 'event_cat', 'field' => 'slug', 'terms' => 'kinh-te']
        ],
        'perPage' => 2,
    ])

    {{-- 3. Bài dài (reading_time >= 10) --}}
    @include('partials.block-slide-dynamic', [
        'title' => '📖 Bài đọc dài hay nhất',
        'meta_query' => [
            ['key' => 'reading_time', 'value' => 10, 'compare' => '>=', 'type' => 'NUMERIC']
        ],
        'orderby' => 'meta_value_num',
        'meta_key' => 'reading_time',
        'perPage' => 3,
    ])

  @while(have_posts()) @php(the_post())
    @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
  @endwhile

  {!! get_the_posts_navigation() !!}
@endsection

@section('sidebar')
  @include('sections.sidebar')
@endsection
