@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8 text-center">Cổng Thông Tin – Tin Tức</h1>

    {{-- FORM LỌC 10/10 --}}
    <form method="GET" class="bg-white p-6 rounded-2xl shadow-md mb-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Tìm kiếm --}}
        <div>
            <label class="block text-sm font-medium mb-2">Tìm kiếm</label>
            <input type="text" name="s" value="{{ request('s') }}" 
                   class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500"
                   placeholder="Nhập từ khóa...">
        </div>

        {{-- Danh mục --}}
        <div>
            <label class="block text-sm font-medium mb-2">Danh mục</label>
            <select name="event_cat" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả danh mục</option>
                @foreach (get_terms(['taxonomy' => 'event-categories', 'hide_empty' => true]) as $term)
                    <option value="{{ $term->slug }}" {{ request('event_cat') === $term->slug ? 'selected' : '' }}>
                        {{ $term->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Flags --}}
        <div>
            <label class="block text-sm font-medium mb-2">Đánh dấu</label>
            <div class="flex flex-wrap gap-4">
                <label><input type="checkbox" name="flags[]" value="hot" {{ in_array('hot', (array)request('flags')) ? 'checked' : '' }}> 🔥 Nóng</label>
                <label><input type="checkbox" name="flags[]" value="featured" {{ in_array('featured', (array)request('flags')) ? 'checked' : '' }}> ⭐ Nổi bật</label>
                <label><input type="checkbox" name="flags[]" value="breaking" {{ in_array('breaking', (array)request('flags')) ? 'checked' : '' }}> 🚨 Khẩn cấp</label>
            </div>
        </div>

        {{-- Reading time range --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Thời gian đọc (từ)</label>
                <input type="number" name="reading_time_min" value="{{ request('reading_time_min') }}" 
                       class="w-full border border-gray-300 rounded-xl px-4 py-3" placeholder="0" min="0">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">đến (phút)</label>
                <input type="number" name="reading_time_max" value="{{ request('reading_time_max') }}" 
                       class="w-full border border-gray-300 rounded-xl px-4 py-3" placeholder="60" min="0">
            </div>
        </div>

        {{-- Sort --}}
        <div class="lg:col-span-4 flex gap-4 items-end">
            <select name="orderby" class="flex-1 border border-gray-300 rounded-xl px-4 py-3">
                <option value="date" {{ request('orderby') === 'date' ? 'selected' : '' }}>Mới nhất</option>
                <option value="reading_time" {{ request('orderby') === 'reading_time' ? 'selected' : '' }}>Thời gian đọc</option>
                <option value="title" {{ request('orderby') === 'title' ? 'selected' : '' }}>A → Z</option>
            </select>
            <select name="order" class="flex-1 border border-gray-300 rounded-xl px-4 py-3">
                <option value="DESC" {{ request('order') !== 'ASC' ? 'selected' : '' }}>Giảm dần</option>
                <option value="ASC" {{ request('order') === 'ASC' ? 'selected' : '' }}>Tăng dần</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-medium hover:bg-blue-700 transition">
                Lọc ngay
            </button>
            <a href="{{ get_post_type_archive_link('event') }}" class="text-sm text-gray-500 underline">Xóa lọc</a>
        </div>
    </form>

    {{-- DANH SÁCH BÀI VIẾT --}}
    @if (have_posts())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @while (have_posts())
                @php the_post(); @endphp
                <article class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all">
                    @if (has_post_thumbnail())
                        <a href="{{ the_permalink() }}" class="block">
                            {!! get_the_post_thumbnail(null, 'medium_large', ['class' => 'w-full h-56 object-cover']) !!}
                        </a>
                    @endif
                    <div class="p-6">
                        <div class="flex gap-2 mb-3">
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
                        <h2 class="text-xl font-semibold leading-tight mb-2">
                            <a href="{{ the_permalink() }}" class="hover:text-blue-600 transition">{{ get_the_title() }}</a>
                        </h2>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ cmeta('subtitle') }}</p>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>{{ cmeta('reading_time') }} phút đọc</span>
                            <span>{{ get_the_date('d/m/Y') }}</span>
                        </div>
                    </div>
                </article>
            @endwhile
        </div>

        {{-- Pagination --}}
        <div class="mt-12 flex justify-center">
            {!! paginate_links(['prev_text' => '← Trước', 'next_text' => 'Sau →']) !!}
        </div>
    @else
        <p class="text-center py-20 text-xl text-gray-500">Không tìm thấy bài viết nào phù hợp với bộ lọc.</p>
    @endif
</div>
@endsection