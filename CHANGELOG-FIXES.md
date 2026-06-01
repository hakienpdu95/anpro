# AnPro Theme — Đặc tả toàn bộ fixes & optimizations

> **Áp dụng cho:** Tất cả website nhân bản từ theme AnPro (Sage WordPress)  
> **Ngày thực hiện:** 2026-06-02  
> **Số file thay đổi:** 30 files PHP/Blade + 3 files CSS/JS config + 3 files Boot mới  
> **Tất cả 54 PHP files:** syntax OK sau khi fix

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
13. [Opt #1 — Double WP_Query trong LoadMore](#opt-1)
14. [Opt #2 — Native lazy loading thiếu trên images](#opt-2)
15. [Opt #3 — ViewCounter + Redis: transient writes và cách tối ưu](#opt-3)
16. [Cleanup — Dev comments "12/10", "ULTIMATE"...](#cleanup-comments)
17. [Architecture — Tách `setup.php` thành Boot modules](#architecture)

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

## Tóm tắt impact

| Loại | Số lượng |
|---|---|
| Bugs functional | 6 |
| Bugs frontend (404) | 3 |
| Dead code removed | 2 |
| Performance optimizations | 3 (double query, lazy loading, Redis guide) |
| Dev comments cleaned | 16 files |
| require_once thừa xóa | 22 |
| Files refactored | 1 → 4 |
| WP_Query giảm / LoadMore click | 2 → 1 (−50%) |
| Blade templates thêm lazy loading | 6 files |
| PHP syntax errors sau fix | 0 / 54 files |
