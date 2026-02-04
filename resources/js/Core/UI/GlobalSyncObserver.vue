<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useNetworkStatus } from '@/Core/Sync/useNetworkStatus';
import { checkQuota, enforceEvictionPolicy } from '@/Core/Storage/quotaManager';
import { backupDatabase, restoreDatabase } from '@/Core/Sync/dataPortability';
import { useResilientSync } from '@/Core/Sync/useResilientSync';

const { isOnline, connectionQuality } = useNetworkStatus();
const { forceSync, isSyncing } = useResilientSync();

const notifications = ref([]);
const showOfflineBanner = ref(false);
const isExpanded = ref(false);
const containerRef = ref(null);

// Storage Stats
const storageStats = ref({ percent: 0, usedMB: 0 });

// Action states
const isExporting = ref(false);
const isRestoring = ref(false);
const fileInput = ref(null);

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

/**
 * Check Storage Quota
 */
async function updateStorageStats() {
    try {
        const stats = await checkQuota();
        storageStats.value = stats;
        
        if (stats.percent > 0.8) {
             const result = await enforceEvictionPolicy();
             if (result.evicted > 0) {
                 addNotification(`تطهير تلقائي: تم تحرير ${result.freedBytes} بايت`, 'info');
                 updateStorageStats();
             }
        }
    } catch (e) {
        console.error('Failed to check storage', e);
    }
}

/**
 * Portability Actions
 */
async function handleBackup() {
    isExporting.value = true;
    try {
        await backupDatabase();
        addNotification('✅ تم تحميل النسخة الاحتياطية بنجاح', 'success');
    } catch (error) {
        addNotification('❌ فشل التصدير: ' + error.message, 'error');
    } finally {
        isExporting.value = false;
    }
}

async function handleRestore(event) {
    const file = event.target.files[0];
    if (!file) return;
    if (!confirm('⚠️ سيتم استبدال البيانات الحالية بالكامل. هل أنت متأكد؟')) {
        event.target.value = '';
        return;
    }
    isRestoring.value = true;
    try {
        await restoreDatabase(file);
        addNotification('✅ تم استعادة البيانات بنجاح!', 'success');
        window.location.reload();
    } catch (error) {
        addNotification('❌ فشل الاستعادة: ' + error.message, 'error');
    } finally {
        isRestoring.value = false;
        event.target.value = '';
    }
}

async function handleForceSync() {
    try {
        const success = await forceSync();
        if (success) {
            addNotification('🔄 جاري بدء المزامنة القسرية...', 'info');
        }
    } catch (error) {
        addNotification('❌ فشلت المزامنة: ' + error.message, 'error');
    }
}

// Click outside to close
function handleOutsideClick(event) {
    if (isExpanded.value && containerRef.value && !containerRef.value.contains(event.target)) {
        isExpanded.value = false;
    }
}

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
    window.addEventListener('mousedown', handleOutsideClick);

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data?.type === 'SYNC_COMPLETED' && event.data.status === 'success') {
                updateStorageStats();
            }
        });
    }
});

onUnmounted(() => {
    window.removeEventListener('mousedown', handleOutsideClick);
});
</script>

