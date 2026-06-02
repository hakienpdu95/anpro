@php
$related = new WP_Query([
    'post_type'      => get_post_type(),
    'posts_per_page' => 3,
    'post__not_in'   => [get_the_ID()],
    'orderby'        => 'rand'
]);
@endphp

@if ($related->have_posts())
<div class="mt-16 border-t pt-12">
    <h3 class="text-2xl font-semibold mb-8">Bài viết liên quan</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @while ($related->have_posts()) @php $related->the_post(); @endphp
            @include('partials.post-card')   {{-- dùng card chung từ archive --}}
        @endwhile
    </div>
</div>
@endif
@php wp_reset_postdata(); @endphp