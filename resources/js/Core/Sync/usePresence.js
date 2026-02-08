import { ref, onUnmounted } from 'vue';
import axios from 'axios';
import { useNetworkStatus } from '@/Core/Sync/useNetworkStatus';

export function usePresence() {
    const { isOnline } = useNetworkStatus();
    const activeUsers = ref([]);
    const count = ref(0);
    const isLoading = ref(false);
    let heartbeatInterval = null;
    const hasLoggedOffline = ref(false);

    /**
     * الانضمام للمستند وبدء نبضات القلب (Heartbeats)
     */
    const join = async (type, slug) => {
        if (heartbeatInterval) leave();
        isLoading.value = true;

        const sendHeartbeat = async () => {
            if (!type || !slug) return;

            if (!isOnline.value) {
                if (!hasLoggedOffline.value) {
                    console.warn('[Presence] Heartbeat paused: User is offline 📡');
                    hasLoggedOffline.value = true;
                }
                return;
            }

            // Reset the log flag when we are back online
            hasLoggedOffline.value = false;

            try {
                const response = await axios.post(route('api.presence.heartbeat', {
                    type: type,
                    slug: slug
                }));
                activeUsers.value = response.data.users;
                count.value = response.data.count;
                isLoading.value = false;
            } catch (error) {
                // Only warn if we think we are online to avoid noise
                if (isOnline.value) {
                    console.warn('[Presence] Heartbeat failed:', error);
                }
            }
        };

        // النبضة الأولى فوراً
        await sendHeartbeat();

        // تكرار كل 10 ثوانٍ
        heartbeatInterval = setInterval(sendHeartbeat, 10000);
    };

    /**
     * المغادرة وتوقف النبضات
     */
    const leave = () => {
        if (heartbeatInterval) {
            clearInterval(heartbeatInterval);
            heartbeatInterval = null;
        }
    };

    // التأكد من التنظيف عند تدمير المكون
    onUnmounted(() => {
        leave();
    });

    return {
        activeUsers,
        count,
        isLoading,
        join,
        leave
    };
}
