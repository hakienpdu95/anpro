@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    @include('partials.entry-header')
    
    <div class="prose prose-lg max-w-none">
        @include('partials.entry-meta')
        @include('partials.entry-content')
    </div>

    @include('partials.related-posts')
</div>
@endsection