@php 
$query = \App\Queries\MergedPostsQuery::breaking(6, ['post', 'event']); 
@endphp

@include('partials.blocks.breaking-posts', [
    'title' => '🚨 Tin Khẩn Cấp',
    'query' => $query   // ← quan trọng: pass $query vào
])