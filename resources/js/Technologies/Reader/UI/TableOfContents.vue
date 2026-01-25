<script setup>
import { ref, computed, inject } from 'vue';

const store = inject('readerStore');
const themeClasses = inject('themeClasses');

const searchQuery = ref('');

const filteredHierarchy = computed(() => {
    if (!searchQuery.value.trim()) return store.hierarchy;
    const query = searchQuery.value.toLowerCase();
    return store.hierarchy.filter(node => 
        node.title?.toLowerCase().includes(query) || 
        node.metadata?.description?.toLowerCase().includes(query)
    );
});

const emit = defineEmits(['close']);

</script>

<template>
    <div :class="['flex flex-col h-full border-l shadow-2xl transition-all duration-300', themeClasses.sidebar, themeClasses.border]">
        <!-- TOC Header -->
        <div :class="['p-6 border-b', themeClasses.border]">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-xl italic tracking-tight">فهرس المحتوى</h3>
                <button @click="emit('close')" class="p-2 hover:bg-black/5 rounded-full transition-colors lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- TOC Search -->
            <div class="relative group">
                <input 
                    v-model="searchQuery"
                    type="text" 
                    placeholder="ابحث في العناوين..."
                    class="w-full pr-10 pl-4 py-3 bg-black/5 border-transparent focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-2xl text-sm transition-all"
                >
                <svg
                    class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                ><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>

        <!-- TOC List -->
        <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            <nav class="space-y-1">
                <button 
                    v-for="node in filteredHierarchy" 
                    :key="node.id"
                    @click="store.navigate(node.slug)"
                    :class="[
                        'w-full text-right p-4 rounded-2xl transition-all duration-200 flex items-center justify-between group',
                        node.slug === store.currentNode?.slug 
                            ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20 scale-[1.02]' 
                            : 'hover:bg-black/5'
                    ]"
                >
                    <div class="flex flex-col overflow-hidden">
                        <span class="font-bold text-sm truncate">{{ node.title }}</span>
                        <span v-if="node.metadata?.duration" class="text-[10px] opacity-60">
                            {{ Math.floor(node.metadata.duration / 60) }} دقيقة
                        </span>
                    </div>

                    <svg 
                        xmlns="http://www.w3.org/2000/svg" 
                        :class="['h-4 w-4 transition-transform', node.slug === store.currentNode?.slug ? 'opacity-100' : 'opacity-0 group-hover:opacity-40']" 
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div v-if="filteredHierarchy.length === 0" class="flex flex-col items-center justify-center py-20 opacity-20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-bold italic">لا يوجد نتائج للبحث</p>
                </div>
            </nav>
        </div>

        <!-- TOC Footer -->
        <div :class="['p-4 border-t text-center', themeClasses.border]">
            <p class="text-[10px] font-black opacity-30 uppercase tracking-widest">
                إجمالي العناصر: {{ store.hierarchy.length }}
            </p>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 10px;
}
.theme-dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.05);
}
</style>
