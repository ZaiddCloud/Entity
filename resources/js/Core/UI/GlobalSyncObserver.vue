<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useNetworkStatus } from '@/Core/Sync/useNetworkStatus';
import { checkQuota, enforceEvictionPolicy } from '@/Core/Storage/quotaManager';
import { backupDatabase, restoreDatabase } from '@/Core/Sync/dataPortability';
import { useResilientSync } from '@/Core/Sync/useResilientSync';

const { isOnline, connectionQuality } = useNetworkStatus();
const { forceSync, isSyncing, downloadAllData } = useResilientSync();

const notifications = ref([]);
const showOfflineBanner = ref(false);
const isExpanded = ref(false);
const containerRef = ref(null);

// Storage Stats
const storageStats = ref({ percent: 0, usedMB: 0 });

// Action states
const isExporting = ref(false);
const isRestoring = ref(false);
const isDownloading = ref(false);
const fileInput = ref(null);
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

async function handleDownloadAll(scope = 'full') {
    const message = scope === 'assigned' 
        ? 'سيتم تحميل المهام المسندة إليك فقط. المتابعة؟'
        : 'سيتم تحميل كافة البيانات (بدون الوسائط الثقيلة) للاستخدام أوفلاين. المتابعة؟';

    if (!confirm(message)) return;
    
    isDownloading.value = true;
    try {
        const success = await downloadAllData((percent, msg) => {
            // Optional: update a specific progress bar or just notify
            if (percent % 20 === 0) addNotification(msg, 'info');
        }, scope);
        
        if (success) {
            addNotification('✅ تم تحميل البيانات بنجاح! جاهز للأوفلاين.', 'success');
            updateStorageStats();
        } else {
             addNotification('⚠️ لم يكتمل التحميل بشكل كامل.', 'warning');
        }
    } catch (error) {
        addNotification('❌ خطأ في التحميل: ' + error.message, 'error');
    } finally {
        isDownloading.value = false;
    }
}

async function handleInstallApp() {
    if (!deferredPrompt.value) return;
    deferredPrompt.value.prompt();
    const { outcome } = await deferredPrompt.value.userChoice;
    if (outcome === 'accepted') {
        isInstallable.value = false;
        deferredPrompt.value = null;
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
    window.removeEventListener('mousedown', handleOutsideClick);
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

        <!-- Expanding FAB (Bottom Left) -->
        <!-- Bottom-Left: FAB Group (Data Management + Offline Indicator) -->
        <div class="fixed bottom-4 left-4 flex items-end gap-3 z-50 pointer-events-none">
            <!-- Offline Status Icon (appears when offline) -->
            <transition
                enter-active-class="transform transition ease-out duration-300"
                enter-from-class="scale-0 opacity-0"
                enter-to-class="scale-100 opacity-100"
                leave-active-class="transform transition ease-in duration-200"
                leave-from-class="scale-100 opacity-100"
                leave-to-class="scale-0 opacity-0"
            >
                <div v-if="!isOnline" 
                     class="pointer-events-auto flex items-center gap-2 bg-blue-600/95 backdrop-blur-md text-white px-3 py-2 rounded-full shadow-lg border border-blue-400/30"
                     title="Offline Mode Active">
                    <i class="ri-wifi-off-line text-sm"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider">Offline</span>
                </div>
            </transition>

            <!-- Data Management FAB -->
            <div ref="containerRef" class="pointer-events-auto">
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
                        <span v-if="!isExporting" class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold bg-blue-500/10 text-blue-500">📥</span>
                        <span v-else class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold animate-spin text-blue-500">↻</span>
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

                    <button @click="handleDownloadAll('assigned')" :disabled="!isOnline || isDownloading" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all w-full text-right active:scale-[0.98] disabled:opacity-40 border-none">
                        <span v-if="!isDownloading" class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold bg-violet-500/10 text-violet-500">💼</span>
                        <span v-else class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold animate-pulse text-violet-500">⏳</span>
                        <span class="flex flex-col text-right">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">مهامي (My Tasks)</span>
                            <span class="text-[9px] opacity-60">تحميل ما تم إسناده لي فقط</span>
                        </span>
                    </button>

                    <button @click="handleDownloadAll('full')" :disabled="!isOnline || isDownloading" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all w-full text-right active:scale-[0.98] disabled:opacity-40 border-none">
                        <span v-if="!isDownloading" class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold bg-emerald-500/10 text-emerald-500">📥</span>
                        <span v-else class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold animate-pulse text-emerald-500">⏳</span>
                        <span class="flex flex-col text-right">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">تحميل كامل (Full)</span>
                            <span class="text-[9px] opacity-60">تنزيل كل البيانات للأوفلاين</span>
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
                                    'bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]': storageStats.percent < 0.7,
                                    'bg-orange-500 shadow-[0_0_8px_rgba(249,115,22,0.5)]': storageStats.percent >= 0.7 && storageStats.percent < 0.9,
                                    'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.5)]': storageStats.percent >= 0.9
                                }"
                                :style="{ width: `${storageStats.percent * 100}%` }"
                            ></div>
                        </div>
                    </div>
                    
                    <button v-if="isInstallable" @click="handleInstallApp" class="flex items-center gap-3 p-2.5 rounded-2xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 transition-all w-full text-right active:scale-[0.98] border-none mt-1">
                        <span class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-lg shadow-sm font-bold bg-blue-500/20">📱</span>
                        <span class="flex flex-col text-right">
                            <span class="text-sm font-bold">تثبيت التطبيق</span>
                            <span class="text-[9px] opacity-60">استخدم Entity كتطبيق مستقل</span>
                        </span>
                    </button>

                    <div class="mt-1 px-4 py-2 border-t border-gray-100 dark:border-white/5 text-[8px] font-mono text-center text-gray-400 uppercase tracking-widest">
                        Entity Sovereignty Protocol v1.0
                    </div>
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
