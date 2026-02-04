<script setup>
import { ref } from 'vue';
import { backupDatabase, restoreDatabase } from '@/Core/Sync/dataPortability';
import { useResilientSync } from '@/Core/Sync/useResilientSync';

const props = defineProps({
    isOpen: Boolean
});

const emit = defineEmits(['close']);

const { isOnline, forceSync, isSyncing } = useResilientSync();
const isExporting = ref(false);
const isRestoring = ref(false);
const fileInput = ref(null);

async function handleBackup() {
    isExporting.value = true;
    try {
        await backupDatabase();
        window.notifySync?.('✅ Backup downloaded successfully', 'success');
    } catch (error) {
        window.notifySync?.('❌ Backup failed: ' + error.message, 'error');
    } finally {
        isExporting.value = false;
    }
}

function triggerFilePicker() {
    fileInput.value.click();
}

async function handleRestore(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!confirm('⚠️ This will wipe ALL current local data and replace it with the backup. Proceed?')) {
        event.target.value = '';
        return;
    }

    isRestoring.value = true;
    try {
        await restoreDatabase(file);
        window.notifySync?.('✅ Database restored successfully!', 'success');
        emit('close');
        // Optional: Reload page to refresh all stores
        window.location.reload();
    } catch (error) {
        window.notifySync?.('❌ Restore failed: ' + error.message, 'error');
    } finally {
        isRestoring.value = false;
        event.target.value = '';
    }
}

async function handleForceSync() {
    try {
        const success = await forceSync();
        if (success) {
            window.notifySync?.('🔄 Force sync initiated...', 'info');
        }
    } catch (error) {
        window.notifySync?.('❌ Force sync failed: ' + error.message, 'error');
    }
}
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/90 backdrop-blur-md transition-all">
        <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <!-- Header -->
            <div class="bg-gradient-to-r from-emerald-600/20 to-blue-600/20 p-6 border-b border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-500/20 rounded-lg">
                        <span class="text-xl">🛡️</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">سيادة البيانات (Data Sovereignty)</h3>
                        <p class="text-xs text-slate-400">تحكم ببياناتك محلياً أو قم بمزامنتها الآن</p>
                    </div>
                </div>
                <button @click="$emit('close')" class="text-slate-500 hover:text-white transition-colors">
                    <span class="text-2xl">×</span>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <!-- Backup Section -->
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                        <span>📦</span> أرشفة البيانات (Local Archive)
                    </h4>
                    <p class="text-sm text-slate-300">قم بتحميل نسخة احتياطية كاملة من بيانات المتصفح (IndexedDB) لحفظها في مكان آمن.</p>
                    <button 
                        @click="handleBackup"
                        :disabled="isExporting"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-slate-700 hover:bg-slate-600 disabled:bg-slate-800 text-white rounded-xl border border-slate-600 transition-all font-semibold shadow-lg"
                    >
                        <span v-if="!isExporting">💾 تحميل النسخة الاحتياطية (.entbak)</span>
                        <span v-else class="flex items-center gap-2">
                            <span class="inline-block w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            جاري التحضير...
                        </span>
                    </button>
                </div>

                <!-- Restore Section -->
                <div class="space-y-3 pt-6 border-t border-slate-700/50">
                    <h4 class="text-sm font-bold text-blue-400 uppercase tracking-wider flex items-center gap-2">
                        <span>🔄</span> استعادة البيانات (Restore)
                    </h4>
                    <p class="text-sm text-slate-300">تحميل نسخة سابقة واستبدال البيانات المحلية الحالية بها.</p>
                    <input 
                        type="file" 
                        ref="fileInput" 
                        @change="handleRestore" 
                        accept=".entbak" 
                        class="hidden" 
                    />
                    <button 
                        @click="triggerFilePicker"
                        :disabled="isRestoring"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 rounded-xl border border-blue-500/30 transition-all font-semibold"
                    >
                        <span v-if="!isRestoring">📥 استعادة من ملف</span>
                        <span v-else class="flex items-center gap-2">
                            <span class="inline-block w-4 h-4 border-2 border-blue-500/30 border-t-blue-500 rounded-full animate-spin"></span>
                            جاري الاستعادة...
                        </span>
                    </button>
                </div>

                <!-- Force Sync Section -->
                <div class="space-y-3 pt-6 border-t border-slate-700/50">
                    <h4 class="text-sm font-bold text-orange-400 uppercase tracking-wider flex items-center gap-2">
                        <span>🚀</span> المزامنة الفورية (Force Sync)
                    </h4>
                    <p class="text-sm text-slate-300">إذا كانت هناك عمليات معلقة أو فاشلة، يمكنك إجبار النظام على محاولة المزامنة الآن.</p>
                    <button 
                        @click="handleForceSync"
                        :disabled="!isOnline || isSyncing"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-orange-600 hover:bg-orange-500 disabled:bg-slate-800 disabled:text-slate-500 text-white rounded-xl transition-all font-bold shadow-lg shadow-orange-900/20"
                    >
                        <span v-if="!isSyncing">⚡ مزامنة كافة العمليات المعلقة</span>
                        <span v-else class="flex items-center gap-2">
                            <span class="inline-block w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            جاري المزامنة...
                        </span>
                    </button>
                    <p v-if="!isOnline" class="text-[10px] text-red-400 text-center">لا يمكن المزامنة لأنك غير متصل بالإنترنت</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 bg-slate-900/50 border-t border-slate-700 text-center">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest">Entity Sovereignty Protocol v1.0</p>
            </div>
        </div>
    </div>
</template>
