<h1>SIDE BAR</h1>
@php $query = \App\Queries\MergedPostsQuery::breaking(6, ['event']); @endphp
@include('partials.blocks.breaking-posts', ['title' => '🚨 Tin Khẩn Cấp Event'])