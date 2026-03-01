@props([
    'posts'      => [],
    'link_type'  => 'listing'
])

@if (empty($posts))
    <div class="bg-gray-50 border border-gray-200 p-8 rounded-3xl text-center col-span-full">
        <p class="text-gray-500">Không có bài viết nào.</p>
    </div>
@else
    <div class="grid grid-cols-4 gap-4 article-thumb">
        @foreach ($posts as $post)
            <div class="item group">
                <div class="w-full">
                    <!-- Thumbnail -->
                    <div class="w-full h-[100px] overflow-hidden relative mb-5 rounded-xl">
                        {!! sage_post_link_open($post, 'block w-full h-full', $link_type) !!}
                            {!! get_the_post_thumbnail($post->ID, 'medium_large', ['class' => 'w-full h-full object-cover transition-transform duration-300']) !!}
                        {!! sage_post_link_close() !!}
                    </div>

                    <!-- Meta -->
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

                    <!-- Title -->
                    {!! sage_post_link_open($post, 'no-underline!', $link_type) !!}
                        <h2 class="xl:text-lg xl:leading-7 text-md font-bold spline-sans text-primary-900 mb-5">
                            {!! get_the_title($post) !!}
                        </h2>
                    {!! sage_post_link_close() !!}
                </div>
            </div>
        @endforeach
    </div>
@endif