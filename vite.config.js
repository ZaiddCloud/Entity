import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';
import path from 'path';

export default defineConfig(({ mode }) => {
    // Load env file based on `mode` in the current working directory.
    // Set the third parameter to '' to load all env regardless of the `VITE_` prefix.
    const env = loadEnv(mode, process.cwd(), '');

    return {
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
                injectRegister: false,
                injectManifest: {
                    modifyURLPrefix: {
                        '': 'build/',
                    },
                },
                devOptions: {
                    enabled: env.VITE_PWA_DEV === 'true',
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
    };
});
