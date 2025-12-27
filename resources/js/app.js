import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { piniaInertiaPlugin } from './plugins/pinia-inertia-plugin';

const appName = import.meta.env.VITE_APP_NAME || 'THANG LONG STAY';

// Store để lưu siteSettings từ props (sử dụng window để có thể truy cập từ title function)
if (typeof window !== 'undefined') {
    window.__inertiaSiteSettings = null;
}

createInertiaApp({
    title: (title, page) => {
        // Title function được gọi với title string từ Head component và page object
        // Lấy site name từ page props hoặc window store
        let siteName = appName;
        
        // Ưu tiên lấy từ page props (nếu có)
        if (page?.props?.siteSettings?.site_name) {
            siteName = page.props.siteSettings.site_name;
            // Lưu vào window để sử dụng cho lần sau
            if (typeof window !== 'undefined') {
                window.__inertiaSiteSettings = page.props.siteSettings;
            }
        }
        // Nếu không có trong page props, lấy từ window store
        else if (typeof window !== 'undefined' && window.__inertiaSiteSettings?.site_name) {
            siteName = window.__inertiaSiteSettings.site_name;
        }
        
        if (!title) {
            return siteName;
        }
        
        // Nếu title đã được format sẵn (có chứa " - "), giữ nguyên
        if (title.includes(' - ')) {
            return title;
        }
        
        return `${title} - ${siteName}`;
    },
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // Lưu siteSettings vào window store khi app được setup
        if (props?.initialPage?.props?.siteSettings && typeof window !== 'undefined') {
            window.__inertiaSiteSettings = props.initialPage.props.siteSettings;
        }
        
        const app = createApp({ render: () => h(App, props) });
        const pinia = createPinia();
        
        // Thêm plugin để sync user từ Inertia
        pinia.use(piniaInertiaPlugin);
        
        app.use(plugin);
        app.use(pinia);
        
        // Cập nhật siteSettings khi navigate
        // Sử dụng Inertia router events để cập nhật siteSettings
        import('@inertiajs/vue3').then(({ router }) => {
            router.on('start', (event) => {
                if (event.detail.page?.props?.siteSettings && typeof window !== 'undefined') {
                    window.__inertiaSiteSettings = event.detail.page.props.siteSettings;
                }
            });
        });
        
        return app.mount(el);
    },
    progress: {
        color: '#000066',
    },
});
