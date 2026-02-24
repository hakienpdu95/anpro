{{-- BLOCK SLIDE: Tin có CẢ breaking VÀ hot --}}
@props([
    'title' => '🚨 Tin nóng & Khẩn cấp',
    'post_type' => 'event',
    'posts_per_page' => 8,
    'perPage' => 3,
    'autoplay' => true,
    'interval' => 4000,
    'debug' => true,
])

@php
$posts = \App\Helpers\QueryHelper::getPostsWithAllFlags(
    $post_type, 
    ['breaking', 'hot'],   // ← thay đổi flags ở đây
    $posts_per_page
);

if ($debug) {
    error_log("=== DEBUG SLIDE FLAGS AND ===");
    error_log("Flags yêu cầu: " . implode(' + ', ['breaking', 'hot']));
    error_log("Số bài load được: " . count($posts));
}
@endphp

<div class="my-16">
    @if ($title)
        <h3 class="text-3xl font-bold mb-8">{{ $title }}</h3>
    @endif

    @if (empty($posts))
        <div class="bg-red-50 border border-red-200 p-8 rounded-3xl text-center">
            <p class="text-red-600">Không tìm thấy bài viết nào có cả breaking & hot.</p>
        </div>
    @else
        <div class="splide" data-splide-config='{ "type": "loop", "perPage": {{ $perPage }}, "autoplay": {{ $autoplay ? 'true' : 'false' }}, "interval": {{ $interval }}, "arrows": true, "pagination": true, "gap": "1.5rem", "lazyLoad": "nearby" }'>
            <div class="splide__track">
                <ul class="splide__list">
                    @foreach ($posts as $post)
                        @php setup_postdata($post); @endphp
                        <li class="splide__slide">
                            <!-- Phần HTML render slide của bạn giữ nguyên từ đây -->
                            <div class="bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all group">
                                @if (has_post_thumbnail($post->ID))
                                    <a href="{{ get_permalink($post) }}">
                                        {!! get_the_post_thumbnail($post->ID, 'medium_large', ['class' => 'w-full h-64 object-cover group-hover:scale-105 transition-transform']) !!}
                                    </a>
                                @endif
                                <div class="p-6">
                                    <h4 class="font-semibold text-xl leading-tight mb-3 line-clamp-2">
                                        <a href="{{ get_permalink($post) }}" class="hover:text-blue-600">{{ get_the_title($post) }}</a>
                                    </h4>
                                    <p class="text-gray-600 text-sm line-clamp-3 mb-4">
                                        {{ cmeta('subtitle', $post->ID) ?: wp_trim_words(get_the_excerpt($post), 25) }}
                                    </p>
                                    <div class="flex justify-between text-xs text-gray-500">
                                        <span>⏱️ {{ (int) cmeta('reading_time', $post->ID) }} phút</span>
                                        <span>{{ get_the_date('d/m/Y', $post) }}</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>

@php wp_reset_postdata(); @endphp