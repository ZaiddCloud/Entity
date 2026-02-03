/**
 * Network Status Composable
 * Enhanced connectivity monitoring for Local-First applications
 */

import { ref, onMounted, onUnmounted } from 'vue';

export function useNetworkStatus() {
    const isOnline = ref(navigator.onLine);
    const latency = ref(null);
    const connectionQuality = ref('unknown'); // excellent, fair, poor, disconnected
    const lastChecked = ref(null);

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
            // Ping a lightweight endpoint (or just a HEAD request)
            await fetch('/api/health-check', {
                method: 'HEAD',
                cache: 'no-cache'
            });

            const end = performance.now();
            latency.value = Math.round(end - start);
            isOnline.value = true;
            lastChecked.value = new Date().toISOString();

            // Determine quality
            if (latency.value < 150) connectionQuality.value = 'excellent';
            else if (latency.value < 500) connectionQuality.value = 'fair';
            else connectionQuality.value = 'poor';

        } catch (error) {
            console.warn('📡 Network check failed:', error);
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

    onMounted(() => {
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        // Initial check
        checkConnectivity();

        // Periodic background check every 30s
        pingInterval = setInterval(checkConnectivity, 30000);
    });

    onUnmounted(() => {
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);
        if (pingInterval) clearInterval(pingInterval);
    });

    return {
        isOnline,
        latency,
        connectionQuality,
        lastChecked,
        checkConnectivity
    };
}
