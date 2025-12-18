import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { piniaInertiaPlugin } from './plugins/pinia-inertia-plugin';

const appName = import.meta.env.VITE_APP_NAME || 'TL Stay';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        const pinia = createPinia();
        
        // Thêm plugin để sync user từ Inertia
        pinia.use(piniaInertiaPlugin);
        
        app.use(plugin);
        app.use(pinia);
        
        return app.mount(el);
    },
    progress: {
        color: '#000066',
    },
});
