@extends('layouts.app')

@section('content')
<section class="py-12">
    <div class="container max-w-6xl mx-auto px-4">
        @php
            global $wp_query;                       // ← FIX QUAN TRỌNG NHẤT
            $keyword = get_search_query();
            $time    = \App\Search\SearchManager::getQueryTime();
            $total   = $wp_query->found_posts ?? 0;
        @endphp

        <!-- Header -->
        <div class="mb-12 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-primary-900 mb-4">
                Kết quả tìm kiếm cho: 
                <span class="text-primary-600">"{{ esc_html($keyword) }}"</span>
            </h1>
            <p class="text-xl text-gray-600">
                Khoảng <strong class="text-primary-700">{{ number_format($total) }}</strong> kết quả 
                <span class="text-sm font-medium text-gray-500">({{ $time }} giây)</span>
            </p>
        </div>

        @if (have_posts())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @while (have_posts())
                    @php the_post(); @endphp
                    
                    @include('partials.blocks.article-thumb-grid', [
                        'posts' => [get_post()]
                    ])
                @endwhile
            </div>

            <!-- Pagination -->
            <div class="mt-16 flex justify-center">
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