import { precacheAndRoute } from 'workbox-precaching';
import { registerRoute } from 'workbox-routing';
import { CacheFirst } from 'workbox-strategies';

/**
 * Entity Project - Service Worker v14 (Vite PWA)
 * Focus: Network First -> Cache Fallback -> Offline Page
 */

// 1. Precache Build Assets (injected by VitePWA)
precacheAndRoute(self.__WB_MANIFEST || []);

const CACHE_NAME = 'entity-runtime-v15';

// Minimal static assets to cache for performance (Dynamic)
// Note: Manifest assets are handled by precacheAndRoute above.
const STATIC_ASSETS = [
    '/offline',
    '/js/vendor/dexie.js'
];

// 2. Serve Static Assets (Cache First) - Fix for dexie.js/offline page
registerRoute(
    ({ url }) => STATIC_ASSETS.includes(url.pathname),
    new CacheFirst({
        cacheName: CACHE_NAME,
    })
);

self.addEventListener('install', (event) => {
    console.log('👷 SW v14: Installing...');
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(err => {
                console.warn('👷 SW: Cache addAll failed', err);
            });
        })
    );
});

self.addEventListener('activate', (event) => {
    console.log('👷 SW v14: Activating & Cleaning...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    // Clean old custom caches
                    if (cache !== CACHE_NAME && !cache.startsWith('workbox-')) {
                        console.log('👷 SW: Deleting old cache:', cache);
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // 1. Ignore non-http
    if (!url.protocol.startsWith('http')) return;

    // 2. API Requests: Network Only
    if (url.pathname.startsWith('/api')) {
        return;
    }

    // 3. Navigation (HTML): Network First -> Cache -> Offline Fallback
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then((networkResponse) => {
                    // Network success: Cache the fresh app shell
                    if (networkResponse.status === 200) {
                        const clone = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    }
                    return networkResponse;
                })
                .catch(() => {
                    // Network failed: Try Runtime Cache (App Shell)
                    return caches.match(event.request).then((cachedResponse) => {
                        if (cachedResponse) {
                            return cachedResponse; // Return cached App Shell
                        }
                        // Fallback: Try Precached Offline Page
                        // Note: exact url match required for workbox precache usually,
                        // but we fallback to our manual cache for '/offline' here.
                        return caches.match('/offline');
                    });
                })
        );
        return;
    }
});
