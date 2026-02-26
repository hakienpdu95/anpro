@if (empty($query) || !$query->have_posts())
    <x-alert type="warning">
        {!! __('Không tìm thấy bài viết nào.', 'sage') !!}
    </x-alert>
@else
    <div class="space-y-10">
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