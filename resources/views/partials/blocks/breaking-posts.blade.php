@php
    if (empty($query) || !$query->have_posts()) {
        return;
    }
    $title = $title ?? '';
    $cols  = $cols  ?? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3';
@endphp

<section class="mb-8">
    <h3 class="mb-5">
        {{ $title }}
        <span class="flex gap-2 mt-1">
            <span class="h-0.5 w-8 bg-current"></span>
            <span class="h-0.5 w-4 bg-current"></span>
        </span>
    </h3>

    <div class="flex flex-col gap-3 md:col-span-2 lg:col-span-1">
        @while ($query->have_posts())
            @php $query->the_post(); @endphp

            <div class="flex items-start gap-3 pb-4 border-b border-gray-200 justify-between cursor-pointer">
                <div class="flex-1">
                    {!! sage_post_link_open(get_post(), 'font-medium text-[14px] md:text-heading-6 line-clamp-2 mb-[2px] no-underline!', 'listing') !!}
                            {!! get_the_title() !!}
                    {!! sage_post_link_close() !!}
                    <ul class="flex space-x-2.5 items-center mb-5 info">
                        <li>{!! sage_post_author_link(get_post(), 'no-underline!') !!}</li>
                        <li class="flex sm:space-x-2.5 space-x-2.5 items-center">
                            {!! sage_post_date(get_post(), false, true) !!}
                        </li>
                    </ul>
                </div>
                <div class="relative flex-1 w-126 max-w-[126px] h-85 max-h-[85px]">
                    {!! get_the_post_thumbnail(null, 'medium_large', [
                        'class'   => 'w-full h-full! object-cover',
                        'loading' => 'lazy',
                        'alt'     => esc_attr(get_the_title())
                    ]) !!}
                </div>
            </div>

        @endwhile        
    </div>
</section>

@php $flags = (array) cmeta('flags'); @endphp
@if ($flags)
    <div class="flex flex-wrap gap-2 mb-3 hidden!">
        @foreach ($flags as $flag)
            @if ($flag === 'breaking')
                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">🚨 Khẩn cấp</span>
            @elseif ($flag === 'hot')
                <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm">🔥 Nóng</span>
            @elseif ($flag === 'featured')
                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">⭐ Nổi bật</span>
            @endif
        @endforeach
    </div>
@endif
@php wp_reset_postdata(); @endphp