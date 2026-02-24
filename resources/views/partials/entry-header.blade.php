<article class="mb-12">
    @if (has_post_thumbnail())
        <div class="rounded-3xl overflow-hidden shadow-xl mb-8">
            {!! get_the_post_thumbnail(null, 'large', ['class' => 'w-full']) !!}
        </div>
    @endif
    <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6">{{ get_the_title() }}</h1>
</article>