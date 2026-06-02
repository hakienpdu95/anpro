{!! sage_get_sidebar_banner(1) !!}

<div class="widget mb-5">
    <h3 class="!mb-[15px]"> Tin chuyên mục <span class="flex gap-2 mt-1">
            <span class="h-0.5 w-8 bg-current"></span>
            <span class="h-0.5 w-4 bg-current"></span>
        </span>
    </h3>
    <div class="guideicons">
        <a class="pp-btn-icon-category flex items-center mt-3"
           href="{{ esc_url(get_post_type_archive_link('happy-family') ?: home_url('/')) }}"
           aria-label="Kế Hoạch Hóa Gia Đình & Mang Thai">
            <div>
                <div class="badge-icon">
                    <img class="img-fluid" src="{{ get_theme_file_uri('resources/images/icon-pregnancy.png') }}"
                         alt="Icon Kế Hoạch Hóa Gia Đình" width="40" height="40" loading="lazy" decoding="async">
                </div>
            </div>
            <div class="category-title"> Kế Hoạch Hóa Gia Đình & Mang Thai </div>
        </a>
        <a class="pp-btn-icon-category flex items-center mt-3"
           href="{{ esc_url(get_post_type_archive_link('family-values') ?: home_url('/')) }}"
           aria-label="Sức Khỏe Gia Đình">
            <div>
                <div class="badge-icon">
                    <img class="img-fluid" src="{{ get_theme_file_uri('resources/images/icon-family.png') }}"
                         alt="Icon Sức Khỏe Gia Đình" width="40" height="40" loading="lazy" decoding="async">
                </div>
            </div>
            <div class="category-title"> Sức Khỏe Gia Đình </div>
        </a>
        <a class="pp-btn-icon-category flex items-center mt-3"
           href="{{ esc_url(get_post_type_archive_link('recipe') ?: home_url('/')) }}"
           aria-label="Dinh Dưỡng & Lối Sống Lành Mạnh">
            <div>
                <div class="badge-icon">
                    <img class="img-fluid" src="{{ get_theme_file_uri('resources/images/icon-nutrition.png') }}"
                         alt="Icon Dinh Dưỡng" width="40" height="40" loading="lazy" decoding="async">
                </div>
            </div>
            <div class="category-title"> Dinh Dưỡng & Lối Sống Lành Mạnh </div>
        </a>
        <a class="pp-btn-icon-category flex items-center mt-3"
           href="{{ esc_url(get_post_type_archive_link('guide') ?: home_url('/')) }}"
           aria-label="Phát Triển Trẻ Em">
            <div>
                <div class="badge-icon">
                    <img class="img-fluid" src="{{ get_theme_file_uri('resources/images/icon-development.png') }}"
                         alt="Icon Phát Triển Trẻ Em" width="40" height="40" loading="lazy" decoding="async">
                </div>
            </div>
            <div class="category-title"> Phát Triển Trẻ Em </div>
        </a>
        <a class="pp-btn-icon-category flex items-center mt-3"
           href="{{ esc_url(get_post_type_archive_link('violence-prevention') ?: home_url('/')) }}"
           aria-label="Sức Khỏe Trẻ Em">
            <div>
                <div class="badge-icon">
                    <img class="img-fluid" src="{{ get_theme_file_uri('resources/images/icon-health.png') }}"
                         alt="Icon Sức Khỏe Trẻ Em" width="40" height="40" loading="lazy" decoding="async">
                </div>
            </div>
            <div class="category-title"> Sức Khỏe Trẻ Em </div>
        </a>
        <a class="pp-btn-icon-category flex items-center mt-3"
           href="{{ esc_url(get_post_type_archive_link('review') ?: home_url('/')) }}"
           aria-label="Thiếu Niên Tích Cực">
            <div>
                <div class="badge-icon">
                    <img class="img-fluid" src="{{ get_theme_file_uri('resources/images/icon-teen.png') }}"
                         alt="Icon Thiếu Niên" width="40" height="40" loading="lazy" decoding="async">
                </div>
            </div>
            <div class="category-title"> Thiếu Niên Tích Cực </div>
        </a>
    </div>
</div>

@php
$query = \App\Queries\MergedPostsQuery::breaking(6, ['post', 'event']);
@endphp

@include('partials.blocks.breaking-posts', [
    'title' => 'Tin Nổi Bật',
    'query' => $query
])
