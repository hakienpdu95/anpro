@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    {{-- Dynamic title - SEO tốt + multilingual --}}
    <h1 class="text-4xl font-bold mb-10 text-center">
        {{ post_type_archive_title('', false) }}
    </h1>

    @php
        global $wp_query;
        $query = $wp_query;

        // Preload meta toàn bộ query (tận dụng CustomTableManager của bạn - zero extra query)
        \App\Database\CustomTableManager::preloadThePostsMeta($query->posts, $query);
    @endphp

    @if ($query->have_posts())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @while ($query->have_posts())
                @php 
                    $query->the_post(); 
                    $flags       = (array) cmeta('flags');
                    $subtitle    = cmeta('subtitle') ?? '';
                    $reading_time = (int) cmeta('reading_time');
                @endphp

                <article class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all">
                    {{-- Thumbnail với lazy + alt --}}
                    @if (has_post_thumbnail())
                        <a href="{{ get_the_permalink() }}" class="block">
                            {!! get_the_post_thumbnail(null, 'medium_large', [
                                'class'   => 'w-full h-56 object-cover',
                                'loading' => 'lazy',
                                'alt'     => esc_attr(get_the_title())
                            ]) !!}
                        </a>
                    @endif

                    <div class="p-6">
                        {{-- Flags --}}
                        @if ($flags)
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach ($flags as $flag)
                                    @if ($flag === 'hot')
                                        <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm">🔥 Nóng</span>
                                    @elseif ($flag === 'featured')
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">⭐ Nổi bật</span>
                                    @elseif ($flag === 'breaking')
                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">🚨 Khẩn cấp</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- Title --}}
                        <h2 class="text-xl font-semibold leading-tight mb-3">
                            <a href="{{ get_the_permalink() }}" rel="bookmark" class="hover:text-blue-600 transition">
                                {{ get_the_title() }}
                            </a>
                        </h2>

                        {{-- Subtitle --}}
                        @if ($subtitle)
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {{ esc_html($subtitle) }}
                            </p>
                        @endif

                        {{-- Meta --}}
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>{{ $reading_time }} phút đọc</span>
                            <span>{{ get_the_date('d/m/Y') }}</span>
                        </div>
                    </div>
                </article>
            @endwhile
        </div>

        {{-- Pagination nhất quán --}}
        <div class="mt-12 flex justify-center">
            {!! \App\Helpers\PaginationHelper::numberPagination($query) !!}
        </div>

    @else
        <div class="text-center py-20">
            <p class="text-xl text-gray-500">Không tìm thấy bài viết nào.</p>
        </div>
    @endif

    @php wp_reset_postdata(); @endphp
</div>
@endsection