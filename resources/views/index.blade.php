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

    {{-- BLOCK SLIDE --}}
    @include('partials.block-slide')
    
  @while(have_posts()) @php(the_post())
    @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
  @endwhile

  {!! get_the_posts_navigation() !!}
@endsection

@section('sidebar')
  @include('sections.sidebar')
@endsection
