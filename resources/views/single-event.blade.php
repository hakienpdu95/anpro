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
                @php $flags = (array) cmeta('flags'); @endphp
                @foreach ($flags as $flag)
                    @if ($flag === 'hot') 
                        <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full">🔥 Nóng</span>
                    @elseif ($flag === 'featured') 
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full">⭐ Nổi bật</span>
                    @elseif ($flag === 'breaking') 
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full">🚨 Khẩn cấp</span>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    @include('partials.related-posts')
</div>
@endsection