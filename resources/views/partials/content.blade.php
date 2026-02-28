<div @php(post_class("col-span-1"))>
    <div class="border border-gray-300 rounded-2xl p-6 hover:transform hover:translate-y-[-5px] hover:transition-all hover:ease-[cubic-bezier(0.02,0.01,0.47,1)] hover:duration-250 transition-all ease-[cubic-bezier(0.02,0.01,0.47,1)] duration-250 blog-single-item">

        <div class="flex items-center flex-col lg:flex-row gap-10">

            <!-- 1. THUMBNAIL – link riêng -->
            <div class="w-full lg:max-w-[500px] h-[380px] blog-single-item-thumbnail overflow-hidden rounded-2xl">
                {!! sage_post_link_open(get_post(), 'block w-full h-full', 'listing') !!}
                    {!! get_the_post_thumbnail($post->ID, 'medium_large', ['class' => 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-105']) !!}
                {!! sage_post_link_close() !!}
            </div>

            <!-- 2. NỘI DUNG BÊN PHẢI -->
            <div class="flex flex-col gap-y-6 flex-1">

                <!-- Category (giữ riêng, click thoải mái, sau này anh có thể thêm link archive) -->
                <span class="w-fit text-warning-dark bg-[rgba(255,193,7,0.16)] px-2 py-px inline-flex rounded-full text-xs leading-[18px] blog-single-item-category">
                    Category Name
                </span>

                <!-- Meta -->
                <div class="flex flex-col gap-y-3 md:flex-row divide-x-0 md:divide-x divide-[rgba(145,158,171,0.24)]">
                    <p class="text-light-secondary-text text-sm leading-[22px] inline-flex items-center gap-x-2 pr-0 md:pr-3 blog-single-item-post-time">
                        <span class="inline-flex items-center justify-center">
                            <i class="hgi hgi-stroke hgi-calendar-03 text-base leading-4 text-light-secondary-text"></i>
                        </span>
                        <span>12:40 PM, 09 Feb 2027</span>
                    </p>
                    <p class="text-light-secondary-text text-sm leading-[22px] inline-flex items-center gap-x-2 pl-0 md:pl-3 blog-single-item-comment">
                        <span class="inline-flex items-center justify-center">
                            <i class="hgi hgi-stroke hgi-chatting-01 text-base text-light-secondary-text"></i>
                        </span>
                        <span>Comment</span>
                        <span>(10)</span>
                    </p>
                </div>

                <!-- 2. TITLE – link riêng -->
                {!! sage_post_link_open(get_post(), '', 'listing') !!}
                    <h5 class="text-light-primary-text hover:text-primary transition-colors duration-300 ease-in-out cursor-pointer mb-3 blog-single-item-title">
                        {!! get_the_title() !!}
                    </h5>
                {!! sage_post_link_close() !!}

                <!-- Excerpt (không link) -->
                <p class="mb-5 line-clamp-6 blog-single-item-description">
                    @php(the_excerpt())
                </p>

                <!-- 3. READ MORE – link riêng -->
                {!! sage_post_link_open(get_post(), 'btn btn-primary btn-large rounded-[60px] group py-2 pl-6 pr-3 gap-x-[18px] inline-flex items-center', 'listing') !!}
                    Read More
                    <span class="size-8 bg-white inline-flex items-center justify-center rounded-full rotate-[-40deg] transform group-hover:rotate-0 transition-all duration-300">
                        <i class="hgi hgi-stroke hgi-arrow-right-02 text-xl text-primary-darker"></i>
                    </span>
                {!! sage_post_link_close() !!}

            </div>
        </div>
    </div>
</div>