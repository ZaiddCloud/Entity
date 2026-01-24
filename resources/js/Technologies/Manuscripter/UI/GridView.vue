<script setup>
import { useManuscriptStore } from '@/Technologies/Store/ManuscriptStore'

const store = useManuscriptStore()

const handleDblClick = (i) => {
    store.shotNumber = i
    store.viewMode = 'list'
}
</script>

<template>
    <div class="w-full h-full overflow-y-auto custom-scrollbar px-4">
        <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-2 pt-20 pb-20">
            <div
                v-for="i in store.totalPages"
                :key="i" 
                class="aspect-[2/3] relative group cursor-pointer border border-white/5 rounded overflow-hidden bg-white/5 hover:border-blue-500/50 transition-colors"
                @click="store.shotNumber = i"
                @dblclick="handleDblClick(i)"
            >
                <img
                    :src="store.getPageUrl(i, store.displayedVersions[0])"
                    loading="lazy"
                    class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity"
                >
                
                <!-- Overlay: Number Only (Compact) -->
                <div class="absolute inset-0 flex items-end justify-start p-1 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="text-[10px] font-bold text-white font-mono">#{{ i }}</span>
                </div>

                <!-- Active Indicator (Single Mode) -->
                <div
                    v-if="i === store.shotNumber"
                    class="absolute top-1 right-1 w-1.5 h-1.5 rounded-full bg-blue-500 shadow-sm shadow-blue-500/50"
                />
            </div>
        </div>
    </div>
</template>
