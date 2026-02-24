@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    @include('partials.entry-header')

    <h1>Single post type event</h1>
    <div class="prose prose-lg max-w-none">
        @include('partials.entry-meta')
        
        {{-- Subtitle nổi bật --}}
        @if (cmeta('subtitle'))
            <p class="text-2xl text-gray-600 italic border-l-4 border-blue-500 pl-6 py-2 mb-8">
                {{ cmeta('subtitle') }}
            </p>
        @endif

        {{-- Nội dung chính + Gallery Repeater --}}
        @include('partials.entry-content')

        {{-- Flags --}}
        @if (cmeta('flags'))
            <div class="flex flex-wrap gap-3 my-8">
                @foreach (cmeta('flags') as $flag)
                    <span class="inline-flex items-center px-4 py-2 bg-orange-100 text-orange-700 rounded-2xl text-sm font-medium">
                        @if ($flag === 'hot') 🔥 Nóng
                        @elseif ($flag === 'featured') ⭐ Nổi bật
                        @else 🚨 Khẩn cấp @endif
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    @include('partials.related-posts')
</div>
@endsection