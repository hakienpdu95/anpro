@if (empty($query) || !$query->have_posts())
    <x-alert type="warning">
        {!! __('Không tìm thấy bài viết nào.', 'sage') !!}
    </x-alert>
@else
    <div class="grid grid-cols-1 gap-y-3 pb-6">
        @while ($query->have_posts())
            @php
                $query->the_post();
            @endphp


            @includeFirst([
                'partials.content-' . get_post_type(),
                'partials.content'
            ])
        @endwhile
    </div>
@endif


@php
    wp_reset_postdata();
@endphp