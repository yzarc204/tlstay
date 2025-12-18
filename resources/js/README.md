# Cấu trúc thư mục `resources/js/`

Cấu trúc thư mục frontend Vue 3 + Inertia.js cho dự án TL STAY.

## Tổng quan

```
resources/js/
├── app.js                 # Entry point của ứng dụng Vue
├── bootstrap.js           # Bootstrap file (axios, echo, etc.)
├── pages/                 # Inertia.js pages (lowercase)
│   ├── Admin/
│   │   └── Dashboard.vue
│   ├── BookingRoom.vue
│   ├── Home.vue
│   ├── HouseDetail.vue
│   ├── HouseList.vue
│   ├── Login.vue
│   ├── Register.vue
│   └── RentalHistory.vue
├── layouts/               # Layout components (lowercase)
│   └── AppLayout.vue
├── components/            # Reusable Vue components
│   ├── layouts/           # Layout-specific components
│   │   └── Footer.vue
│   ├── booking/           # Booking-related components
│   │   └── RoomCard.vue
│   └── house/             # House-related components
│       └── HouseCard.vue
├── composables/           # Vue 3 composables
│   └── useAuth.js
├── stores/                # Pinia stores
│   ├── auth.js
│   ├── booking.js
│   ├── house.js
│   └── site.js
├── plugins/               # Vue plugins
│   └── pinia-inertia-plugin.js
└── utils/                 # Utility functions
    └── amenityIcons.js
```

## Quy tắc đặt tên

### Thư mục
- **lowercase** với dấu gạch ngang nếu cần: `pages/`, `layouts/`, `components/`
- Tránh PascalCase cho tên thư mục: ❌ `Pages/`, ✅ `pages/`

### Files
- **PascalCase** cho Vue components: `AppLayout.vue`, `HouseCard.vue`
- **camelCase** cho JavaScript files: `useAuth.js`, `amenityIcons.js`

## Import paths

### Sử dụng alias `@`
Tất cả imports nên sử dụng alias `@` thay vì relative paths:

```javascript
// ✅ Đúng
import AppLayout from '@/layouts/AppLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { getAmenityIcon } from '@/utils/amenityIcons'

// ❌ Sai
import AppLayout from '../layouts/AppLayout.vue'
import { useAuthStore } from '../../stores/auth'
```

### Alias configuration
Alias `@` được cấu hình trong `vite.config.js`:
```javascript
resolve: {
    alias: {
        '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
    },
}
```

## Mô tả các thư mục

### `pages/`
Chứa các Inertia.js pages. Mỗi page tương ứng với một route trong Laravel.

**Lưu ý**: 
- Pages được resolve tự động bởi Inertia.js
- Tên file phải khớp với route name trong Laravel
- Có thể tổ chức theo thư mục con (ví dụ: `pages/Admin/Dashboard.vue`)

### `layouts/`
Chứa các layout components. Layouts wrap các pages và cung cấp cấu trúc chung (header, footer, sidebar, etc.).

**Ví dụ**: `AppLayout.vue` - Layout chính của ứng dụng

### `components/`
Chứa các reusable Vue components được sử dụng trong nhiều pages.

**Tổ chức theo feature**:
- `components/layouts/` - Components dùng trong layouts (Footer, Header, etc.)
- `components/booking/` - Components liên quan đến booking
- `components/house/` - Components liên quan đến house

### `composables/`
Chứa các Vue 3 composables - các functions có thể tái sử dụng logic giữa components.

**Ví dụ**: `useAuth.js` - Composable để quản lý authentication state

### `stores/`
Chứa các Pinia stores để quản lý global state.

**Các stores hiện có**:
- `auth.js` - Authentication state
- `booking.js` - Booking state
- `house.js` - House/Property state
- `site.js` - Site-wide state

### `plugins/`
Chứa các Vue plugins và Pinia plugins.

**Ví dụ**: `pinia-inertia-plugin.js` - Plugin để sync Pinia stores với Inertia.js

### `utils/`
Chứa các utility functions và helpers.

**Ví dụ**: `amenityIcons.js` - Utility để map amenities với icons

## Best Practices

1. **Tổ chức theo feature**: Nhóm các components, stores, và utils liên quan lại với nhau
2. **Sử dụng alias `@`**: Luôn sử dụng `@/` thay vì relative paths
3. **Naming consistency**: Tuân thủ quy tắc đặt tên (lowercase cho folders, PascalCase cho components)
4. **Single Responsibility**: Mỗi component/store/composable nên có một trách nhiệm rõ ràng
5. **Reusability**: Tạo components có thể tái sử dụng thay vì duplicate code

## Thêm mới

### Thêm một page mới
1. Tạo file trong `pages/`: `pages/NewPage.vue`
2. Thêm route trong Laravel: `Route::get('/new-page', [Controller::class, 'method'])->name('new-page')`
3. Inertia.js sẽ tự động resolve page

### Thêm một component mới
1. Tạo file trong `components/` hoặc subfolder phù hợp
2. Import và sử dụng: `import NewComponent from '@/components/NewComponent.vue'`

### Thêm một store mới
1. Tạo file trong `stores/`: `stores/newStore.js`
2. Export store: `export const useNewStore = defineStore('newStore', {...})`
3. Sử dụng: `import { useNewStore } from '@/stores/newStore'`

## Tài liệu tham khảo

- [Vue 3 Documentation](https://vuejs.org/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Laravel Vite Plugin](https://laravel.com/docs/vite)
