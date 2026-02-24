{{-- BLOCK SLIDE DYNAMIC 10/10 – Lấy dữ liệu thật từ CPT event + flags = breaking --}}
@props([
    'title'          => '🚨 Tin khẩn cấp (flags = breaking)',
    'post_type'      => 'event',
    'posts_per_page' => 8,
    'meta_query'     => [],
    'tax_query'      => [],
    'orderby'        => 'date',
    'meta_key'       => '',
    'order'          => 'DESC',
    'perPage'        => 3,
    'autoplay'       => true,
    'interval'       => 4000,
    'debug'          => true,          // Bật true để xem debug
])

@php
// === TẠO CACHE KEY AN TOÀN ===
$cache_params = [
    'post_type'      => $post_type,
    'posts_per_page' => $posts_per_page,
    'meta_query'     => $meta_query,
    'tax_query'      => $tax_query,
    'orderby'        => $orderby,
    'meta_key'       => $meta_key,
    'order'          => $order,
];
$cache_key = 'slide_event_' . md5(json_encode($cache_params, JSON_UNESCAPED_UNICODE));

$posts = get_transient($cache_key);

if (false === $posts) {
    $args = [
        'post_type'        => $post_type,
        'posts_per_page'   => $posts_per_page,
        'orderby'          => $orderby,
        'order'            => $order,
        'meta_query'       => $meta_query,
        'tax_query'        => $tax_query,
        'suppress_filters' => false,
    ];

    if ($orderby === 'meta_value_num' && $meta_key) {
        $args['meta_key'] = $meta_key;
    }

    $query = new WP_Query($args);
    $posts = $query->posts;

    set_transient($cache_key, $posts, 5 * MINUTE_IN_SECONDS);

    if ($debug) {
        error_log("DEBUG SLIDE → CPT: {$post_type} | Found: " . count($posts) . " posts | Cache key: {$cache_key}");
        if (empty($posts)) {
            error_log("DEBUG SLIDE → Query args: " . print_r($args, true));
        }
    }
}
@endphp

<div class="my-16">
    @if ($title)
        <h3 class="text-3xl font-bold mb-8">{{ $title }}</h3>
    @endif

    @if (empty($posts))
        <div class="bg-red-50 border border-red-200 p-8 rounded-3xl text-center">
            <p class="text-red-600">Không tìm thấy bài viết nào phù hợp.</p>
            <p class="text-xs text-red-500 mt-2">(Kiểm tra debug.log để xem lý do)</p>
        </div>
    @else
        <div 
            class="splide"
            data-splide-config='{ 
                "type": "loop",
                "perPage": {{ $perPage }},
                "autoplay": {{ $autoplay ? 'true' : 'false' }},
                "interval": {{ $interval }},
                "arrows": true,
                "pagination": true,
                "gap": "1.5rem",
                "lazyLoad": "nearby"
            }'
        >
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