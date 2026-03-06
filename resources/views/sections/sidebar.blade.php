@php 
$query = \App\Queries\MergedPostsQuery::breaking(6, ['post', 'event']); 
@endphp

@include('partials.blocks.breaking-posts', [
    'title' => 'Tin Nổi Bật',
    'query' => $query 
])