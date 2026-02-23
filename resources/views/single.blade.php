@extends('layouts.app')

@section('content')
    @if (is_singular('tin-tuc'))
        @include('single.single-tin-tuc')
    @elseif (is_singular('post'))
        @include('single.single-post')
    @else
        @include('single.single-default')
    @endif
@endsection