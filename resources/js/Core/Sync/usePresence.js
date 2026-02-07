import { ref, onUnmounted } from 'vue';
import axios from 'axios';

export function usePresence() {
    const activeUsers = ref([]);
    const count = ref(0);
    const isLoading = ref(false);
    let heartbeatInterval = null;

    /**
     * الانضمام للمستند وبدء نبضات القلب (Heartbeats)
     */
    const join = async (type, slug) => {
        if (heartbeatInterval) leave();

        const sendHeartbeat = async () => {
            try {
                const response = await axios.post(route('api.presence.heartbeat', { type, slug }));
                activeUsers.value = response.data.users;
                count.value = response.data.count;
            } catch (error) {
                console.warn('[Presence] Heartbeat failed:', error);
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
