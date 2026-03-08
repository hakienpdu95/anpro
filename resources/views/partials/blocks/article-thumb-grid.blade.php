@props([
    'posts'      => [],
    'link_type'  => 'listing'
])

@if (empty($posts))
    <div class="bg-gray-50 border border-gray-200 p-3 text-center col-span-full">
        <p class="text-gray-500">Không có bài viết nào.</p>
    </div>
@else
    <div class="grid sm:grid-cols-4 grid-cols-1 gap-4 article-thumb">
        @foreach ($posts as $post)
            <div class="item group">
                <div class="w-full">
                    <!-- Thumbnail -->
                    <div class="w-full sm:h-[100px] h-[205px] overflow-hidden relative mb-1">
                        {!! sage_post_link_open($post, 'block w-full h-full', $link_type) !!}
                            {!! sage_thumbnail('thumb-small', [
                                'class' => 'w-full h-full object-cover transition-transform duration-300'
                            ], $post) !!}
                        {!! sage_post_link_close() !!}
                    </div>

                    <!-- Meta -->
                    <ul class="flex space-x-2.5 items-center mb-5 info">
                        <li>{!! sage_post_author_link($post, 'no-underline!') !!}</li>
                        <li class="flex sm:space-x-2.5 space-x-2.5 items-center">
                            {!! sage_post_date($post) !!}
                        </li>
                    </ul>

                    <!-- Title -->
                    {!! sage_post_link_open($post, 'no-underline!', $link_type) !!}
                        <h2 class="font-medium">
                            {!! get_the_title($post) !!}
                        </h2>
                    {!! sage_post_link_close() !!}
                </div>
            </div>
        @endforeach
    </div>
@endif