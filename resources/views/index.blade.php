@extends('layouts.app')

@section('content')
    @include('partials.blocks.tabs-highlight')
    {{ sage_prefetch_link_posts($wp_query->posts ?? []) }}

    @php global $wp_query; $query = $wp_query; @endphp

    @include('partials.content-listing', ['query' => $query])

    @php
        $total_posts = $query->found_posts ?? 0;
    @endphp

    @if ($total_posts >= 3)
        <!-- LOAD MORE BUTTON -->
        <div class="text-center mt-12">
            <button id="load-more-btn"
                    class="px-12 py-4 bg-[#6697a1] hover:bg-[#55868f] text-white font-medium rounded-2xl transition-all flex items-center gap-3 mx-auto disabled:opacity-70"
                    data-offset="3"
                    data-ajaxurl="{{ admin_url('admin-ajax.php') }}"
                    data-nonce="{{ wp_create_nonce('load_more_nonce') }}">
                <span class="btn-text">Xem thêm 3 bài viết</span>
                <span class="loading hidden">
                    <svg class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </span>
            </button>
        </div>
    @endif
@endsection

@section('sidebar')
    @include('sections.sidebar')
@endsection