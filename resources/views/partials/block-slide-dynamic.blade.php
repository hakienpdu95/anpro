{{-- BLOCK SLIDE DYNAMIC 10/10 – Lấy dữ liệu thật từ CPT + Metabox + Taxonomy --}}
@props([
    'title'          => '🔥 Tin nóng nổi bật',
    'post_type'      => 'event',
    'posts_per_page' => 6,
    'meta_query'     => [],
    'tax_query'      => [],
    'orderby'        => 'date',
    'meta_key'       => '',
    'order'          => 'DESC',
    'perPage'        => 3,
    'autoplay'       => true,
    'interval'       => 4000,
])

@php
// === TẠO CACHE KEY AN TOÀN (không dùng func_get_args) ===
$cache_params = [
    'post_type'      => $post_type,
    'posts_per_page' => $posts_per_page,
    'meta_query'     => $meta_query,
    'tax_query'      => $tax_query,
    'orderby'        => $orderby,
    'meta_key'       => $meta_key,
    'order'          => $order,
];
$cache_key = 'slide_' . md5(json_encode($cache_params, JSON_UNESCAPED_UNICODE));

$posts = get_transient($cache_key);

if (false === $posts) {
    $args = [
        'post_type'        => $post_type,
        'posts_per_page'   => $posts_per_page,
        'orderby'          => $orderby,
        'order'            => $order,
        'meta_query'       => $meta_query,
        'tax_query'        => $tax_query,
        'suppress_filters' => false,   // Bắt buộc để CustomTableManager xử lý meta_query
    ];

    if ($orderby === 'meta_value_num' && $meta_key) {
        $args['meta_key'] = $meta_key;
    }

    $query = new WP_Query($args);
    $posts = $query->posts;

    // Cache 5 phút (có thể tăng lên 10-15 phút khi site lớn)
    set_transient($cache_key, $posts, 5 * MINUTE_IN_SECONDS);
}
@endphp

<div class="my-16">
    @if ($title)
        <h3 class="text-3xl font-bold mb-8">{{ $title }}</h3>
    @endif

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
                                    {!! get_the_post_thumbnail($post->ID, 'medium_large', ['class' => 'w-full h-64 object-cover group-hover:scale-105 transition-transform duration-500']) !!}
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
</div>

@php wp_reset_postdata(); @endphp