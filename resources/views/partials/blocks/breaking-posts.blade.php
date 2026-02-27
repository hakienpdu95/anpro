@php
    if (empty($query) || !$query->have_posts()) {
        return;
    }
    $title = $title ?? '🚨 Bài Khẩn Cấp';
    $cols  = $cols  ?? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3';
@endphp

<section class="mb-12">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
        {{ $title }}
    </h2>

    <div class="{{ $cols }} gap-8">
        @while ($query->have_posts())
            @php $query->the_post(); @endphp

            <article class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all border-l-4 border-red-500">
                @if (has_post_thumbnail())
                    <a href="{{ get_the_permalink() }}" class="block">
                        {!! get_the_post_thumbnail(null, 'medium_large', [
                            'class'   => 'w-full h-56 object-cover',
                            'loading' => 'lazy',
                            'alt'     => esc_attr(get_the_title())
                        ]) !!}
                    </a>
                @endif

                <div class="p-6">
                    {{-- Flags (luôn hiển thị breaking nổi bật) --}}
                    @php $flags = (array) cmeta('flags'); @endphp
                    @if ($flags)
                        <div class="flex flex-wrap gap-2 mb-3">
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

                    <h3 class="text-lg font-semibold leading-tight mb-3">
                        <a href="{{ get_the_permalink() }}" class="hover:text-red-600 transition">
                            {{ get_the_title() }}
                        </a>
                    </h3>

                    @php $subtitle = cmeta('subtitle') ?? ''; @endphp
                    @if ($subtitle)
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                            {{ esc_html($subtitle) }}
                        </p>
                    @endif

                    <div class="flex justify-between text-xs text-gray-500">
                        <span>{{ (int) cmeta('reading_time') }} phút đọc</span>
                        <span>{{ get_the_date('d/m/Y') }}</span>
                    </div>
                </div>
            </article>
        @endwhile
    </div>
</section>

@php wp_reset_postdata(); @endphp