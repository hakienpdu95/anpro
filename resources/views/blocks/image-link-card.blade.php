@php
    $image_url = wp_get_attachment_image_url($fields['image'] ?? 0, 'large');
@endphp

<div class="image-link-card group overflow-hidden rounded-2xl shadow-lg bg-white {{ $attributes['className'] ?? '' }}">
    @if ($image_url)
        <div class="overflow-hidden">
            {!! wp_get_attachment_image($fields['image'], 'large', false, [
                'class' => 'w-full h-64 object-cover transition-transform duration-300 group-hover:scale-105'
            ]) !!}
        </div>
    @endif

    <div class="p-6">
        <h3 class="text-xl font-semibold mb-4 line-clamp-2">{{ $fields['title'] }}</h3>

        @if (!empty($fields['link']))
            <a href="{{ esc_url($fields['link']) }}"
               class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium transition">
                {{ $fields['button_text'] ?? 'Xem chi tiết' }}
                <span class="text-lg">→</span>
            </a>
        @endif
    </div>
</div>