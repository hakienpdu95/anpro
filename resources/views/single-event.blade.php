@extends('layouts.app')

@section('content')
    @include('partials.entry-header')

    @if (has_post_thumbnail())
        <div class="overflow-hidden shadow-xl mb-8">
            {!! get_the_post_thumbnail(null, 'large', ['class' => 'w-full']) !!}
        </div>
    @endif

    @include('partials.content-single')

@endsection

@section('sidebar')
    @include('sections.sidebar')
@endsection