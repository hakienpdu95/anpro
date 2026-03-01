@props([
    'posts'     => [],
    'perPage'   => 3,
    'autoplay'  => true,
    'interval'  => 4000,
])

@if (empty($posts))
    <div class="bg-red-50 border border-red-200 p-8 rounded-3xl text-center">
        <p class="text-red-600">Không tìm thấy bài viết nào phù hợp.</p>
    </div>
@else
    <div class="splide" data-splide-config='{ "type": "loop", "perPage": {{ $perPage }}, "autoplay": {{ $autoplay ? 'true' : 'false' }}, "interval": {{ $interval }}, "arrows": true, "pagination": true, "gap": "1.5rem", "lazyLoad": "nearby" }'>
        <div class="splide__track">
            <ul class="splide__list">
                @foreach ($posts as $post)
                    <li class="splide__slide">
                        <div class="w-full article-content">
                            <!-- Thumbnail + link -->
                            <div class="w-full h-[350px] overflow-hidden rounded-2xl">
                                {!! sage_post_link_open($post, 'block w-full h-full', 'featured') !!}
                                    {!! get_the_post_thumbnail($post->ID, 'medium_large', ['class' => 'w-full h-full object-cover transition-transform duration-300']) !!}
                                {!! sage_post_link_close() !!}
                            </div>

                            <!-- Nội dung -->
                            <div class="w-full sm:px-[30px] -mt-[30px] relative z-10">
                                {!! sage_post_link_open($post, 'no-underline!', 'featured') !!}
                                    <p class="sm:text-[30px] font-semibold spline-sans sm:leading-9 text-2xl text-primary-900 mb-2">
                                        {!! get_the_title($post) !!}
                                    </p>
                                {!! sage_post_link_close() !!}

                                <ul class="flex space-x-5 items-center mb-5">
                                    <li><span class="sm:text-base sm:leading-[27px] text-sm text-primary-100">By Admin</span></li>
                                    <li class="flex sm:space-x-5 space-x-2.5 items-center">
                                        <div class="w-2.5 h-2.5 rounded-full bg-primary-500"></div>
                                        <span class="sm:text-base sm:leading-[27px] text-sm text-primary-100">Category</span>
                                    </li>
                                    <li class="flex sm:space-x-5 space-x-2.5 items-center">
                                        <div class="w-2.5 h-2.5 rounded-full bg-primary-500"></div>
                                        <span class="sm:text-base sm:leading-[27px] text-sm text-primary-100">Comment</span>
                                    </li>
                                </ul>

                                @php $excerpt = get_the_excerpt($post); @endphp
                                @if (trim($excerpt))
                                    <p class="sm:text-base text-primary-100 text-sm sm:leading-[27px] mb-[30px]">
                                        {{ $excerpt }}
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