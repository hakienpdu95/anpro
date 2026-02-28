<div @php(post_class("col-span-1"))>
    <div class="blog-single-item">

        <div class="flex items-start flex-col lg:flex-row gap-3">

            <!-- 1. THUMBNAIL – link riêng -->
            <div class="w-[315px] h-[165px] blog-single-item-thumbnail overflow-hidden">
                {!! sage_post_link_open(get_post(), 'block w-full h-full', 'listing') !!}
                    {!! get_the_post_thumbnail($post->ID, 'medium_large', ['class' => 'w-full h-full object-cover']) !!}
                {!! sage_post_link_close() !!}
            </div>

            <!-- 2. NỘI DUNG BÊN PHẢI -->
            <div class="flex flex-col gap-y-1 flex-1">

                <span class="w-fit text-warning-dark bg-[rgba(255,193,7,0.16)] px-2 py-px mb-1 inline-flex rounded-full text-xs leading-[18px] blog-single-item-category">
                    Category Name
                </span>

                <!-- Meta -->
                <div class="flex flex-col gap-y-3 md:flex-row divide-x-0 md:divide-x divide-[rgba(145,158,171,0.24)]">
                    <p class="text-light-secondary-text text-sm leading-[22px] inline-flex items-center pr-0 md:pr-3 blog-single-item-post-time">
                        <span class="inline-flex items-center justify-center">
                            <i class="hgi hgi-stroke hgi-calendar-03 text-base leading-4 text-light-secondary-text"></i>
                        </span>
                        <span>12:40 PM, 09 Feb 2027</span>
                    </p>
                </div>

                {!! sage_post_link_open(get_post(), 'no-underline!', 'listing') !!}
                    <h5 class="text-light-primary-text hover:text-primary blog-single-item-title">
                        {!! get_the_title() !!}
                    </h5>
                {!! sage_post_link_close() !!}

                @if (trim(get_the_excerpt()))
                    <p class="mb-5 line-clamp-6 blog-single-item-description">
                        {{ get_the_excerpt() }}
                    </p>
                @endif

            </div>
        </div>
    </div>
</div>