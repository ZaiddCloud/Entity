
/**
 * Network Status Composable (Singleton Pattern)
 * Enhanced connectivity monitoring for Local-First applications
 * Shared state to prevent multiple polling intervals
 */

import { ref, onMounted, onUnmounted } from 'vue';

// Global Shared State
const isOnline = ref(navigator.onLine);
const latency = ref(null);
const connectionQuality = ref('unknown'); // excellent, fair, poor, disconnected
const lastChecked = ref(null);

let activeConsumers = 0;
let pingInterval = null;

/**
 * Check actual connectivity by pinging the server
 */
async function checkConnectivity() {
    if (!navigator.onLine) {
        isOnline.value = false;
        connectionQuality.value = 'disconnected';
        return;
    }

    const start = performance.now();
    try {
        // Ping a lightweight endpoint (HEAD request)
        // Ensure no-cache to get real network status
        const response = await fetch('/api/health-check?t=' + Date.now(), {
            method: 'HEAD',
            cache: 'no-cache',
            headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
        });

        if (!response.ok) {
            // If 401 Unauthorized, we are conceptually "Online" but "Unauthenticated".
            // However, for sync purposes, we might consider 401 as "connected but can't sync".
            // For now, if we reach the server, we are online.
            if (response.status === 401 || response.status === 419) {
                isOnline.value = true;
                latency.value = Math.round(performance.now() - start);
                connectionQuality.value = 'fair'; // Authenticated issues, but network is fine
                return;
            }
            throw new Error('OFFLINE_RESPONSE');
        }

        const end = performance.now();
        latency.value = Math.round(end - start);
        isOnline.value = true;
        lastChecked.value = new Date().toISOString();

        // Determine quality
        if (latency.value < 150) connectionQuality.value = 'excellent';
        else if (latency.value < 500) connectionQuality.value = 'fair';
        else connectionQuality.value = 'poor';

    } catch (error) {
        // Silence noise if it's a known offline transition
        if (isOnline.value) {
            console.log('📡 System transitioning to Offline mode.');
        }
        isOnline.value = false;
        connectionQuality.value = 'disconnected';
    }
}

const handleOnline = () => {
    isOnline.value = true;
    checkConnectivity();
};

const handleOffline = () => {
    isOnline.value = false;
    connectionQuality.value = 'disconnected';
};

// Start the global poller
function startGlobalPoller() {
    if (!pingInterval) {
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        // Initial check
        checkConnectivity();

        // Periodic background check every 60s (Increased from 30s) + Jitter
        // Random jitter 0-5s to prevent thundering herd if multiple tabs open same time
        pingInterval = setInterval(checkConnectivity, 60000 + Math.random() * 5000);
    }
}

// Stop the global poller (only if no consumers)
function stopGlobalPoller() {
    if (activeConsumers <= 0 && pingInterval) {
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);
        clearInterval(pingInterval);
        pingInterval = null;
    }
}

export function useNetworkStatus() {
    onMounted(() => {
        activeConsumers++;
        startGlobalPoller();
    });

    onUnmounted(() => {
        activeConsumers--;
        // Optional: Stop if 0, but keeping it running might be better for single page apps 
        // traversing routes. We'll leave it running or use a timeout to stop?
        // For simplicity in this frequent-nav app, let's keep it robust.
        // Actually, if we navigate away, onUnmounted runs. If we navigate to new page, onMounted runs.
        // There might be a split second where consumers = 0.
        // Let's rely on standard GC or keep it running.
        // For strict correctness:
        setTimeout(() => stopGlobalPoller(), 100);
    });

    return {
        isOnline,
        latency,
        connectionQuality,
        lastChecked,
        checkConnectivity
    };
}
