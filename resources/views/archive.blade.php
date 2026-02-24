@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold mb-8 text-center">
        {{ get_the_archive_title() }}
    </h1>

    @if (have_posts())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @while (have_posts())
                @php the_post(); @endphp
                @include('partials.post-card')   {{-- card chung, scale tốt --}}
            @endwhile
        </div>

        <div class="mt-12 flex justify-center">
            {!! paginate_links(['prev_text' => '← Trước', 'next_text' => 'Sau →']) !!}
        </div>
    @else
        <p class="text-center py-20 text-xl text-gray-500">Không tìm thấy bài viết nào.</p>
    @endif
</div>
@endsection