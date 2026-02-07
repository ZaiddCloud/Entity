/**
 * Entity Project - Service Worker v12 (Safe Mode)
 * Focus: Asset Caching without Aggressive Offline Fallback
 */

const CACHE_NAME = 'entity-cache-v12';

// Minimal static assets to cache for performance
const ASSETS_TO_CACHE = [
    '/offline', // Keep available just in case, but don't force it
    '/images/grid.svg',
    '/js/vendor/dexie.js'
];

self.addEventListener('install', (event) => {
    console.log('👷 SW v12: Installing...');
    self.skipWaiting(); // Activate immediately
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE).catch(err => {
                console.warn('👷 SW: Cache addAll failed, skipping', err);
            });
        })
    );
});

self.addEventListener('activate', (event) => {
    console.log('👷 SW v12: Activating & Cleaning...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
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

    // 2. Network-Only for API and Navigation (To prevent "Offline Sanctum" Lockout)
    // We explicitly do NOT cache or fallback for HTML navigation to avoid the loop.
    if (event.request.mode === 'navigate' || url.pathname.startsWith('/api')) {
        return; // Let the browser handle it (Network -> Standard Offline Page)
    }

    // 3. Stale-While-Revalidate for Static Assets (Images, Fonts, JS, CSS)
    if (url.pathname.match(/\.(js|css|png|jpg|jpeg|svg|woff2|ico)$/)) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                const networkFetch = fetch(event.request).then((networkResponse) => {
                    // Update cache if valid
                    if (networkResponse && networkResponse.status === 200) {
                        const clone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                    }
                    return networkResponse;
                }).catch(() => {
                    // Network failed, nothing to do here
                });

                // Return cached response immediately if available, else wait for network
                return cachedResponse || networkFetch;
            })
        );
    }
});
