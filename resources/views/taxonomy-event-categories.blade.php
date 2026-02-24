@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">

    {{-- Form lọc nâng cao (có thể tái sử dụng từ archive-event) --}}
    @include('partials/taxonomy-filters')   {{-- mình cung cấp bên dưới --}}

    @if (have_posts())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @while (have_posts())
                @php the_post(); @endphp
                @include('partials/post-card')
            @endwhile
        </div>

        <div class="mt-12 flex justify-center">
            {!! paginate_links(['prev_text' => '← Trước', 'next_text' => 'Sau →']) !!}
        </div>
    @else
        <p class="text-center py-20 text-xl text-gray-500">Không tìm thấy bài viết phù hợp trong chuyên mục này.</p>
    @endif
</div>
@endsection