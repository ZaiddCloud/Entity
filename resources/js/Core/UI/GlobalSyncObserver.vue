<script setup>
import { ref, watch, onMounted } from 'vue';
import { useNetworkStatus } from '@/Core/Sync/useNetworkStatus';

const { isOnline, connectionQuality } = useNetworkStatus();
const notifications = ref([]);
const showOfflineBanner = ref(false);

// Watch for network changes
watch(isOnline, (online) => {
    if (!online) {
        showOfflineBanner.value = true;
        addNotification('System Offline: Working in Sanctuary Mode 📡', 'warning');
    } else {
        showOfflineBanner.value = false;
        addNotification('Back Online: Reconnecting to server... 🌐', 'success');
    }
});

/**
 * Add a temporary notification toast
 */
function addNotification(message, type = 'info') {
    const id = Date.now();
    notifications.value.push({ id, message, type });
    
    // Auto-remove after 5s
    setTimeout(() => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    }, 5000);
}

// Expose addNotification to window for global access (or use a registry)
onMounted(() => {
    window.notifySync = addNotification;
});
</script>

<template>
    <div class="fixed inset-x-0 top-0 z-[100] pointer-events-none">
        <!-- Offline Banner -->
        <transition
            enter-active-class="transform transition ease-out duration-500"
            enter-from-class="-translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transform transition ease-in duration-300"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="-translate-y-full opacity-0"
        >
            <div v-if="showOfflineBanner" class="bg-orange-600/95 backdrop-blur-md text-white py-1.5 px-4 text-center text-xs font-bold flex items-center justify-center gap-2 border-b border-orange-400/30 shadow-lg pointer-events-auto">
                <span class="animate-pulse">📡</span>
                <span>OFFLINE MODE ACTIVE — Your changes are being saved locally</span>
            </div>
        </transition>

        <!-- Toast Container -->
        <div class="fixed bottom-6 right-6 flex flex-col gap-3 items-end">
            <transition-group
                enter-active-class="transform transition ease-out duration-300"
                enter-from-class="translate-x-full opacity-0 scale-95"
                enter-to-class="translate-x-0 opacity-100 scale-100"
                leave-active-class="transform transition ease-in duration-200"
                leave-from-class="translate-x-0 opacity-100 scale-100"
                leave-to-class="translate-x-full opacity-0 scale-95"
            >
                <div 
                    v-for="note in notifications" 
                    :key="note.id"
                    class="p-4 rounded-xl shadow-2xl border backdrop-blur-xl flex items-center gap-3 max-w-sm pointer-events-auto"
                    :class="{
                        'bg-emerald-900/90 border-emerald-500/30 text-emerald-100': note.type === 'success',
                        'bg-slate-800/90 border-slate-700 text-slate-100': note.type === 'info',
                        'bg-orange-900/90 border-orange-500/30 text-orange-100': note.type === 'warning',
                        'bg-red-900/90 border-red-500/30 text-red-100': note.type === 'error'
                    }"
                >
                    <div class="flex-shrink-0 text-xl">
                        {{ note.type === 'success' ? '✅' : note.type === 'warning' ? '⚠️' : note.type === 'error' ? '❌' : 'ℹ️' }}
                    </div>
                    <div class="text-sm font-medium leading-tight">
                        {{ note.message }}
                    </div>
                </div>
            </transition-group>
        </div>
    </div>
</template>

<style scoped>
.backdrop-blur-xl {
    backdrop-filter: blur(20px);
}
</style>
