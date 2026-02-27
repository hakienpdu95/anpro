@extends('layouts.app')

@section('content')
    @php
        global $wp_query;
        $query = $wp_query;
    @endphp

    @include('partials.content-listing', ['query' => $query])
    {!! \App\Helpers\PaginationHelper::numberPagination($query) !!}
@endsection

@section('sidebar')
    @include('sections.sidebar')
@endsection