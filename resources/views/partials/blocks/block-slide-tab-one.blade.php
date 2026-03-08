@props([
    'posts'     => [],
    'perPage'   => 3,
    'autoplay'  => true,
    'interval'  => 4000,
])

@if (empty($posts))
    <div class="bg-red-50 border border-red-200 p-3 mb-4 text-center">
        <p class="text-red-600">Không tìm thấy bài viết nào phù hợp.</p>
    </div>
@else
    <div class="splide article-content mb-6" data-splide-config='{ "type": "loop", "perPage": {{ $perPage }}, "autoplay": {{ $autoplay ? 'true' : 'false' }}, "interval": {{ $interval }}, "arrows": true, "pagination": true, "gap": "1.5rem", "lazyLoad": "nearby" }'>
        <div class="splide__track">
            <ul class="splide__list">
                @foreach ($posts as $post)
                    <li class="splide__slide">
                        <div class="w-full article-content">
                            <!-- Thumbnail + link -->
                            <div class="w-full overflow-hidden mb-3">
                                @php $primary_flag = sage_get_primary_flag($post); @endphp
                                {!! sage_flag_badge($primary_flag) !!}

                                {!! sage_post_link_open($post, 'block w-full h-full', 'featured') !!}
                                    {!! sage_thumbnail('thumb-medium', [
                                        'class' => 'w-full h-full object-cover transition-transform duration-300'
                                    ], $post) !!}
                                {!! sage_post_link_close() !!}
                            </div>

                            <!-- Nội dung -->
                            <div class="w-full relative z-10">
                                {!! sage_post_link_open($post, 'no-underline!', 'featured') !!}
                                    <p class="font-medium mb-1">
                                        {!! get_the_title($post) !!}
                                    </p>
                                {!! sage_post_link_close() !!}

                                <ul class="flex space-x-2.5 items-center mb-5 info">
                                    <li>{!! sage_post_author_link($post, 'no-underline!') !!}</li>
                                    <li class="flex sm:space-x-2.5 space-x-2.5 items-center">
                                        {!! sage_post_date($post, true) !!}
                                    </li>
                                </ul>

                                @if (sage_excerpt($post))
                                    <p class="text-sm">
                                        {!! sage_excerpt($post) !!}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@php wp_reset_postdata(); @endphp