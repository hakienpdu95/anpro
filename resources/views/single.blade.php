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
        @include('partials.entry-content')
    </div>

    @include('partials.related-posts')
@endsection