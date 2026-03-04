@props([
    'slide_posts' => [],
    'grid_posts'  => [],
    'link_type'   => 'listing',
    'autoplay'    => true,
    'interval'    => 4000,
])

<div class="w-full flex flex-col">
    {{-- SLIDE --}}
    @include('partials.blocks.block-slide-tab-one', [
        'posts'     => $slide_posts,
        'perPage'   => 1,
        'autoplay'  => $autoplay,
        'interval'  => $interval,
    ])

    {{-- GRID --}}
    @include('partials.blocks.article-thumb-grid', [
        'posts'     => $grid_posts,
        'link_type' => $link_type
    ])
</div>