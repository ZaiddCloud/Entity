import '../css/app.css';
import './bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue, route } from 'ziggy-js';
import { Ziggy } from './ziggy';
import { createPinia } from 'pinia';
import RootLayout from './Layouts/RootLayout.vue';
import Card from './Components/Card.vue';
import TextInput from './Components/TextInput.vue';
import SelectInput from './Components/SelectInput.vue';
import PrimaryButton from './Components/PrimaryButton.vue';
import InputLabel from './Components/InputLabel.vue';

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

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue, Ziggy)
            .component('Card', Card)
            .component('TextInput', TextInput)
            .component('SelectInput', SelectInput)
            .component('PrimaryButton', PrimaryButton)
            .component('InputLabel', InputLabel)
            .directive('tooltip', (el, binding) => {
                el.title = binding.value || '';
            });

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
