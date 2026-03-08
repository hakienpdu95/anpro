<form role="search" method="get" action="{{ home_url('/') }}" class="relative max-w-xl mx-auto">
    <input type="search" 
           name="s" 
           value="{{ get_search_query() }}" 
           placeholder="Tìm kiếm bài viết, sự kiện..." 
           class="w-full py-2 pl-4 pr-14 border border-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 text-lg outline-none">
    <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-primary-600 hover:text-primary-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 01-14 0 7 7 0 0114 0z" />
        </svg>
    </button>
</form>