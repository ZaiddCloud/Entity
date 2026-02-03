<script setup>
import { computed } from 'vue';

const props = defineProps({
    isOpen: Boolean,
    conflictData: Object
});

const emit = defineEmits(['resolve']);

const serverEntity = computed(() => props.conflictData?.server_version || {});
const clientEntity = computed(() => props.conflictData?.client_version || {});

function resolve(strategy) {
    emit('resolve', strategy);
}
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-sm">
        <div class="bg-slate-800 border-2 border-red-500 rounded-xl max-w-2xl w-full shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-red-500/10 p-4 border-b border-red-500/30 flex items-center gap-3">
                <span class="text-2xl">🛡️</span>
                <div>
                    <h3 class="text-xl font-bold text-white">Sync Conflict Detected</h3>
                    <p class="text-sm text-red-300">The server has a newer version of this content.</p>
                </div>
            </div>

            <!-- Diff View -->
            <div class="p-6 grid grid-cols-2 gap-6">
                <!-- Local Version -->
                <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-700">
                    <div class="text-xs text-emerald-400 font-bold uppercase mb-2 text-center">Your Version (Local)</div>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-slate-500">Title</label>
                            <div class="font-bold text-white">{{ clientEntity.title }}</div>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Last Modified</label>
                            <div class="font-mono text-xs text-slate-300">Just now</div>
                        </div>
                    </div>
                </div>

                <!-- Server Version -->
                <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-700">
                    <div class="text-xs text-blue-400 font-bold uppercase mb-2 text-center">Server Version (Remote)</div>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-slate-500">Title</label>
                            <div class="font-bold text-white">{{ serverEntity.title }}</div>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Last Modified</label>
                            <div class="font-mono text-xs text-slate-300">{{ serverEntity.updated_at }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resolution Actions -->
            <div class="p-4 bg-slate-800 border-t border-slate-700 flex justify-end gap-3">
                <button 
                    @click="resolve('server')"
                    class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors flex items-center gap-2"
                >
                    <span>⬇️</span> Discard My Changes
                </button>
                <button 
                    @click="resolve('client')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition-colors flex items-center gap-2 shadow-lg shadow-emerald-900/20"
                >
                    <span>⬆️</span> Force Overwrite
                </button>
            </div>
        </div>
    </div>
</template>
