@extends('layouts.app')

@section('content')
<section>
    @php
        global $wp_query;    
        nocache_headers();            
        $keyword = get_search_query();
        $time    = \App\Search\SearchManager::getQueryTime();
        $total   = $wp_query->found_posts ?? 0;
    @endphp    
    <header class="entry-header">
      <h1 class="entry-title" itemprop="headline">Kết quả tìm kiếm cho: {{ esc_html($keyword) }}</h1>
    </header>

    <div class="entry-content">
        @include('partials.search-form')
        @if (have_posts())
        <div id="search-content">
            <div class="search-stats">Có {{ number_format($total) }} kết quả. ({{ $time }} seconds)</div>
            @while (have_posts())
                @php the_post(); @endphp
                <div class="pst-srch">
                    <h2>
                        {!! sage_post_link_open(get_post(), 'no-underline!', 'search-type') !!}
                            {!! get_the_title(get_post()) !!}
                        {!! sage_post_link_close() !!}
                    </h2>
                    @php
                        $link = sage_post_link(get_post(), 'search');
                    @endphp
                    <a href="{{ $link['url'] }}" 
                       class="text-sm text-blue-600 hover:text-blue-700 break-all block mt-2">
                        {{ $link['url'] }}
                    </a>

                    @if (sage_excerpt(get_post()))
                        <p class="entry-meta">
                            {!! sage_excerpt(get_post()) !!}
                        </p>
                    @endif
                </div>
            @endwhile            
        </div>

        <div class="search-pagin">
            {!! \App\Helpers\PaginationHelper::numberPagination($wp_query) !!}
        </div>

        @else

        <!-- No results -->
        <div class="text-center py-24 bg-gray-50 rounded-3xl border border-gray-100">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <span class="text-5xl">🔍</span>
            </div>
            <p class="text-2xl font-semibold text-gray-700 mb-3">Không tìm thấy kết quả nào</p>
            <p class="text-gray-500 max-w-md mx-auto">
                Không có bài viết nào khớp với từ khóa "<strong>{{ esc_html($keyword) }}</strong>". 
                Hãy thử từ khóa khác hoặc xem các bài viết nổi bật bên dưới.
            </p>
        </div>
        @endif
    </div>
</section>
@endsection

@section('sidebar')
    @include('sections.sidebar')
@endsection