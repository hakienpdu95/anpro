# Sage/Acorn Theme Setup — Required Directories

## Lỗi 1: `ReflectionException: Class "view" does not exist`

**Tạo thư mục storage:**
```bash
mkdir -p /var/www/html/vigiadinhvn/wp-content/themes/anpro/storage/framework/cache/data
mkdir -p /var/www/html/vigiadinhvn/wp-content/themes/anpro/storage/framework/views
mkdir -p /var/www/html/vigiadinhvn/wp-content/themes/anpro/storage/framework/sessions
mkdir -p /var/www/html/vigiadinhvn/wp-content/themes/anpro/storage/logs
chmod -R 777 /var/www/html/vigiadinhvn/wp-content/themes/anpro/storage
```

## Lỗi 2: `file_put_contents(...sage-cache...): Failed to open stream`

**Tạo thư mục uploads:**
```bash
mkdir -p /var/www/html/vigiadinhvn/wp-content/uploads/sage-cache
chmod 777 /var/www/html/vigiadinhvn/wp-content/uploads
chmod 777 /var/www/html/vigiadinhvn/wp-content/uploads/sage-cache
chmod 775 /var/www/html/vigiadinhvn/wp-content
```

## Chạy một lần duy nhất (all-in-one)

```bash
mkdir -p /var/www/html/vigiadinhvn/wp-content/themes/anpro/storage/framework/cache/data \
         /var/www/html/vigiadinhvn/wp-content/themes/anpro/storage/framework/views \
         /var/www/html/vigiadinhvn/wp-content/themes/anpro/storage/framework/sessions \
         /var/www/html/vigiadinhvn/wp-content/themes/anpro/storage/logs \
         /var/www/html/vigiadinhvn/wp-content/uploads/sage-cache

chmod -R 777 /var/www/html/vigiadinhvn/wp-content/themes/anpro/storage
chmod 777 /var/www/html/vigiadinhvn/wp-content/uploads
chmod 777 /var/www/html/vigiadinhvn/wp-content/uploads/sage-cache
chmod 775 /var/www/html/vigiadinhvn/wp-content
```

## Lưu ý

- Web server chạy dưới user `www-data`, cần quyền ghi vào các thư mục trên.
- Thư mục `storage/` dùng bởi Acorn để cache service providers và compiled Blade views.
- Thư mục `uploads/sage-cache/` dùng bởi `App\Helpers\CacheHelper` để lưu file cache HTML.
- Hai thư mục này **không có trong git** — phải tạo lại thủ công mỗi khi deploy lên server mới.
