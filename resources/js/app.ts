import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h, type DefineComponent } from 'vue';

import './sw';

const appName = import.meta.env.VITE_APP_NAME || 'School Management';

type PageModule = Record<string, { default: DefineComponent }>;

const pages = import.meta.glob('./pages/**/*.vue', { eager: true }) as PageModule;

void createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    resolve: (name) => pages[`./pages/${name}.vue`],
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4f46e5',
    },
});