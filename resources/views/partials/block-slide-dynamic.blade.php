{{-- BLOCK SLIDE DYNAMIC 10/10 – Query trực tiếp từ custom table (bypass filterPostsClauses) --}}
@props([
    'title'          => '🚨 Tin khẩn cấp (flags = breaking)',
    'post_type'      => 'event',
    'posts_per_page' => 8,
    'perPage'        => 3,
    'autoplay'       => true,
    'interval'       => 4000,
    'debug'          => true,
])

@php
// === QUERY TRỰC TIẾP TỪ CUSTOM TABLE (đảm bảo 100% lấy được data) ===
global $wpdb;
$table = \App\Database\CustomTableManager::getTableName($post_type);

$post_ids = $wpdb->get_col($wpdb->prepare(
    "SELECT DISTINCT post_id 
     FROM `$table` 
     WHERE meta_key = %s 
       AND meta_value = %s 
     LIMIT %d",
    'flags',
    'breaking',
    $posts_per_page
));

$posts = [];
if (!empty($post_ids)) {
    $posts = get_posts([
        'post_type'      => $post_type,
        'post__in'       => $post_ids,
        'posts_per_page' => $posts_per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'suppress_filters' => false,
    ]);
}

if ($debug) {
    error_log("=== DEBUG SLIDE EVENT DIRECT QUERY ===");
    error_log("Post Type: {$post_type}");
    error_log("Post IDs tìm thấy: " . implode(', ', $post_ids));
    error_log("Số bài viết load được: " . count($posts));
    error_log("=========================");
}
@endphp

<div class="my-16">
    @if ($title)
        <h3 class="text-3xl font-bold mb-8">{{ $title }}</h3>
    @endif

    @if (empty($posts))
        <div class="bg-red-50 border border-red-200 p-8 rounded-3xl text-center">
            <p class="text-red-600">Không tìm thấy bài viết nào có flags = "breaking".</p>
            <p class="text-xs text-red-500 mt-2">Kiểm tra debug.log để xem chi tiết</p>
        </div>
    @else
        <div class="splide" data-splide-config='{ 
            "type": "loop",
            "perPage": {{ $perPage }},
            "autoplay": {{ $autoplay ? 'true' : 'false' }},
            "interval": {{ $interval }},
            "arrows": true,
            "pagination": true,
            "gap": "1.5rem",
            "lazyLoad": "nearby"
        }'>
            <div class="splide__track">
                <ul class="splide__list">
                    @foreach ($posts as $post)
                        @php setup_postdata($post); @endphp
                        <li class="splide__slide">
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