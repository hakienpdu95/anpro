{{-- BLOCK SLIDE DEMO 10/10 – Hỗ trợ cấu hình động + dữ liệu giả --}}
@props([
    'title'       => '🔥 Tin nóng nổi bật',
    'perPage'     => 3,
    'autoplay'    => true,
    'interval'    => 4000,
    'arrows'      => true,
    'pagination'  => true,
    'gap'         => '1.5rem',
])

<div class="my-16">
    @if ($title)
        <h3 class="text-3xl font-bold mb-8 flex items-center gap-3">
            {{ $title }}
        </h3>
    @endif

    {{-- Slider --}}
    <div 
        class="splide"
        data-splide-config='{ 
            "type": "loop",
            "perPage": {{ $perPage }},
            "autoplay": {{ $autoplay ? 'true' : 'false' }},
            "interval": {{ $interval }},
            "arrows": {{ $arrows ? 'true' : 'false' }},
            "pagination": {{ $pagination ? 'true' : 'false' }},
            "gap": "{{ $gap }}",
            "speed": 800
        }'
    >
        <div class="splide__track">
            <ul class="splide__list">

                {{-- === MẢNG DEMO DỮ LIỆU GIẢ (4 items) === --}}
                @php
                $demoItems = [
                    [
                        'image'   => 'https://picsum.photos/id/1015/800/450',
                        'title'   => 'iPhone 17 Pro ra mắt: Thiết kế mới cực mỏng',
                        'excerpt' => 'Apple vừa công bố mẫu iPhone mới với màn hình 120Hz và camera 48MP.',
                    ],
                    [
                        'image'   => 'https://picsum.photos/id/201/800/450',
                        'title'   => 'Giá vàng hôm nay tăng vọt 2 triệu đồng/lượng',
                        'excerpt' => 'Giá vàng trong nước tiếp tục lập kỷ lục mới do ảnh hưởng từ thị trường thế giới.',
                    ],
                    [
                        'image'   => 'https://picsum.photos/id/237/800/450',
                        'title'   => 'Tuyển Việt Nam thắng trận mở màn AFF Cup',
                        'excerpt' => 'Đội tuyển Việt Nam đã có chiến thắng ấn tượng trước đối thủ mạnh.',
                    ],
                    [
                        'image'   => 'https://picsum.photos/id/870/800/450',
                        'title'   => 'Thời tiết Hà Nội rét đậm, nhiệt độ xuống 8°C',
                        'excerpt' => 'Người dân thủ đô cần mặc ấm và chuẩn bị sẵn áo khoác dày.',
                    ],
                ];
                @endphp

                @foreach ($demoItems as $item)
                    <li class="splide__slide">
                        <div class="bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all group">
                            <img
                                src="{{ $item['image'] }}"
                                class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-500"
                                alt="{{ $item['title'] }}"
                                loading="lazy"
                                decoding="async"
                            >
                            <div class="p-6">
                                <h4 class="font-semibold text-xl leading-tight mb-3 line-clamp-2">
                                    {{ $item['title'] }}
                                </h4>
                                <p class="text-gray-600 text-sm line-clamp-3">
                                    {{ $item['excerpt'] }}
                                </p>
                                <div class="mt-4 text-xs text-blue-600 font-medium">
                                    8 phút đọc • {{ now()->subHours(rand(1, 12))->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach

            </ul>
        </div>
    </div>
</div>