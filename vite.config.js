import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            strategies: 'injectManifest',
            srcDir: 'resources/js',
            filename: 'sw.js',
            registerType: 'autoUpdate',
            injectRegister: false, // Disable auto-injection as we handle it manually in app.js
            injectManifest: {
                modifyURLPrefix: {
                    '': 'build/', // Fix for sw.js being moved to root: prepend 'build/' to all asset paths
                },
            },
            devOptions: {
                enabled: true,
                type: 'module',
            },
        }),
        tailwindcss(),
    ],
    server: {
        host: 'localhost',
        hmr: {
            host: 'localhost',
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    test: {
        globals: true,
        environment: 'jsdom',
        setupFiles: [path.resolve(__dirname, './resources/js/tests-setup.js')],
    },
});
