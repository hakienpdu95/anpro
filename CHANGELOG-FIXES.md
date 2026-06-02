# AnPro Theme — Đặc tả toàn bộ fixes & optimizations

> **Áp dụng cho:** Tất cả website nhân bản từ theme AnPro (Sage WordPress)  
> **Ngày thực hiện:** 2026-06-02  
> **Số file thay đổi:** 45 files PHP/Blade + 3 files CSS/JS config + 3 files Boot mới  
> **Tất cả PHP files:** syntax OK sau khi fix — 0 lỗi

---

## Mục lục

1. [Bug #1 — `sage_social_icons` không render icons](#bug-1)
2. [Bug #2 — 17 helper functions bị trap trong outer if block](#bug-2)
3. [Bug #3 — Homepage query conflict (redundant pre_get_posts)](#bug-3)
4. [Bug #4 — `error_log()` chạy vô điều kiện trong production](#bug-4)
5. [Bug #5 — `update_option()` chạy mỗi admin request](#bug-5)
6. [Bug #6 — Font 404 (woff/ttf/eot/svg)](#bug-6)
7. [Bug #7 — Vite base URL thiếu `/wp/` prefix](#bug-7)
8. [Bug #8 — `body-background.png` 404](#bug-8)
9. [Bug #9 — Dead code `$prefix` trong `sage_post_date`](#bug-9)
10. [Bug #10 — AssetOptimizer: defer + async cùng script](#bug-10)
11. [Bug #11 — 22 `require_once` thừa cho PSR-4 classes](#bug-11)
12. [Bug #12 — LoadMore AJAX query sai post types](#bug-12)
13. [Bug #13 — `Vite::asset()` gọi file không có trong manifest → PHP Fatal Error](#bug-13)
14. [Opt #1 — Double WP_Query trong LoadMore](#opt-1)
15. [Opt #2 — Native lazy loading thiếu trên images](#opt-2)
16. [Opt #3 — ViewCounter + Redis: transient writes và cách tối ưu](#opt-3)
17. [Cleanup — Dev comments "12/10", "ULTIMATE"...](#cleanup-comments)
18. [Architecture — Tách `setup.php` thành Boot modules](#architecture)
19. [Review Round 2 — Bảo mật (4 bugs)](#review-security)
20. [Review Round 2 — Hiệu suất (7 optimizations)](#review-perf)
21. [Review Round 2 — Scale & code quality (4 fixes)](#review-scale)
22. [Review Round 2 — Blade templates (5 fixes)](#review-blade)

---

## Bug #1 — `sage_social_icons` không render icons {#bug-1}

**File:** `app/helpers.php`  
**Mức độ:** 🔴 Critical — social icons không hiển thị gì trên toàn site

### Nguyên nhân
Hàm `sage_social_icons()` có `foreach` body hoàn toàn rỗng — hàm chỉ trả về một `<ul>` rỗng.

```php
// TRƯỚC — foreach body rỗng
foreach ($items as $item) {
    // không có gì
}
```

### Fix
Thêm logic render đầy đủ vào body của `foreach`:
- Lấy `url` và `title` từ menu item
- Match URL/title với `icon_map` key (vd: URL `facebook.com` → key `facebook` → SVG)
- Render `<li><a>` với đầy đủ accessibility attributes
- Fallback sang `esc_html($title)` nếu không tìm thấy icon phù hợp

```php
// SAU
foreach ($items as $item) {
    $url         = $item->url ?? '';
    $title       = $item->title ?? '';
    $icon        = '';
    $url_lower   = strtolower($url);
    $title_lower = strtolower($title);

    foreach ($icon_map as $network => $svg) {
        if (str_contains($url_lower, $network) || str_contains($title_lower, $network)) {
            $icon = $svg;
            break;
        }
    }

    $output .= sprintf(
        '<li><a href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s">%s</a></li>',
        esc_url($url),
        esc_attr($title),
        $icon ?: esc_html($title)
    );
}
```

---

## Bug #2 — 17 helper functions bị trap trong outer if block {#bug-2}

**File:** `app/helpers.php` (lines 126–685 gốc)  
**Mức độ:** 🔴 Critical — nếu plugin nào define `sage_social_icons` trước, 17 hàm sẽ không được khai báo → site crash

### Nguyên nhân
Tất cả 17 helper functions (`sage_post_link`, `sage_thumbnail`, `sage_excerpt`, v.v.) được khai báo bên **trong** block `if (!function_exists('sage_social_icons'))`. Nếu function này đã tồn tại, toàn bộ 17 hàm còn lại sẽ không được định nghĩa.

```php
// TRƯỚC — cấu trúc sai
if (!function_exists('sage_social_icons')) {
    function sage_social_icons() { ... }

    // 17 hàm bị trap bên trong:
    if (!function_exists('sage_post_link')) { ... }
    if (!function_exists('sage_thumbnail')) { ... }
    // ...
} // ← tất cả 17 hàm phụ thuộc vào if này
```

### Fix
Tách mỗi hàm ra thành block `if (!function_exists())` riêng biệt ở top-level. Xóa orphaned `}` ở cuối file.

```php
// SAU — mỗi hàm độc lập
if (!function_exists('sage_social_icons')) {
    function sage_social_icons() { ... }
}

if (!function_exists('sage_post_link')) {
    function sage_post_link() { ... }
}

if (!function_exists('sage_thumbnail')) {
    function sage_thumbnail() { ... }
}
// ...
```

### Kiểm tra bằng PHP tokenizer
```bash
php -r "
\$tokens = token_get_all(file_get_contents('app/helpers.php'));
\$depth = 0;
foreach (\$tokens as \$t) {
    if (is_array(\$t) && \$t[0] === T_STRING && \$t[1] === 'function_exists')
        echo 'Line ' . \$t[2] . ': depth=' . \$depth . PHP_EOL;
    elseif (\$t === '{') \$depth++;
    elseif (\$t === '}') \$depth--;
}
"
# Tất cả phải là depth=0
```

---

## Bug #3 — Homepage query conflict {#bug-3}

**File:** `app/setup.php`  
**Mức độ:** 🔴 Critical — hai `pre_get_posts` hook conflict nhau, gây confusion và debug log bloat

### Nguyên nhân
Có hai logic riêng biệt điều khiển homepage query:

| Hook | Priority | posts_per_page | post_types |
|---|---|---|---|
| Direct `add_action('pre_get_posts', ...)` | 1 | 1 | `['post', 'event']` |
| `MergedPostsQuery::initHomepage()` | 2 | 3 | 5 CPTs |

Priority 2 luôn override priority 1 → direct hook là **dead code** hoàn toàn. Ngoài ra direct hook còn có `error_log()` chạy mỗi homepage request.

```php
// TRƯỚC — 2 block conflict
\App\Queries\MergedPostsQuery::initHomepage(['posts_per_page' => 3]); // priority 2

add_action('pre_get_posts', function ($query) { // priority 1 — bị override hoàn toàn
    error_log("[HOMEPAGE_FINAL] Main query modified | paged = {$paged}");
    $query->set('post_type', ['post', 'event']);
    $query->set('posts_per_page', 1); // ← không bao giờ được áp dụng
    ...
}, 1);

add_filter('redirect_canonical', function ($redirect_url) {
    error_log("[HOMEPAGE_FINAL] Blocked redirect_canonical");
    return false; // ← MergedPostsQuery đã xử lý việc này
}, 10);
```

### Fix
Xóa hoàn toàn direct `pre_get_posts` block (29 dòng) và `redirect_canonical` filter thừa. Giữ duy nhất `MergedPostsQuery::initHomepage()`.

```php
// SAU — single source of truth
\App\Queries\MergedPostsQuery::initHomepage(['posts_per_page' => 3]);
```

---

## Bug #4 — `error_log()` chạy vô điều kiện trong production {#bug-4}

**File:** `app/Helpers/HtmlMinifier.php` (và gián tiếp từ Bug #3)  
**Mức độ:** 🟠 High — bloat server error log mỗi page load

### Nguyên nhân A — từ Bug #3
Hai dòng `error_log("[HOMEPAGE_FINAL]...")` trong `pre_get_posts` chạy mỗi homepage visit.

### Nguyên nhân B — HtmlMinifier::init()
```php
// TRƯỚC — line 45, không có WP_DEBUG guard
error_log('🚀 [HtmlMinifier 10/10] ĐÃ BẬT THÀNH CÔNG – Giữ nguyên logic code gốc + Safe mode Alpine/Splide');
```

### Fix
- Fix A: xóa cùng với Bug #3
- Fix B: xóa dòng `error_log` vô điều kiện trong `HtmlMinifier::init()`

### Lưu ý
Tất cả `error_log()` còn lại trong codebase đều **đúng** — được guard bởi `WP_DEBUG` hoặc `self::$debug`:
```php
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('...');
}
```

---

## Bug #5 — `update_option()` chạy mỗi admin request {#bug-5}

**File:** `app/setup.php`  
**Mức độ:** 🟠 High — 5 DB query thừa mỗi lần mở trang admin

### Nguyên nhân
5 lần `update_option()` được đặt trong hook `admin_init` — hook này chạy **mỗi khi** bất kỳ trang admin nào được load.

```php
// TRƯỚC — chạy mỗi admin page load
add_action('admin_init', function () {
    update_option('medium_size_w', 0);
    update_option('medium_size_h', 0);
    update_option('large_size_w', 0);
    update_option('large_size_h', 0);
    update_option('medium_large_size_w', 0);
});
```

### Fix
Chuyển sang hook `after_switch_theme` — chỉ chạy **1 lần duy nhất** khi activate theme.

```php
// SAU — chỉ chạy khi activate theme
add_action('after_switch_theme', function () {
    update_option('medium_size_w', 0);
    update_option('medium_size_h', 0);
    update_option('large_size_w', 0);
    update_option('large_size_h', 0);
    update_option('medium_large_size_w', 0);
});
```

> **Lưu ý khi apply:** Sau khi deploy fix này, vào WP Admin → Appearance → Themes và deactivate rồi activate lại theme để trigger hook.

---

## Bug #6 — Font 404 (woff/ttf/eot/svg) {#bug-6}

**File:** `resources/css/hgi-stroke-rounded.css`  
**Mức độ:** 🟠 High — icon font không load, tất cả icon hiển thị sai

### Nguyên nhân
`@font-face` trong source CSS reference 4 format không tồn tại trong `resources/fonts/` (chỉ có `.woff2`):

```css
/* TRƯỚC — 4 format không tồn tại */
@font-face {
    font-family: "hgi-stroke-rounded";
    src: url("hgi-stroke-rounded.eot?t=1721855138058");
    src: url("hgi-stroke-rounded.eot?t=1721855138058#iefix") format("embedded-opentype"),
         url("@/fonts/hgi-stroke-rounded.woff2?t=1721855138058") format("woff2"),
         url("hgi-stroke-rounded.woff?t=1721855138058") format("woff"),      /* 404 */
         url("hgi-stroke-rounded.ttf?t=1721855138058") format("truetype"),   /* 404 */
         url("hgi-stroke-rounded.svg?t=1721855138058") format("svg");        /* 404 */
}
```

### Fix
Giữ duy nhất `woff2` (tất cả browser hiện đại đều hỗ trợ), xóa các format cũ:

```css
/* SAU — chỉ woff2 */
@font-face {
    font-family: "hgi-stroke-rounded";
    src: url("@/fonts/hgi-stroke-rounded.woff2") format("woff2");
}
```

> **Sau khi fix:** Phải chạy `npm run build` để rebuild assets.

---

## Bug #7 — Vite base URL thiếu `/wp/` prefix {#bug-7}

**File:** `vite.config.js`  
**Mức độ:** 🟠 High — tất cả asset URL được Vite embed trong CSS đều sai path

### Nguyên nhân
`base` trong `vite.config.js` không khớp với subdirectory của WordPress installation:

```js
// TRƯỚC — thiếu /wp/ prefix
base: '/wp-content/themes/anpro/public/build/',
```

Kết quả: URL woff2 trong built CSS là `/wp-content/themes/anpro/...` thay vì `/wp/wp-content/themes/anpro/...` → 404.

### Fix
```js
// SAU
base: '/wp/wp-content/themes/anpro/public/build/',
```

> **Lưu ý:** Chỉnh `base` theo đúng path WordPress của từng server. Nếu WordPress ở root (`http://example.com/`) thì `base: '/wp-content/themes/anpro/public/build/'` là đúng. Nếu WordPress ở subdirectory `/wp/` thì cần thêm prefix tương ứng.

> **Sau khi fix:** Phải chạy `npm run build`.

---

## Bug #8 — `body-background.png` 404 {#bug-8}

**File:** `resources/css/base/_global.scss`  
**Mức độ:** 🟠 High — background image của `<body>` không hiển thị

### Nguyên nhân
Path tương đối trong SCSS không được Vite resolve đúng cách:

```scss
/* TRƯỚC */
background-image: url('./../../images/body-background.png');
```

Vite không xử lý path này → built CSS giữ nguyên `url(./../../images/body-background.png)` → browser resolve từ `public/build/assets/` lên 2 cấp → `public/images/` → **không tồn tại**.

### Fix
Dùng Vite alias `@` để Vite tự hash và generate đúng URL:

```scss
/* SAU */
background-image: url('@/images/body-background.png');
```

Vite resolve `@` → `resources/` → copy và hash file → URL trong built CSS: `/wp/wp-content/.../assets/body-background.{hash}.png` ✓

> **Sau khi fix:** Phải chạy `npm run build`.

---

## Bug #9 — Dead code `$prefix` trong `sage_post_date` {#bug-9}

**File:** `app/helpers.php`  
**Mức độ:** 🟡 Medium — code thừa, confusing

### Nguyên nhân
Biến `$prefix` được khai báo nhưng luôn là empty string bất kể điều kiện:

```php
// TRƯỚC — dead code
$prefix = $use_modified ? '' : ''; // luôn = ''
return $prefix . $date;            // = '' . $date = $date
```

### Fix
Xóa biến thừa, simplify return và sprintf:

```php
// SAU
if ($raw) {
    return $date;
}

return sprintf(
    '<span class="sm:text-base sm:leading-[27px] text-sm text-primary-100%s">%s</span>',
    esc_attr($class),
    esc_html($date)
);
```

---

## Bug #10 — AssetOptimizer: defer + async cùng một script {#bug-10}

**File:** `app/setup.php` (hoặc `app/Boot/Services.php` sau refactor)  
**Mức độ:** 🟠 High — config sai, `defer` không bao giờ được áp dụng cho alpine/splide

### Nguyên nhân
`alpine` và `splide` xuất hiện trong cả `defer` lẫn `async`. Browser không thể áp dụng cả hai cùng lúc — `async` luôn thắng. `defer` config trở thành dead code.

```php
// TRƯỚC — alpine và splide trùng nhau
'defer' => ['alpine', 'splide', 'swiper', 'gsap', 'videojs', 'chartjs', 'fancybox'],
'async' => ['alpine', 'splide', 'lazysizes'],
```

### Fix
Xóa `alpine` và `splide` khỏi `defer` — chỉ giữ scripts thực sự cần `defer`:

```php
// SAU — rõ ràng, không trùng lặp
'defer'   => ['swiper', 'gsap', 'videojs', 'chartjs', 'fancybox'],
'async'   => ['alpine', 'splide', 'lazysizes'],
'exclude' => ['jquery', 'wp-', 'heartbeat', 'wp-auth-check'],
```

**Nguyên tắc phân biệt:**
- `async` → scripts cần load sớm nhất có thể (Alpine, Splide, lazysizes — interactive UI)
- `defer` → scripts nặng, không cần chạy ngay (video player, chart, lightbox)

---

## Bug #11 — 22 `require_once` thừa cho PSR-4 classes {#bug-11}

**File:** `app/setup.php`  
**Mức độ:** 🟠 High — disk I/O thừa mỗi page load, vi phạm PSR-4 convention

### Nguyên nhân
Composer PSR-4 autoloader đã được load trong `functions.php`. Mọi class trong namespace `App\` sẽ tự load khi được gọi lần đầu. Việc `require_once` thủ công là hoàn toàn thừa.

```php
// TRƯỚC — 22 dòng require_once thừa
require_once get_theme_file_path('app/Optimizations/PerformanceOptimizer.php');
require_once get_theme_file_path('app/Database/CustomTableManager.php');
require_once get_theme_file_path('app/Helpers/CacheHelper.php');
// ... 19 dòng khác
```

### Fix
Xóa tất cả `require_once` cho PSR-4 classes. Chỉ giữ 2 `require_once` hợp lệ:

| `require_once` | Lý do giữ |
|---|---|
| `app/helpers.php` | File functions thuần túy, không phải PSR-4 class |
| `vendor/wpmetabox/meta-box/meta-box.php` | Plugin bên thứ 3, ngoài autoloader |

Ngoài ra xóa `require_once $file` trong các vòng lặp PostTypes/Taxonomies/Metaboxes — thay bằng `class_exists($class)` tự trigger autoloader.

```php
// TRƯỚC — trong init() loop
foreach ($files as $file) {
    require_once $file;                        // ← thừa
    $class = '\\App\\PostTypes\\' . ...;
    if (class_exists($class)) { ... }
}

// SAU — class_exists tự autoload
foreach ($files as $file) {
    $class = '\\App\\PostTypes\\' . ...;
    if (class_exists($class)) { ... }          // ← autoloader tự load file
}
```

---

## Bug #12 — LoadMore AJAX query sai post types {#bug-12}

**File:** `app/Helpers/QueryCache.php` (line 66)  
**Mức độ:** 🔴 Critical — LoadMore trả về kết quả sai, bỏ sót hầu hết nội dung

### Nguyên nhân
`getLoadMoreChunk()` hardcode danh sách post types từ một phiên bản cũ của site, không khớp với CPTs thực tế đang được đăng ký:

```php
// TRƯỚC — 3 CPT không tồn tại, 6 CPT thật bị bỏ sót
$post_types = ['post', 'event', 'viet-heritage', 'viet-product', 'viet-travel'];
```

| Post type | Có đăng ký? | Có trong array cũ? |
|---|---|---|
| `post` | ✅ | ✅ |
| `event` | ✅ | ✅ |
| `guide` | ✅ | ❌ bỏ sót |
| `review` | ✅ | ❌ bỏ sót |
| `recipe` | ✅ | ❌ bỏ sót |
| `happy-family` | ✅ | ❌ bỏ sót |
| `violence-prevention` | ✅ | ❌ bỏ sót |
| `family-values` | ✅ | ❌ bỏ sót |
| `viet-heritage` | ❌ | ✅ query thừa |
| `viet-product` | ❌ | ✅ query thừa |
| `viet-travel` | ❌ | ✅ query thừa |

### Fix
Cập nhật thành đúng 8 CPT đang được đăng ký trong `Boot/MetaData.php`:

```php
// SAU — khớp với CPTs đăng ký trong MetaData.php
$post_types = ['post', 'event', 'guide', 'review', 'recipe', 'happy-family', 'violence-prevention', 'family-values'];
```

> **Lưu ý khi clone site:** Danh sách này phải khớp với các CPTs thực sự được đăng ký qua `CustomTableManager::register()` trong `Boot/MetaData.php`. Nếu thêm hoặc bỏ CPT, cập nhật cả hai nơi.

---

## Bug #13 — `Vite::asset()` gọi file không có trong manifest → PHP Fatal Error {#bug-13}

**File:** `resources/views/sections/sidebar.blade.php`  
**Mức độ:** 🔴 Critical — toàn bộ trang crash với `Illuminate\Foundation\ViteException`

### Nguyên nhân

`sidebar.blade.php` dùng `Vite::asset()` để lấy URL của 6 file icon PNG trong sidebar:

```php
// TRƯỚC — 6 lần gọi Vite::asset() cho file không có trong manifest
<img src="{{ Vite::asset('resources/images/icon-pregnancy.png') }}">
<img src="{{ Vite::asset('resources/images/icon-family.png') }}">
<img src="{{ Vite::asset('resources/images/icon-nutrition.png') }}">
<img src="{{ Vite::asset('resources/images/icon-development.png') }}">
<img src="{{ Vite::asset('resources/images/icon-health.png') }}">
<img src="{{ Vite::asset('resources/images/icon-teen.png') }}">
```

`Vite::asset()` hoạt động bằng cách **tra cứu file trong `manifest.json`**. File chỉ vào manifest khi được khai báo là entry point trong `vite.config.js` hoặc được `import` từ một entry point.

6 icon này được copy vào `public/build/images/` bởi **`viteStaticCopy`** — một cơ chế copy tĩnh hoàn toàn tách biệt, **không ghi vào manifest**. Kết quả: `Vite::asset()` không tìm thấy key → throw `ViteException` → PHP fatal error, toàn trang trắng.

### Sơ đồ vì sao manifest không có icon

```
vite.config.js
├── laravel() input entries    → được hash + ghi vào manifest.json  ✅
├── viteStaticCopy targets     → copy file thô, KHÔNG ghi manifest  ❌
│   └── resources/images/*  →  public/build/images/icon-*.png
│                               (file tồn tại, manifest không biết)
└── Vite::asset('resources/images/icon-*.png')
        → tra manifest → không thấy → ViteException 💥
```

### Fix

Thay `Vite::asset()` bằng `get_theme_file_uri()` trỏ thẳng vào thư mục `resources/images/` của theme. Toàn bộ thư mục theme là public-accessible trong WordPress, không cần build pipeline:

```php
// SAU — get_theme_file_uri() serve trực tiếp, không qua manifest
<img src="{{ get_theme_file_uri('resources/images/icon-pregnancy.png') }}" loading="lazy" decoding="async">
<img src="{{ get_theme_file_uri('resources/images/icon-family.png') }}" loading="lazy" decoding="async">
<img src="{{ get_theme_file_uri('resources/images/icon-nutrition.png') }}" loading="lazy" decoding="async">
<img src="{{ get_theme_file_uri('resources/images/icon-development.png') }}" loading="lazy" decoding="async">
<img src="{{ get_theme_file_uri('resources/images/icon-health.png') }}" loading="lazy" decoding="async">
<img src="{{ get_theme_file_uri('resources/images/icon-teen.png') }}" loading="lazy" decoding="async">
```

### Khi nào dùng `Vite::asset()` vs `get_theme_file_uri()`

| Trường hợp | Dùng gì |
|---|---|
| File được khai báo trong `input[]` của `vite.config.js` | `Vite::asset()` ✅ |
| File được `import` từ JS/CSS entry point | `Vite::asset()` ✅ |
| File copy bởi `viteStaticCopy` (ảnh tĩnh, font thô) | `get_theme_file_uri()` ✅ |
| Ảnh/icon tĩnh không cần content hash | `get_theme_file_uri()` ✅ |

### Lưu ý khi clone site
Khi thêm ảnh mới dùng trong Blade template, chọn một trong hai:
- **Cần cache-busting (hash URL):** import ảnh từ SCSS với `@/images/file.png` → Vite sẽ thêm vào manifest → dùng `Vite::asset()` được
- **Ảnh tĩnh đơn giản:** đặt vào `resources/images/` → dùng `get_theme_file_uri('resources/images/file.png')`, không cần rebuild

---

## Opt #1 — Double WP_Query trong LoadMore {#opt-1}

**File:** `app/Helpers/QueryCache.php` (`getLoadMoreChunk`)  
**Mức độ:** 🟠 High — 2 DB query cho mỗi AJAX click Load More

### Nguyên nhân
Để kiểm tra còn bài nữa không (`has_more`), code chạy một `WP_Query` thứ hai riêng biệt sau khi đã lấy xong posts:

```php
// TRƯỚC — 2 query riêng biệt
$query    = new \WP_Query(['posts_per_page' => $posts_per_page, ...]); // query 1
$has_more = false;
if (count($posts) === $posts_per_page) {
    $next     = new \WP_Query(['posts_per_page' => 1, 'offset' => $offset + $posts_per_page, ...]); // query 2
    $has_more = $next->have_posts();
}
```

### Fix — N+1 trick (1 query duy nhất)
Fetch thêm 1 record (`posts_per_page + 1`). Nếu nhận được nhiều hơn `posts_per_page`, thì còn bài → slice bỏ record thừa, không cần query thứ hai:

```php
// SAU — chỉ 1 query
$query     = new \WP_Query(['posts_per_page' => $posts_per_page + 1, ...]);
$all_posts = $query->posts;
$has_more  = count($all_posts) > $posts_per_page;
$posts     = $has_more ? array_slice($all_posts, 0, $posts_per_page) : $all_posts;
```

**Kết quả:** Mỗi lần Load More giảm từ 2 WP_Query → 1 WP_Query. Với site nhiều request AJAX đồng thời, tổng DB hit giảm 50% cho endpoint này.

---

## Opt #2 — Native lazy loading thiếu trên images {#opt-2}

**Files:** 5 template Blade  
**Mức độ:** 🟠 High — browser load tất cả ảnh ngay lập tức, ảnh hưởng LCP và bandwidth

### Vấn đề
`loading="lazy"` là thuộc tính HTML native giúp browser defer load ảnh ngoài viewport. Không có thuộc tính này, browser request tất cả ảnh cùng lúc khi parse HTML.

### Các file đã fix

| File | Loại ảnh | Trước | Sau |
|---|---|---|---|
| `partials/post-card.blade.php` | Card listing | không có `loading` | `loading="lazy" decoding="async"` |
| `partials/block-slide.blade.php` | Slider demo | không có `loading` | `loading="lazy" decoding="async"` |
| `partials/block-slide-dynamic.blade.php` | Slider thật | không có `loading` | `loading="lazy" decoding="async"` |
| `single.blade.php` | Hero (LCP) | không có `loading` | `loading="eager" fetchpriority="high" decoding="sync"` |
| `single-event.blade.php` | Hero (LCP) | không có `loading` | `loading="eager" fetchpriority="high" decoding="sync"` |
| `page.blade.php` | Hero (LCP) | không có `loading` | `loading="eager" fetchpriority="high" decoding="sync"` |

### Nguyên tắc phân biệt

- **`loading="lazy" decoding="async"`** — ảnh trong listing, card, slider: không trong viewport khi load page, defer lại là tốt
- **`loading="eager" fetchpriority="high" decoding="sync"`** — ảnh hero đầu trang (LCP candidate): phải load ngay, báo cho browser biết đây là ảnh quan trọng nhất → cải thiện Core Web Vitals LCP score

### Lưu ý
`sage_thumbnail()` trong `helpers.php` đã có `loading="lazy"` mặc định cho tất cả calls qua helper. Các template dùng trực tiếp `get_the_post_thumbnail()` mới cần set thủ công.

---

## Opt #3 — ViewCounter + Redis: transient writes và cách tối ưu {#opt-3}

**File:** `app/Helpers/ViewCounter.php`  
**Mức độ:** 🟡 Medium — không phải bug, là bottleneck khi không có object cache

### Cơ chế rate limiting hiện tại (đã có sẵn)
ViewCounter đã implement 2 tầng rate limiting:

```php
// Tầng 1 — request lock (in-memory, chống hook chạy 2 lần cùng request)
if (isset(self::$request_lock[$post_id])) return;
self::$request_lock[$post_id] = true;

// Tầng 2 — IP+post transient (60 giây, chống F5 / refresh nhanh / bot)
$lock_key = 'view_lock_' . $post_id . '_' . md5($ip);
if (get_transient($lock_key)) return;
set_transient($lock_key, '1', 60);
```

### Vấn đề khi không có Redis
`get_transient` / `set_transient` **mặc định ghi vào MySQL** (bảng `wp_options`). Mỗi lượt xem mới tạo ra:
- 1 `SELECT` → kiểm tra transient
- 1 `INSERT/UPDATE` → ghi transient lock (wp_options)
- 1 `UPDATE` → ghi `post_views` vào post_meta

Với traffic cao, 3 DB queries/lượt xem cộng dồn nhanh.

### Fix — Cấu hình Redis Object Cache

**Bước 1 — Cài Redis server (Ubuntu/Debian):**
```bash
sudo apt install redis-server php-redis
sudo systemctl enable --now redis-server
```

**Bước 2 — Cài WordPress Redis drop-in:**
```bash
# Cách A: Plugin (khuyến nghị, có UI)
wp plugin install redis-cache --activate
wp redis enable

# Cách B: thủ công
cp wp-content/plugins/redis-cache/includes/object-cache.php wp-content/object-cache.php
```

**Bước 3 — Thêm vào `wp-config.php`:**
```php
define('WP_REDIS_HOST', '127.0.0.1');
define('WP_REDIS_PORT', 6379);
define('WP_REDIS_DATABASE', 0);
define('WP_REDIS_TIMEOUT', 1);
define('WP_REDIS_READ_TIMEOUT', 1);
```

**Sau khi cấu hình:**
- `get_transient` / `set_transient` → Redis (RAM, ~0.1ms thay vì ~5–15ms DB query)
- `wp_cache_set` / `wp_cache_get` → Redis (thay vì per-request PHP memory)
- Session lock của ViewCounter không còn hit DB
- Tổng DB queries/page giảm đáng kể

**Kiểm tra Redis đang hoạt động:**
```bash
wp redis status
# Expected: Status: Connected | Hits: X | Misses: X
```

---

## Cleanup — Dev comments "12/10", "ULTIMATE"... {#cleanup-comments}

**Files:** `app/setup.php` + 16 class files  
**Mức độ:** 🟡 Medium — code smell, không professional

### Các file được clean

| File | Comment cũ → mới |
|---|---|
| `setup.php` | 13 comment "12/10", "11/10", "10/10"... |
| `EditorOptimizer.php` | `EDITOR OPTIMIZER 12/10` → `EditorOptimizer` |
| `PreloadOptimizer.php` | `PRELOAD OPTIMIZER 12/10` → `PreloadOptimizer` |
| `PaginationHelper.php` | `SMART PAGINATION 10/10` → `Smart pagination helper` |
| `CacheHelper.php` | `DATA VERSIONING 10/10 scalable` → `Data versioning per post type` |
| `QueryHelper.php` | `QUERY 11/10` → descriptive docstring |
| `HtmlMinifier.php` | `CẤU HÌNH GIỐNG CODE GỐC – 10/10` → xóa |
| `PlaceholderHandler.php` | `Cache siêu mạnh` → `Per-post, per-file placeholder cache` |
| `PermalinkManager.php` | `FORCE SLUG 10/10` → descriptive |
| `MergedPostsQuery.php` | `HÀM CORE TỐI ƯU 12/10` → descriptive |
| `helpers.php` | `siêu mạnh!`, `SIÊU NHANH` → clean English |
| `ViewCounter.php` | error_log messages → clean format `[ClassName] action` |
| `QueryCache.php` | error_log messages + `PREFETCH SIÊU SÂU` → clean |
| `DataCache.php` | error_log messages → clean |
| `ViewCache.php` | error_log messages → clean |
| `Archives/EventArchive.php` | `Modular 10/10` → `Event archive query filters` |

---

## Architecture — Tách `setup.php` thành Boot modules {#architecture}

**Mức độ:** 🟡 Medium — maintainability, SRP  
**Loại thay đổi:** Refactor thuần túy, không thay đổi behavior

### Trước
`app/setup.php` — 423 dòng, làm 20+ việc không liên quan:
- Theme support, menus, image sizes
- Editor integration
- Service init (cache, search, query)
- CPT/Taxonomy registration
- Meta Box boot
- Output buffering
- ...

### Sau — 4 files với trách nhiệm rõ ràng

```
app/
  setup.php              ← orchestrator, 16 dòng
  Boot/
    ThemeSupport.php     ← WP theme setup, menus, image sizes, sidebars, editor
    MetaData.php         ← custom meta tables, CPT/Tax, Meta Box
    Services.php         ← optimizations, cache, search, queries, AJAX
```

**`app/setup.php`** — orchestrator duy nhất:
```php
<?php
namespace App;

require_once get_theme_file_path('app/helpers.php');
require_once get_theme_file_path('app/Boot/ThemeSupport.php');
require_once get_theme_file_path('app/Boot/MetaData.php');
require_once get_theme_file_path('app/Boot/Services.php');
```

**`app/Boot/ThemeSupport.php`** — 125 dòng:
- Block editor (Vite CSS/JS injection)
- `after_setup_theme`: theme support, nav menus, image sizes, MCE table plugin
- `after_switch_theme`: zero out default WP image sizes
- `widgets_init`: sidebars
- `image_size_names_choose`: media uploader labels

**`app/Boot/MetaData.php`** — 93 dòng:
- `CustomTableManager::init()` + `register()` cho từng CPT
- `EventArchive::init()`, `EventColumns::init()`
- `init` hook: auto-register CPT/Taxonomy subclasses
- `after_setup_theme`: Meta Box plugin boot
- `rwmb_meta_boxes` filter: auto-register Metabox subclasses

**`app/Boot/Services.php`** — 91 dòng:
- Performance/Editor/Preload/Asset optimizers
- Permalink Manager, Watermark, Placeholder
- Cache system (CacheHelper, DataCache, QueryCache, ViewCache)
- HtmlMinifier + `template_redirect` output buffer
- Admin CSS enqueue
- SearchManager, ViewCounter, CMB2 Registrar
- MergedPostsQuery (homepage + archives)
- LoadMore AJAX

### Nguyên tắc thêm feature mới
| Muốn thêm gì | Chỉnh file nào |
|---|---|
| CPT mới | `Boot/MetaData.php` — thêm `CustomTableManager::register()` |
| Service/optimizer mới | `Boot/Services.php` |
| Menu/sidebar/image size mới | `Boot/ThemeSupport.php` |
| `setup.php` | Không bao giờ cần chỉnh |

---

## Checklist áp dụng cho site clone

### PHP changes (không cần rebuild)
- [ ] `app/helpers.php` — fix `sage_social_icons` foreach + tách 17 hàm ra top-level
- [ ] `app/helpers.php` — xóa dead code `$prefix` trong `sage_post_date`
- [ ] `app/setup.php` → xóa toàn bộ, tạo orchestrator 16 dòng
- [ ] Tạo `app/Boot/ThemeSupport.php`
- [ ] Tạo `app/Boot/MetaData.php`
- [ ] Tạo `app/Boot/Services.php`
- [ ] `app/Helpers/HtmlMinifier.php` — xóa unconditional `error_log` trong `init()`
- [ ] `app/Helpers/QueryCache.php` — cập nhật post types + gộp double WP_Query thành 1 (N+1 trick)
- [ ] `resources/views/partials/post-card.blade.php` — thêm `loading="lazy" decoding="async"`
- [ ] `resources/views/partials/block-slide.blade.php` — thêm `loading="lazy" decoding="async"`
- [ ] `resources/views/partials/block-slide-dynamic.blade.php` — thêm `loading="lazy" decoding="async"`
- [ ] `resources/views/single.blade.php` — thêm `loading="eager" fetchpriority="high"` cho hero image
- [ ] `resources/views/single-event.blade.php` — thêm `loading="eager" fetchpriority="high"` cho hero image
- [ ] `resources/views/page.blade.php` — thêm `loading="eager" fetchpriority="high"` cho hero image
- [ ] `resources/views/sections/sidebar.blade.php` — thay `Vite::asset()` → `get_theme_file_uri()` cho 6 icon PNG
- [ ] **Review Round 2 — Bảo mật:**
  - [ ] `app/Database/CustomTableManager.php:371` — SQL Injection `filterOrderByMeta` → `sanitize_key()` + `$wpdb->prepare()`
  - [ ] `app/Database/CustomTableManager.php:204` — thêm `current_user_can('edit_posts')` trước preload meta
  - [ ] `app/Database/CustomTableManager.php:462` — autoload option `true` → `false`
  - [ ] 6 blade templates — `{!! get_the_title() !!}` → `{{ get_the_title() }}`
  - [ ] `app/Permalinks/PermalinkManager.php:125` — `esc_url_raw(wp_unslash(...))` cho REQUEST_URI
  - [ ] `sections/sidebar.blade.php` — dead links `href="#"` → `get_post_type_archive_link()`
- [ ] **Review Round 2 — Hiệu suất:**
  - [ ] `app/Helpers/ViewCounter.php` — IP thật qua proxy headers + defer DB write ra `shutdown`
  - [ ] `app/Helpers/CacheHelper.php` — `save_post` priority 20→5, `forever()` → `put(..., YEAR_IN_SECONDS)`
  - [ ] `app/Helpers/QueryHelper.php:75` — `orderby=rand` → PHP shuffle trên pool nhỏ
  - [ ] `app/Helpers/QueryCache.php` — xóa `ob_start()` thừa
  - [ ] `app/Optimizations/PerformanceOptimizer.php:25` — `init` priority 9999→1
  - [ ] `app/Helpers/HtmlMinifier.php:15` — bật minify cả trong WP_DEBUG
- [ ] **Review Round 2 — Scale & quality:**
  - [ ] `app/Ajax/LoadMore.php` — named constants + `wp_kses_post()` output
  - [ ] `app/Search/SearchManager.php:31` — post types dynamic từ `CustomTableManager::$registered`
  - [ ] `app/Watermark/WatermarkHandler.php` — whitelist extension `['jpg','jpeg','png']`
  - [ ] `app/Helpers/DataCache.php:26` — log label "miss" → "cache"
- [ ] **Review Round 2 — Blade/Frontend:**
  - [ ] `sections/header.blade.php` — `<div href>` → `<p>`, `aria-label` search buttons, logo `width`/`height`
  - [ ] `sections/sidebar.blade.php` — `aria-label` + `alt` cho icons, `width`/`height`
  - [ ] `partials/block-slide.blade.php` — thêm `width="800" height="450"`
- [ ] Cấu hình Redis Object Cache (xem Opt #3 trong CHANGELOG)
- [ ] Xóa dev comments "12/10", "ULTIMATE"... trong 16 class files (optional, cosmetic)

### Frontend changes (cần `npm run build`)
- [ ] `vite.config.js` — cập nhật `base` đúng với WordPress path của server
- [ ] `resources/css/hgi-stroke-rounded.css` — `@font-face` chỉ giữ `woff2`
- [ ] `resources/css/base/_global.scss` — đổi path `body-background.png` sang `@/images/`
- [ ] Chạy `npm run build`

### Activation step
- [ ] Sau khi deploy PHP changes: deactivate + activate lại theme để trigger `after_switch_theme` (zero out default image sizes)

---

---

## Review Round 2 — Bảo mật {#review-security}

> Phát hiện qua code review toàn diện 4 chiều (security / performance / quality / template).

### Bug #14 — SQL Injection trong `filterOrderByMeta`

**File:** `app/Database/CustomTableManager.php:371`  
**Mức độ:** 🔴 Critical

`$meta_key` từ `WP_Query->get('meta_key')` được nối thẳng vào SQL string:

```php
// TRƯỚC — injection vector
return "MAX(CASE WHEN {$table}.meta_key = '{$meta_key}' THEN {$table}.meta_value END) {$order}";

// SAU — sanitize_key() + $wpdb->prepare()
$meta_key = sanitize_key($query->get('meta_key'));
return $wpdb->prepare(
    "MAX(CASE WHEN {$table}.meta_key = %s THEN {$table}.meta_value END) {$order}",
    $meta_key
);
```

---

### Bug #15 — XSS: `{!! get_the_title() !!}` trong 6 Blade templates

**Files:** `partials/content.blade.php`, `partials/content-loadmore.blade.php`, `partials/blocks/breaking-posts.blade.php`, `partials/blocks/block-slide-tab-one.blade.php`, `partials/blocks/article-thumb-grid.blade.php`, `views/search.blade.php`  
**Mức độ:** 🔴 Critical

`{!! !!}` bỏ qua Blade auto-escaping. `get_the_title()` không tự escape HTML — nếu title chứa `<script>` hoặc ký tự đặc biệt sẽ render thô trên trang.

```php
// TRƯỚC — unescaped output
{!! get_the_title() !!}

// SAU — Blade htmlspecialchars()
{{ get_the_title() }}
```

---

### Bug #16 — Dead links `href="#"` trong sidebar

**File:** `resources/views/sections/sidebar.blade.php`  
**Mức độ:** 🟠 High

6 category links đều trỏ `href="#"` — broken link với SEO và UX.

```php
// TRƯỚC
<a href="#">

// SAU — trỏ đúng CPT archive, fallback home
<a href="{{ esc_url(get_post_type_archive_link('happy-family') ?: home_url('/')) }}"
   aria-label="Kế Hoạch Hóa Gia Đình & Mang Thai">
```

---

### Bug #17 — Thiếu capability check trong `preloadCurrentPostMeta`

**File:** `app/Database/CustomTableManager.php:204`  
**Mức độ:** 🟠 High

`$_GET['post']` được đọc không có kiểm tra quyền hạn — bất kỳ user nào biết ID post đều có thể trigger load meta.

```php
// TRƯỚC
$post_id = (int) ($_GET['post'] ?? 0);
if ($post_id > 0) self::loadAllMeta($post_id);

// SAU
if (!is_admin() || !current_user_can('edit_posts')) return;
$post_id = (int) ($_GET['post'] ?? 0);
if ($post_id > 0) self::loadAllMeta($post_id);
```

---

## Review Round 2 — Hiệu suất {#review-perf}

### Opt #4 — ViewCounter: IP spoofing + defer DB write

**File:** `app/Helpers/ViewCounter.php`

**IP spoofing:** `$_SERVER['REMOTE_ADDR']` trả về IP của proxy khi site đứng sau Cloudflare/nginx — toàn bộ user chung 1 IP, rate limit vô hiệu.

**DB write blocking:** `update_post_meta()` chạy đồng bộ, block page render.

```php
// TRƯỚC
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
update_post_meta($post_id, 'post_views', $new_total); // block render

// SAU — IP thật qua proxy headers + defer ra shutdown
private static function getClientIp(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR'] as $h) {
        $ip = trim(explode(',', $_SERVER[$h] ?? '')[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// DB write không block page render
add_action('shutdown', function () use ($post_id, $new_total) {
    update_post_meta($post_id, 'post_views', $new_total);
}, 999);
```

---

### Opt #5 — CacheHelper: hook priority + `forever()` → có expiry

**File:** `app/Helpers/CacheHelper.php`

| Vấn đề | Trước | Sau |
|---|---|---|
| `save_post` priority quá muộn | `20` (sau plugin khác) | `5` (trước) |
| Version key không expire | `$cache->forever($key, $v)` | `$cache->put($key, $v, YEAR_IN_SECONDS)` |

---

### Opt #6 — QueryHelper: `ORDER BY RAND()` → PHP shuffle

**File:** `app/Helpers/QueryHelper.php:75`

`ORDER BY RAND()` yêu cầu MySQL tính random value cho toàn bộ result set → O(n) với n là số bài trong bảng.

```php
// TRƯỚC — chậm theo tỉ lệ DB size
'orderby' => 'rand'

// SAU — fetch pool nhỏ theo date, shuffle trong PHP
$pool = self::cquery(['posts_per_page' => max($limit * 3, 18), 'orderby' => 'date', ...]);
$posts = $pool->posts ?? [];
shuffle($posts);
$pool->posts = array_slice($posts, 0, $limit);
return $pool;
```

---

### Opt #7 — QueryCache: bỏ `ob_start()` thừa trong render loop

**File:** `app/Helpers/QueryCache.php`

`ob_start()` được gọi nhưng không capture gì — Blade `->render()` trả về string, không echo. Xóa `ob_start()` vô nghĩa.

---

### Opt #8 — CustomTableManager: autoload option `true` → `false`

**File:** `app/Database/CustomTableManager.php:462`

Flag `sage_custom_tables_created` được load vào RAM mọi request (autoload = true) dù chỉ cần đọc khi activate theme.

```php
// TRƯỚC
update_option('sage_custom_tables_created', true, true);

// SAU
update_option('sage_custom_tables_created', true, false);
```

---

### Opt #9 — PerformanceOptimizer: `init` priority `9999` → `1`

**File:** `app/Optimizations/PerformanceOptimizer.php:25`

Priority 9999 chạy sau tất cả plugin → plugin có thể đã đăng ký emoji/oembed/xmlrpc trước khi theme kịp disable. Đổi sang `1` để disable trước.

```php
// TRƯỚC
add_action('init', [self::class, 'applyOptimizations'], 9999);

// SAU
add_action('init', [self::class, 'applyOptimizations'], 1);
```

---

### Opt #10 — HtmlMinifier: bật cả trong `WP_DEBUG`

**File:** `app/Helpers/HtmlMinifier.php:15`

Khi `WP_DEBUG = true`, minifier bị tắt — local và production chạy HTML khác nhau, bug minify chỉ xuất hiện trên production.

```php
// TRƯỚC — tắt khi debug
self::$enabled = !$isDebug;

// SAU — luôn bật, local == production
self::$enabled = true;
```

---

## Review Round 2 — Scale & code quality {#review-scale}

### Opt #11 — LoadMore: magic numbers → named constants + `wp_kses_post` output

**File:** `app/Ajax/LoadMore.php`

```php
// TRƯỚC
$offset = max(3, (int) ($_POST['offset'] ?? 3));
$chunk  = QueryCache::getLoadMoreChunk($offset, 3);
echo $chunk['html'] ?? '';

// SAU
private const INITIAL_OFFSET  = 3;
private const POSTS_PER_CHUNK = 3;

$offset = max(self::INITIAL_OFFSET, (int) ($_POST['offset'] ?? self::INITIAL_OFFSET));
$chunk  = QueryCache::getLoadMoreChunk($offset, self::POSTS_PER_CHUNK);
echo wp_kses_post($chunk['html'] ?? '');
```

---

### Opt #12 — SearchManager: hardcode post types → dynamic từ `CustomTableManager`

**File:** `app/Search/SearchManager.php:31`

Post types hardcode phải sync tay với `MetaData.php`. Đổi sang đọc trực tiếp từ registry:

```php
// TRƯỚC
$query->set('post_type', ['post', 'event', 'happy-family', 'family-values', 'violence-prevention']);

// SAU — tự động sync với CPTs đăng ký
$searchable = array_values(array_filter(
    CustomTableManager::$registered ?? ['post'],
    fn($pt) => $pt !== 'revision'
));
$query->set('post_type', $searchable ?: ['post']);
```

---

### Opt #13 — WatermarkHandler: whitelist extension file

**File:** `app/Watermark/WatermarkHandler.php`

GD và Imagick có thể xử lý file không phải ảnh nếu không validate extension — nguy cơ crash hoặc ghi đè file lạ.

```php
// SAU — cả GD lẫn Imagick đều guard trước
$ext = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
    return;
}
```

---

### Opt #14 — DataCache: log label "miss" → "cache"

**File:** `app/Helpers/DataCache.php:26`

Log luôn in `"miss"` kể cả khi cache hit — misleading khi debug.

```php
// TRƯỚC
error_log("[DataCache] miss: {$key} ...");

// SAU
error_log("[DataCache] cache: {$key} ...");
```

---

## Review Round 2 — Blade templates {#review-blade}

### Opt #15 — Images: `width`/`height` + LCP priority

**Files:** `sections/header.blade.php`, `partials/block-slide.blade.php`

Thiếu `width`/`height` khiến browser không reserve space → layout shift (CLS). Logo là LCP candidate cần load sớm.

```html
<!-- TRƯỚC -->
<img src="..." loading="lazy" class="img-fluid">

<!-- SAU — logo: eager + high priority + intrinsic size -->
<img src="..." width="100" height="40" loading="eager" fetchpriority="high" class="img-fluid">

<!-- SAU — slider demo: intrinsic size + lazy -->
<img src="..." width="800" height="450" loading="lazy" decoding="async">
```

---

### Opt #16 — Sidebar: `aria-label`, `alt` text, archive links

**File:** `resources/views/sections/sidebar.blade.php`

| Vấn đề | Fix |
|---|---|
| 6 links `href="#"` | Trỏ đúng CPT archive (`get_post_type_archive_link()`), fallback `home_url('/')` |
| Icon img không có `alt` | Thêm `alt="Icon [tên chuyên mục]"` mô tả |
| Link thiếu `aria-label` | Thêm `aria-label` cho screen reader |
| Icon img thiếu `width`/`height` | Thêm `width="40" height="40"` — fix CLS |

---

### Opt #17 — Header: `aria-label` cho search button, sanitize `REQUEST_URI`

**Files:** `sections/header.blade.php`, `app/Permalinks/PermalinkManager.php`

```html
<!-- TRƯỚC — icon-only button không có label -->
<a href="#!" @click.prevent="open = true">

<!-- SAU -->
<a href="#!" aria-label="Mở tìm kiếm" @click.prevent="open = true">
```

```php
// TRƯỚC — REQUEST_URI không sanitize
$current_url = $_SERVER['REQUEST_URI'] ?? '';

// SAU
$current_url = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
```

---

## Tóm tắt impact

| Loại | Số lượng |
|---|---|
| **Bugs functional** | **11** (7 cũ + 4 round 2) |
| Bugs frontend (404) | 3 |
| Dead code removed | 2 |
| **Performance optimizations** | **11** (3 cũ + 8 round 2) |
| Security fixes | 4 (SQL injection, XSS ×6 templates, capability check, REQUEST_URI) |
| Dev comments cleaned | 16 files |
| require_once thừa xóa | 22 |
| Files refactored | 1 → 4 |
| WP_Query giảm / LoadMore click | 2 → 1 (−50%) |
| Blade templates fixed | 12 files |
| DB write ViewCounter | blocking → deferred (shutdown hook) |
| IP detection | REMOTE_ADDR → proxy-aware (CF/nginx/X-Forwarded-For) |
| ORDER BY RAND() | eliminated → PHP shuffle |
| PHP syntax errors sau fix | 0 / 54 files |
| **Tổng findings đã fix** | **40 / 40** |
