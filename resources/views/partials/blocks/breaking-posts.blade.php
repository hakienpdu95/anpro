@php
    if (empty($query) || !$query->have_posts()) {
        return;
    }
    $title = $title ?? '';
    $cols  = $cols  ?? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3';
@endphp

<section class="mb-12">
    <h3 class="mb-5">
        {{ $title }}
        <span class="flex gap-2 mt-1">
            <span class="h-0.5 w-8 bg-current"></span>
            <span class="h-0.5 w-4 bg-current"></span>
        </span>
    </h3>

    <div class="space-y-4">
        @while ($query->have_posts())
            @php $query->the_post(); @endphp

            <div class="flex items-center gap-3 flex-wrap md:flex-nowrap">
                {!! get_the_post_thumbnail(null, 'medium_large', [
                    'class'   => 'size-22 object-cover shrink-0',
                    'loading' => 'lazy',
                    'alt'     => esc_attr(get_the_title())
                ]) !!}

                <div>
                    <p class="text-gray-600 mb-1 dark:text-gray-400">{{ get_the_date('d/m/Y') }}</p>
                    <h4>
                        {!! sage_post_link_open(get_post(), 'no-underline!', 'listing') !!}
                                {!! get_the_title() !!}
                        {!! sage_post_link_close() !!}
                    </h4>
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