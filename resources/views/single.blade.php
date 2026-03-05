@extends('layouts.app')

@section('content')
    @include('partials.entry-header')
    
    <div class="prose prose-lg max-w-none">
        <!-- Meta Views -->
        <div class="flex items-center gap-2 text-sm text-gray-500 mt-3">
            <span class="flex items-center gap-1">
                👁️ <strong>{{ sage_views() }}</strong> lượt xem
            </span>
            {!! sage_hot_badge() !!}
        </div>
        @include('partials.entry-meta')
    </div>
    
    @if (has_post_thumbnail())
        <div class="rounded-3xl overflow-hidden shadow-xl mb-8">
            {!! get_the_post_thumbnail(null, 'large', ['class' => 'w-full']) !!}
        </div>
    @endif

    @include('partials.content-single')
@endsection