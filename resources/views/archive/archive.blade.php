@extends('layouts.app')

@section('content')
    @if (is_post_type_archive('tin-tuc'))
        @include('archive.archive-tin-tuc')
    @else
        @include('archive.archive')
    @endif
@endsection