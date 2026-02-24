@php
$term = get_queried_object();
$taxonomy_label = get_taxonomy($term->taxonomy)->labels->singular_name ?? 'Chuyên mục';
@endphp

<header class="mb-12 text-center">
    <span class="inline-block px-4 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full mb-4">
        {{ $taxonomy_label }}
    </span>
    
    <h1 class="text-4xl md:text-5xl font-bold mb-6">
        {{ single_term_title('', false) }}
    </h1>

    @if (term_description())
        <div class="prose max-w-2xl mx-auto text-gray-600 mb-8">
            {{ term_description() }}
        </div>
    @endif

    {{-- Term Meta (thumbnail, icon, color...) --}}
    @if ($thumbnail = cterm_meta('thumbnail_id'))
        <div class="mt-8 rounded-3xl overflow-hidden shadow-xl">
            {!! wp_get_attachment_image($thumbnail, 'large', false, ['class' => 'w-full h-80 object-cover']) !!}
        </div>
    @endif

    <div class="flex justify-center gap-8 text-sm text-gray-500 mt-8">
        <div>{{ $term->count }} bài viết</div>
        @if (cterm_meta('icon'))
            <div>{{ cterm_meta('icon') }}</div>
        @endif
    </div>
</header>