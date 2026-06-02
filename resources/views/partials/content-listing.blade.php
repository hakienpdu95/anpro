@if (empty($query) || !$query->have_posts())
    <x-alert type="warning">
        {!! __('Không tìm thấy bài viết nào.', 'sage') !!}
    </x-alert>
@else
    <div class="mb-[20px]">
      <span class="text-lg font-bold spline-sans leading-7 mb-2.5 block">{!! __('Bài viết mới nhất', 'sage') !!}</span>
      <div class="w-full h-[2px] bg-[#6697a1]"></div>
    </div>
    <div id="posts-grid" class="grid grid-cols-1 gap-y-3 pb-6">
        @while ($query->have_posts())
            @php
                $query->the_post();
            @endphp


            @include('partials.content')
        @endwhile
    </div>
@endif


@php
    wp_reset_postdata();
@endphp