{{-- BLOCK TABS 10/10 – Alpine.js + Tailwind – Reusable --}}
<div class="my-16" x-data="{ activeTab: 1 }">
    <div class="flex flex-wrap border-b border-gray-200 mb-10">
        <button 
            @click="activeTab = 1"
            :class="{ 'border-b-4 border-blue-600 text-blue-600 font-semibold': activeTab === 1 }"
            class="px-8 py-4 text-lg transition-all hover:text-blue-600 focus:outline-none">
            🔥 Giới thiệu
        </button>
        <button 
            @click="activeTab = 2"
            :class="{ 'border-b-4 border-blue-600 text-blue-600 font-semibold': activeTab === 2 }"
            class="px-8 py-4 text-lg transition-all hover:text-blue-600 focus:outline-none">
            ⭐ Đặc điểm nổi bật
        </button>
        <button 
            @click="activeTab = 3"
            :class="{ 'border-b-4 border-blue-600 text-blue-600 font-semibold': activeTab === 3 }"
            class="px-8 py-4 text-lg transition-all hover:text-blue-600 focus:outline-none">
            📸 Hình ảnh & Video
        </button>
    </div>

    <!-- Tab 1 -->
    <div x-show="activeTab === 1" class="prose prose-lg max-w-none">
        <h3 class="text-2xl font-bold mb-4">Chào mừng đến với trang tin tức hiện đại</h3>
        <p class="text-gray-600">Chúng tôi mang đến những tin tức nóng hổi, phân tích sâu và nội dung chất lượng cao mỗi ngày.</p>
    </div>

    <!-- Tab 2 -->
    <div x-show="activeTab === 2" class="prose prose-lg max-w-none">
        <ul class="list-disc pl-6 space-y-3">
            <li>Tốc độ tải trang cực nhanh nhờ Custom Table</li>
            <li>Filter thông minh với meta_query</li>
            <li>Carousel mượt mà với Splide.js</li>
            <li>Tabs mượt mà với Alpine.js</li>
        </ul>
    </div>

    <!-- Tab 3 -->
    <div x-show="activeTab === 3" class="prose prose-lg max-w-none">
        <p class="italic">Hình ảnh và video sẽ được hiển thị ở đây...</p>
    </div>
</div>