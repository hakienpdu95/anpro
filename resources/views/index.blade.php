@extends('layouts.app')

@section('content')
<div class="container">
    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-9 col-span-12">
            @php
                global $wp_query;
                $query = $wp_query;
            @endphp

            @include('partials.content-listing', ['query' => $query])
            {!! \App\Helpers\PaginationHelper::numberPagination($query) !!}
        </div>

        <div class="xl:col-span-3 col-span-12">
            @section('sidebar')
                @include('sections.sidebar')
            @endsection
        </div>
    </div>
</div>
@endsection