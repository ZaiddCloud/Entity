<script setup>
import { ref, watch, inject } from 'vue';
import axios from 'axios';
import { useReaderStore } from '../Core/ReaderStore';

const store = inject('readerStore');
const themeClasses = inject('themeClasses');

const searchQuery = ref('');
const results = ref([]);
const isSearching = ref(false);
const hasSearched = ref(false);

const emit = defineEmits(['close', 'select']);

const handleSearch = async () => {
    if (!searchQuery.value.trim()) {
        results.value = [];
        store.setSearchResults([]);
        return;
    }

    isSearching.value = true;
    hasSearched.value = true;

    try {
        const response = await axios.get(route('reader.search', { 
            type: store.type, 
            slug: store.entity.slug 
        }), {
            params: { q: searchQuery.value }
        });
        results.value = response.data.results;
        store.setSearchResults(response.data.results); // Update store for navigation
    } catch (error) {
        console.error('Search failed:', error);
        results.value = [];
        store.setSearchResults([]);
    } finally {
        isSearching.value = false;
    }
};

// Debounce search
let searchTimeout;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(handleSearch, 500);
});

const handleResultClick = (result) => {
    emit('select', result);
};

const isActive = (result) => {
    return (result.id || result._id) === store.activeChildId;
};

// Auto-scroll to active search result
watch(() => store.activeChildId, (newId) => {
    if (!newId || !results.value.length) return;

    setTimeout(() => {
        const activeEl = document.getElementById(`search-result-${newId}`);
        if (activeEl) {
            activeEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 100);
});
</script>

<template>
    <div :class="['flex flex-col h-full border-l shadow-2xl transition-all duration-300', themeClasses.sidebar, themeClasses.border]">
        <!-- Search Header -->
        <div :class="['p-6 border-b', themeClasses.border]">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-xl italic tracking-tight">البحث في الكيان</h3>
                <button @click="emit('close')" class="p-2 hover:bg-black/5 rounded-full transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Search Input -->
            <div class="relative group">
                <input 
                    v-model="searchQuery"
                    type="text" 
                    placeholder="ابحث عن نص أو عنوان..."
                    class="w-full pr-10 pl-4 py-3 bg-black/5 border-transparent focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-2xl text-sm transition-all"
                    autofocus
                >
                <div class="absolute right-4 top-1/2 -translate-y-1/2">
                    <svg v-if="!isSearching" class="w-5 h-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <svg v-else class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            <div v-if="results.length > 0" class="space-y-3">
                <button 
                    v-for="result in results" 
                    :key="result.id"
                    :id="`search-result-${result.id}`"
                    @click="handleResultClick(result)"
                    :class="[
                        'w-full text-right p-4 my-1 transition-all duration-300 border group items-center relative',
                        isActive(result)
                            ? 'bg-blue-500 text-white shadow-xl shadow-blue-500/30 rounded-2xl scale-[1.02] border-transparent' 
                            : 'bg-black/5 hover:bg-black/10 border-transparent hover:border-blue-500/20 rounded-2xl'
                    ]"
                >
                    <div class="flex items-center justify-between mb-2">
                        <span :class="['font-black text-[10px] uppercase tracking-widest', isActive(result) ? 'text-white' : 'text-blue-500']">{{ result.title }}</span>
                        <div class="flex items-center gap-2">
                            <span v-if="result.timestamp" :class="['text-[10px] font-mono px-1.5 py-0.5 rounded-md transition-opacity', isActive(result) ? 'bg-white/20 text-white' : 'bg-black/5 opacity-40 group-hover:opacity-100']">
                                🕒 {{ Math.floor(result.timestamp / 60) }}:{{ (result.timestamp % 60).toString().padStart(2, '0') }}
                            </span>
                            <span v-if="result.page" :class="['text-[10px] font-bold px-1.5 py-0.5 rounded-md transition-opacity', isActive(result) ? 'bg-white/20 text-white' : 'bg-black/5 opacity-40 group-hover:opacity-100']">
                                📄 صفحة {{ result.page }}
                            </span>
                        </div>
                    </div>
                    <p :class="['text-xs leading-relaxed line-clamp-3', isActive(result) ? 'opacity-90' : 'opacity-70']" v-html="result.snippet.replace(new RegExp(searchQuery, 'gi'), match => `<mark class='${isActive(result) ? 'bg-white/30 text-white' : 'bg-blue-500/20 text-blue-700'} px-0.5 rounded'>${match}</mark>`)"></p>
                    
                    <!-- Capsule Chevron Indicator -->
                    <svg 
                        v-if="isActive(result)"
                        xmlns="http://www.w3.org/2000/svg" 
                        class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 opacity-100 animate-pulse text-white" 
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- Empty States -->
            <div v-else-if="hasSearched && !isSearching" class="flex flex-col items-center justify-center py-20 opacity-20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="font-bold italic">لم يتم العثور على نتائج لـ "{{ searchQuery }}"</p>
            </div>

            <div v-else-if="!hasSearched" class="flex flex-col items-center justify-center py-20 opacity-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-sm font-bold italic">ابدأ البحث في محتوى {{ store.type === 'book' ? 'الكتاب' : store.type === 'video' ? 'الفيديو' : 'الكيان' }}</p>
            </div>
        </div>

        <!-- Search Footer -->
        <div v-if="results.length > 0" :class="['p-4 border-t text-center', themeClasses.border]">
            <p class="text-[10px] font-black opacity-30 uppercase tracking-widest">
                تم العثور على {{ results.length }} نتيجة
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
