# Views Directory Structure

Cấu trúc thư mục views cho dự án Laravel + Inertia.js.

## Cấu trúc

```
resources/views/
├── app.blade.php          # Root template cho Inertia.js
├── emails/                # Email templates (Blade)
│   └── .gitkeep
├── components/            # Blade components (nếu cần)
│   └── .gitkeep
└── README.md             # File này
```

## Mô tả

### `app.blade.php`
Root template chính cho Inertia.js. File này được sử dụng bởi `HandleInertiaRequests` middleware để render tất cả các Inertia pages.

**Lưu ý:** Không nên chỉnh sửa file này trừ khi cần thay đổi cấu trúc HTML cơ bản hoặc thêm meta tags.

### `emails/`
Thư mục chứa các email templates sử dụng Blade syntax. Các email này có thể được sử dụng với Laravel Mail system.

**Ví dụ sử dụng:**
```php
Mail::send('emails.welcome', ['user' => $user], function ($message) {
    $message->to($user->email)->subject('Chào mừng đến với TL Stay');
});
```

### `components/`
Thư mục chứa các Blade components có thể tái sử dụng. 

**Lưu ý:** Với dự án sử dụng Inertia.js, hầu hết UI components nên được tạo bằng Vue trong `resources/js/components/`. Chỉ sử dụng Blade components khi:
- Cần render server-side (SEO, email, PDF)
- Cần tích hợp với Laravel features (forms, validation errors)

## Best Practices

1. **Với Inertia.js:** Tất cả UI components nên được tạo bằng Vue trong `resources/js/components/`
2. **Email templates:** Sử dụng Blade trong `emails/` để tạo email HTML
3. **Server-side rendering:** Chỉ sử dụng Blade khi cần render phía server
4. **Tái sử dụng:** Tạo Blade components trong `components/` nếu cần dùng lại ở nhiều nơi

## Liên quan

- Vue components: `resources/js/components/`
- Vue pages: `resources/js/Pages/`
- Inertia middleware: `app/Http/Middleware/HandleInertiaRequests.php`
