<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useNetworkStatus } from '@/Core/Sync/useNetworkStatus';
import { checkQuota, enforceEvictionPolicy } from '@/Core/Storage/quotaManager';
import { backupDatabase, restoreDatabase } from '@/Core/Sync/dataPortability';
import { useResilientSync } from '@/Core/Sync/useResilientSync';

const { 
    isOnline, 
    isSyncing, 
    storageStats, 
    updateStorageStats, 
    handleDownloadAll 
} = useResilientSync();

const notifications = ref([]);
const showOfflineBanner = ref(false);
const containerRef = ref(null);
const deferredPrompt = ref(null);
const isInstallable = ref(false);

/**
 * Add a temporary notification toast
 */
function addNotification(message, type = 'info') {
    const id = Date.now();
    notifications.value.push({ id, message, type });
    setTimeout(() => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    }, 5000);
}

// Click outside removed (UI moving to Navbar)

// Watchers and Lifecycle
watch(isOnline, (online) => {
    if (!online) {
        showOfflineBanner.value = true;
        addNotification('أنت الآن تعمل في وضع الأوفلاين 📡', 'warning');
    } else {
        showOfflineBanner.value = false;
        addNotification('عاد الاتصال: جاري المزامنة... 🌐', 'success');
        updateStorageStats();
    }
});

onMounted(() => {
    window.notifySync = addNotification;
    updateStorageStats();
    setInterval(updateStorageStats, 30000);

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt.value = e;
        isInstallable.value = true;
    });

    window.addEventListener('appinstalled', () => {
        isInstallable.value = false;
        deferredPrompt.value = null;
        addNotification('🎉 تم تثبيت التطبيق بنجاح!', 'success');
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data?.type === 'SYNC_COMPLETED' && event.data.status === 'success') {
                updateStorageStats();
            }
        });

        // Detect Service Worker updates
        navigator.serviceWorker.ready.then(registration => {
            registration.onupdatefound = () => {
                const installingWorker = registration.installing;
                if (installingWorker) {
                    installingWorker.onstatechange = () => {
                        if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            addNotification('🚀 تحديث جديد متوفر! يرجى إعادة تحميل الصفحة.', 'info');
                        }
                    };
                }
            };
        });
    }
});

onUnmounted(() => {
    // No-op
});
</script>

<template>
    <div class="fixed inset-0 z-[100] pointer-events-none">
        

        <!-- Toast Notifications (Bottom Right) -->
        <div class="fixed bottom-6 right-6 flex flex-col gap-2 items-end">
            <transition-group
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="translate-x-8 opacity-0 blur-sm"
                enter-to-class="translate-x-0 opacity-100 blur-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="translate-x-0 opacity-100 blur-0"
                leave-to-class="translate-x-8 opacity-0 blur-sm"
            >
                <div 
                    v-for="note in notifications" 
                    :key="note.id"
                    class="px-4 py-2.5 rounded-2xl shadow-xl border backdrop-blur-xl text-[11px] font-medium flex items-center gap-2 max-w-sm pointer-events-auto"
                    :class="{
                        'bg-blue-900/90 border-blue-500/30 text-blue-100 shadow-blue-500/20': note.type === 'success',
                        'bg-slate-900/90 border-slate-700/50 text-slate-100 shadow-slate-900/50': note.type === 'info',
                        'bg-orange-900/90 border-orange-500/30 text-orange-100 shadow-orange-500/20': note.type === 'warning',
                        'bg-red-900/90 border-red-500/30 text-red-100': note.type === 'error'
                    }"
                >
                    <span class="text-xs">{{ note.type === 'success' ? '⚡' : note.type === 'warning' ? '📡' : 'ℹ️' }}</span>
                    {{ note.message }}
                </div>
            </transition-group>
        </div>

        <!-- Offline Indicator (Minimal Overlay) -->
        <div class="fixed top-0 inset-x-0 w-full z-50 pointer-events-none">
            <transition
                enter-active-class="transform transition ease-out duration-500"
                enter-from-class="-translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transform transition ease-in duration-300"
                leave-from-class="translate-y-0"
                leave-to-class="-translate-y-full"
            >
                <div v-if="!isOnline" 
                     class="bg-blue-600 text-white text-[10px] font-black uppercase tracking-[0.2em] py-1 text-center shadow-lg border-b border-blue-400/30 backdrop-blur-md bg-opacity-90">
                    <i class="ri-wifi-off-line mr-2"></i>
                    وضعية العمل بدون اتصال (Offline Mode Active)
                </div>
            </transition>
        </div>
    </div>
</template>

<style scoped>
.backdrop-blur-xl {
    backdrop-filter: blur(20px);
}
</style>