<template>
    <div class="fixed inset-0 z-[100] pointer-events-none">
        
        <!-- Offline Banner (Subtle top bar) -->
        <transition
            enter-active-class="transform transition ease-out duration-500"
            enter-from-class="-translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transform transition ease-in duration-300"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="-translate-y-full opacity-0"
        >
            <div v-if="showOfflineBanner" class="bg-orange-600/95 backdrop-blur-md text-white py-1.5 px-4 text-center text-[10px] font-bold uppercase tracking-widest border-b border-orange-400/30 shadow-lg pointer-events-auto">
                Offline Mode Active — Working Locally
            </div>
        </transition>

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
                        'bg-emerald-900/90 border-emerald-500/30 text-emerald-100': note.type === 'success',
                        'bg-slate-900/90 border-slate-700/50 text-slate-100 shadow-slate-900/50': note.type === 'info',
                        'bg-orange-900/90 border-orange-500/30 text-orange-100': note.type === 'warning',
                        'bg-red-900/90 border-red-500/30 text-red-100': note.type === 'error'
                    }"
                >
                    <span class="text-xs">{{ note.type === 'success' ? '✅' : note.type === 'warning' ? '⚠️' : 'ℹ️' }}</span>
                    {{ note.message }}
                </div>
            </transition-group>
        </div>

        <!-- Expanding FAB (Bottom Left) -->
        <div ref="containerRef" class="fixed bottom-4 left-4 pointer-events-auto z-50">
            <div 
                class="relative transition-all duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] overflow-hidden bg-white/80 dark:bg-black/80 hover:bg-white dark:hover:bg-black backdrop-blur-md border border-gray-200 dark:border-white/10 rounded-[2rem] shadow-lg flex flex-col"
                :class="isExpanded ? 'w-64 p-2' : 'w-auto px-3 py-1.5'"
            >
                <!-- Initial State (Minimal Pill) -->
                <button 
                    @click="isExpanded = !isExpanded"
                    class="flex items-center justify-center gap-2 w-full text-xs text-gray-500 dark:text-gray-400 no-underline outline-none shrink-0 transition-all duration-300"
                    :class="isExpanded ? 'mb-3 px-3 pt-1 border-b border-gray-100 dark:border-white/5 pb-2' : 'px-3 py-1.5'"
                >
                    <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="{
                        'bg-emerald-500': storageStats.percent < 0.7,
                        'bg-orange-500': storageStats.percent >= 0.7 && storageStats.percent < 0.9,
                        'bg-red-500': storageStats.percent >= 0.9
                    }"></span>
                    
                    <span class="font-mono opacity-80 whitespace-nowrap tabular-nums">
                        {{ storageStats.usedMB }}MB
                    </span>

                    <span class="text-sm">🛡️</span>

                    <template v-if="isExpanded">
                         <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mr-2">إدارة البيانات</span>
                         <span class="mr-auto text-lg leading-none opacity-40 hover:opacity-100 transition-opacity">&times;</span>
                    </template>
                </button>

                <!-- Expanded Content (Actions) -->
                <div v-show="isExpanded" class="flex flex-col gap-1 transition-all duration-300">
                    <button @click="handleBackup" :disabled="isExporting" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all w-full text-right active:scale-[0.98] disabled:opacity-40">
                        <span v-if="!isExporting" class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold bg-emerald-500/10 text-emerald-500">📥</span>
                        <span v-else class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold animate-spin">↻</span>
                        <span class="flex flex-col text-right">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">تصدير (Backup)</span>
                            <span class="text-[9px] opacity-60">حفظ نسخة كاملة للجهاز</span>
                        </span>
                    </button>

                    <button @click="fileInput.click()" :disabled="isRestoring" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all w-full text-right active:scale-[0.98] disabled:opacity-40">
                        <input type="file" ref="fileInput" @change="handleRestore" accept=".entbak" class="hidden" />
                        <span v-if="!isRestoring" class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold bg-blue-500/10 text-blue-500">📤</span>
                        <span v-else class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold animate-spin">↻</span>
                        <span class="flex flex-col text-right">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">استعادة (Restore)</span>
                            <span class="text-[9px] opacity-60">رفع ملف .entbak</span>
                        </span>
                    </button>

                    <button @click="handleForceSync" :disabled="!isOnline || isSyncing" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all w-full text-right active:scale-[0.98] disabled:opacity-40 border-none">
                        <span v-if="!isSyncing" class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold bg-orange-500/10 text-orange-500">⚡</span>
                        <span v-else class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold animate-spin">↻</span>
                        <span class="flex flex-col text-right">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">مزامنة (Sync)</span>
                            <span class="text-[9px] opacity-60">تحديث فوري مع السيرفر</span>
                        </span>
                    </button>

                    <!-- Storage Quota Details -->
                    <div class="mt-2 px-3 py-2 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-100 dark:border-white/5">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">سعة التخزين محلياً</span>
                            <span class="text-[9px] font-mono text-gray-500" dir="ltr">
                                {{ storageStats.usedMB }}MB / {{ storageStats.quotaMB }}MB
                            </span>
                        </div>
                        <div class="w-full h-1 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                            <div 
                                class="h-full transition-all duration-1000" 
                                :class="{
                                    'bg-emerald-500': storageStats.percent < 0.7,
                                    'bg-orange-500': storageStats.percent >= 0.7 && storageStats.percent < 0.9,
                                    'bg-red-500': storageStats.percent >= 0.9
                                }"
                                :style="{ width: `${storageStats.percent * 100}%` }"
                            ></div>
                        </div>
                    </div>
                    
                    <div class="mt-1 px-4 py-2 border-t border-gray-100 dark:border-white/5 text-[8px] font-mono text-center text-gray-400 uppercase tracking-widest">
                        Entity Sovereignty Protocol v1.0
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.backdrop-blur-xl {
    backdrop-filter: blur(20px);
}
</style>
