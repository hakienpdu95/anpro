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
    @includeCached('partials.block-tabs', [], 3600, true)

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

    @includeCached('partials.block-slide-dynamic', [
        'title' => '🚨 Tin khẩn cấp',
        'post_type' => 'event',
        'perPage' => 3,
    ], 300)

  @while(have_posts()) @php(the_post())
    @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
  @endwhile

  {!! get_the_posts_navigation() !!}
@endsection

@section('sidebar')
  @include('sections.sidebar')
@endsection
