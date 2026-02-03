/**
 * Entity Project - Service Worker
 * Handles background synchronization and offline persistence
 */

const CACHE_NAME = 'entity-sync-v2';

const ASSETS_TO_CACHE = [
    '/offline', // You need to create this route/view
    '/css/app.css',
    '/js/app.js',
    '/images/logo.png' // Adjust based on actual assets
];

self.addEventListener('install', (event) => {
    console.log('👷 Service Worker: Installing & Caching App Shell...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE).catch(err => {
                console.warn('👷 SW: Cache addAll warning:', err);
                // Continue even if some assets fail to cache (resilience)
            });
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('👷 Service Worker: Activated & Cleaning Old Caches');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        console.log('👷 SW: Clearing Old Cache', cache);
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => clients.claim())
    );
});

/**
 * Offline Serving Strategy: Stale-While-Revalidate or Network-First
 */
self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') return;

    // Ignore non-http(s) requests (like chrome-extension://)
    if (!event.request.url.startsWith('http')) return;

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // If effective network response, clone it to cache
                if (response && response.status === 200 && response.type === 'basic') {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return response;
            })
            .catch(() => {
                // Network failed, look in cache
                return caches.match(event.request).then((response) => {
                    if (response) {
                        return response;
                    }
                    // If regular page navigation fails, return offline page (if it exists)
                    if (event.request.mode === 'navigate') {
                        return caches.match('/offline') || new Response('<h1>Offline Mode (Sanctuary)</h1><p>You are offline. Your changes are safe locally.</p>', {
                            headers: { 'Content-Type': 'text/html' }
                        });
                    }
                });
            })
    );
});

/**
 * Background Sync Handler
 */
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-entities') {
        console.log('👷 Service Worker: Syncing entities...');
        event.waitUntil(processSyncQueue());
    }
});

/**
 * Communication from the application
 */
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'TRIGGER_SYNC') {
        console.log('👷 Service Worker: Manual sync triggered');
        event.waitUntil(processSyncQueue());
    }
});

/**
 * Placeholder for processing the sync queue
 * In a real implementation, we would import Dexie and useResilientSync logic here
 */
async function processSyncQueue() {
    // This will be expanded to interact with Dexie
    console.log('👷 Service Worker: Processing Sync Queue...');

    // Notify clients that sync is starting
    const allClients = await clients.matchAll();
    allClients.forEach(client => {
        client.postMessage({ type: 'SYNC_STARTED' });
    });

    // Mock processing delay
    await new Promise(resolve => setTimeout(resolve, 1000));

    // Notify clients that sync is done
    allClients.forEach(client => {
        client.postMessage({ type: 'SYNC_COMPLETED', status: 'success' });
    });
}
