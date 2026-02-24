<article class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all">
    @if (has_post_thumbnail())
        <a href="{{ the_permalink() }}">
            {!! get_the_post_thumbnail(null, 'medium_large', ['class' => 'w-full h-56 object-cover']) !!}
        </a>
    @endif
    <div class="p-6">
        <h2 class="text-xl font-semibold leading-tight mb-3">
            <a href="{{ the_permalink() }}" class="hover:text-blue-600 transition">{{ get_the_title() }}</a>
        </h2>
        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ cmeta('subtitle') ?? get_the_excerpt() }}</p>
        <div class="flex justify-between text-xs text-gray-500">
            <span>{{ cmeta('reading_time') }} phút đọc</span>
            <span>{{ get_the_date('d/m/Y') }}</span>
        </div>
    </div>
</article>