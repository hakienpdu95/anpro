@extends('layouts.app')

@section('content')
<article @php post_class('single-news') @endphp>
    <header class="mb-8">
        <h1 class="text-4xl font-bold">{{ get_the_title() }}</h1>
        <div class="meta text-gray-500 mt-3">
            {{ get_the_date('d/m/Y H:i') }} • 
            @php $time = cmeta('reading_time'); @endphp
            @if($time) {{ $time }} phút đọc @endif
        </div>
    </header>

    @if (has_post_thumbnail())
        <img src="{{ get_the_post_thumbnail_url(null, 'large') }}" 
             loading="lazy" 
             class="w-full rounded-xl mb-8" 
             alt="{{ get_the_title() }}">
    @endif

    <div class="prose max-w-none">
        {!! get_the_content() !!}
    </div>

    {{-- Gallery từ metabox --}}
    @php $gallery = get_post_meta(get_the_ID(), 'gallery', true); @endphp
    @if ($gallery)
        <div class="gallery grid grid-cols-3 gap-4 my-12">
            @foreach ($gallery as $item)
                <img src="{{ wp_get_attachment_image_url($item['image'], 'medium') }}" 
                     loading="lazy" alt="">
            @endforeach
        </div>
    @endif

    {{-- Bài liên quan --}}
    @php $related = \App\Helpers\QueryHelper::get_related_posts(get_the_ID(), 6); @endphp
    @if ($related->have_posts())
        <h3 class="text-2xl font-semibold mt-16 mb-6">Bài viết liên quan</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            @while ($related->have_posts()) @php $related->the_post(); @endphp
                <a href="{{ get_permalink() }}" class="block">
                    {{ get_the_title() }}
                </a>
            @endwhile
        </div>
    @endif
</article>
@endsection