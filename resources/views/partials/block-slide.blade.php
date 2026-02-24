{{-- BLOCK SLIDE 10/10 – Splide.js + Tailwind --}}
<div class="my-16">
    <div class="flex items-end justify-between mb-8">
        <h3 class="text-3xl font-bold">🔥 Tin nóng nổi bật</h3>
        <div class="splide__arrows flex gap-4"></div>
    </div>

    <div class="splide" id="home-hot-slide">
        <div class="splide__track">
            <ul class="splide__list">
                <!-- Demo 4 slide (bạn có thể thay bằng loop post thật) -->
                @for ($i = 1; $i <= 4; $i++)
                    <li class="splide__slide">
                        <div class="bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all">
                            <img src="https://picsum.photos/id/{{ 100 + $i }}/800/450" 
                                 class="w-full h-64 object-cover" alt="Slide {{ $i }}">
                            <div class="p-6">
                                <h4 class="font-semibold text-xl mb-2">Tiêu đề demo slide {{ $i }} – Tin nóng hôm nay</h4>
                                <p class="text-gray-600 text-sm line-clamp-2">Mô tả ngắn gọn cho slide {{ $i }}. Nội dung sẽ được thay bằng dữ liệu thật sau.</p>
                                <span class="inline-block mt-4 text-xs bg-blue-100 text-blue-700 px-4 py-2 rounded-2xl">15 phút đọc</span>
                            </div>
                        </div>
                    </li>
                @endfor
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    new window.Splide('#home-hot-slide', {
        type: 'loop',
        perPage: 3,
        perMove: 1,
        gap: '1.5rem',
        autoplay: true,
        interval: 4000,
        pauseOnHover: true,
        arrows: true,
        pagination: true,
        lazyLoad: 'nearby',
        breakpoints: {
            640:  { perPage: 1 },
            1024: { perPage: 2 }
        }
    }).mount();
});
</script>