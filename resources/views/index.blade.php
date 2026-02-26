@extends('layouts.app')

@section('content')
<div class="container">
    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-9 col-span-12">
            @php
                $paged = get_query_var('paged') ?: 1;
                $query = \App\Helpers\QueryHelper::getLatestMergedPosts(1, $paged);
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