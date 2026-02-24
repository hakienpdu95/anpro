@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold text-center mb-12">{{ get_the_title() }}</h1>
    
    <div class="prose prose-lg max-w-none">
        @php the_content(); @endphp
    </div>
</div>
@endsection