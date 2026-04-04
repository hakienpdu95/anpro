<div @php(post_class('col-span-1'))>
    <div class="blog-single-item">
        <div class="flex items-start flex-col lg:flex-row gap-3">
            <!-- Thumbnail (giữ sage_thumbnail → có placeholder tự động) -->
            <div class="sm:w-[315px] w-full sm:h-[165px] h-[205px] blog-single-item-thumbnail overflow-hidden">
                {!! sage_post_link_open(get_post(), 'block w-full h-full', 'listing') !!}
                {!! sage_thumbnail('thumb-medium') !!}
                {!! sage_post_link_close() !!}
            </div>

            <!-- Nội dung (giữ hết sage_ helper) -->
            <div class="flex flex-col gap-y-1 flex-1">
                {!! sage_post_link_open(get_post(), 'no-underline!', 'listing') !!}
                <h5 class="text-light-primary-text hover:text-primary blog-single-item-title">
                    {!! get_the_title() !!}
                </h5>
                {!! sage_post_link_close() !!}

                <ul class="flex space-x-2.5 items-center mb-5 info">
                    <li>{!! sage_post_author_link($post, 'no-underline!') !!}</li>
                    <li class="flex sm:space-x-2.5 space-x-2.5 items-center">
                        {!! sage_post_date($post, false, true) !!}
                    </li>
                </ul>

                @if (sage_excerpt(get_post()))
                    <p class="text-sm">{!! sage_excerpt(get_post()) !!}</p>
                @endif
            </div>
        </div>
    </div>
</div>