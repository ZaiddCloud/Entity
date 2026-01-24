<script setup>
import { useManuscriptStore } from '@/Technologies/Store/ManuscriptStore'

const store = useManuscriptStore()

const handleDblClick = (i) => {
    store.shotNumber = i
    store.viewMode = 'list'
    // Let parent/store handle scrolling or use emit
    // For now we'll emit an event or rely on state watching basically
    // Ideally we should emit 'scrollTo'
}
</script>

<template>
    <div class="w-full h-full overflow-y-auto custom-scrollbar">
        <div class="max-w-5xl mx-auto flex flex-col gap-2 py-8 pt-20 pb-20 px-4">
            <div
                v-for="i in store.totalPages"
                :key="i" 
                class="w-full h-20 flex items-center gap-4 bg-white/5 border border-white/5 rounded-lg p-2 hover:bg-white/10 hover:border-white/10 transition-colors group cursor-pointer"
                @click="store.shotNumber = i"
                @dblclick="handleDblClick(i)"
            >
                <!-- Thumbnail -->
                <div class="h-full aspect-[2/3] bg-black rounded overflow-hidden relative shadow-sm">
                    <img
                        :src="store.getPageUrl(i, store.displayedVersions[0])"
                        loading="lazy"
                        class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity"
                    >
                </div>

                <!-- Metadata Info -->
                <div class="flex flex-col flex-1 gap-1">
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400 font-mono text-sm font-bold">#{{ i }}</span>
                        <span class="text-white/60 text-xs">لقطة {{ i }}</span>
                    </div>
                    <span class="text-xs text-white/40">DSC{{ String(i + 90).padStart(5, '0') }}.JPG</span>
                </div>

                <!-- Actions (Mock) -->
                <div class="flex items-center gap-2 px-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                        class="p-2 rounded hover:bg-white/10 text-white/60 hover:text-white"
                        title="عرض"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
