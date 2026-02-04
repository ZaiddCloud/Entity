import '../css/app.css';
import './bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue, route } from 'ziggy-js';
import { Ziggy } from './ziggy';
import { createPinia } from 'pinia';
import RootLayout from './Layouts/RootLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

window.Ziggy = Ziggy;
window.route = route;

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const page = resolvePageComponent(
            `./${name.startsWith('Technologies/') ? '' : 'Pages/'}${name}.vue`,
            import.meta.glob(['./Pages/**/*.vue', './Technologies/**/*.vue'])
        );

        page.then(module => {
            if (!module.default.layout) {
                module.default.layout = RootLayout;
            }
        });

        return page;
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue, Ziggy)
            .directive('tooltip', (el, binding) => {
                el.title = binding.value || '';
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

/**
 * Service Worker Registration for Touch 5
 */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('👷 SW: Registered with scope:', registration.scope);
            })
            .catch(error => {
                console.error('👷 SW: Registration failed:', error);
            });
    });
}

import { router } from '@inertiajs/vue3';
import db from './Core/Database/dexieApp';

/**
 * Security Layer: Clear Data on Logout
 * Protects shared devices by wiping IndexedDB when user logs out.
 */
router.on('navigate', async (event) => {
    if (event.detail.page.url.includes('logout')) {
        console.log('🔒 Logout detected - clearing sensitive data...');
        try {
            await db.delete();
            await db.open(); // Re-open for next login/session
            console.log('✅ Local database wiped successfully');
        } catch (e) {
            console.error('❌ Failed to wipe local database:', e);
        }
    }
});
