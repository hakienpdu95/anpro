@extends('layouts.app')

@section('content')
<article class="single-post max-w-4xl mx-auto px-6 py-12">

    <h1 class="text-4xl font-bold mb-6">{{ get_the_title() }}</h1>

    @php
        $subtitle      = rwmb_meta('subtitle');
        $reading_time  = rwmb_meta('reading_time');
        $flags         = rwmb_meta('flags');
        $gallery       = rwmb_meta('gallery');   // repeater
    @endphp

    @if ($subtitle)
        <p class="text-xl text-gray-600 mb-8">{{ $subtitle }}</p>
    @endif

    @if ($reading_time)
        <p class="text-sm text-gray-500 mb-6">⏱ Thời gian đọc: {{ $reading_time }} phút</p>
    @endif

    @if ($flags && is_array($flags))
        <div class="flex gap-2 mb-8">
            @foreach ($flags as $flag)
                <span class="px-4 py-1 bg-red-100 text-red-700 rounded-full text-sm">
                    @if ($flag === 'hot') 🔥 Tin nóng
                    @elseif ($flag === 'featured') ⭐ Nổi bật
                    @elseif ($flag === 'breaking') 🚨 Khẩn cấp
                    @endif
                </span>
            @endforeach
        </div>
    @endif

    <div class="prose max-w-none mb-12">
        {!! get_the_content() !!}
    </div>

    {{-- Gallery Repeater --}}
    @if ($gallery && is_array($gallery))
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 my-12">
            @foreach ($gallery as $item)
                @if (!empty($item['image']))
                    <img src="{{ wp_get_attachment_url($item['image']) }}" 
                         alt="{{ $item['caption'] ?? '' }}" 
                         class="rounded-lg shadow" loading="lazy">
                @endif
            @endforeach
        </div>
    @endif

</article>
@endsection