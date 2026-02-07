<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { searchEntities } from '@/Core/Sync/searchEngine';

const isOpen = ref(false);
const searchQuery = ref('');
const results = ref([]);
const isSearching = ref(false);
const selectedIndex = ref(0);

// Toggle search modal with Cmd+K / Ctrl+K
const handleKeyDown = (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        isOpen.value = !isOpen.value;
        if (isOpen.value) {
            setTimeout(() => document.getElementById('quick-search-input')?.focus(), 100);
        }
    }
    
    if (!isOpen.value) return;

    if (e.key === 'Escape') {
        isOpen.value = false;
    } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = (selectedIndex.value + 1) % results.value.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = (selectedIndex.value - 1 + results.value.length) % results.value.length;
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (results.value[selectedIndex.value]) {
            navigateTo(results.value[selectedIndex.value]);
        }
    }
};

onMounted(() => window.addEventListener('keydown', handleKeyDown));
onUnmounted(() => window.removeEventListener('keydown', handleKeyDown));

// Perform search
watch(searchQuery, async (val) => {
    if (!val || val.length < 2) {
        results.value = [];
        return;
    }
    
    isSearching.value = true;
    try {
        results.value = await searchEntities(val);
        selectedIndex.value = 0;
    } finally {
        isSearching.value = false;
    }
});

const navigateTo = (entity) => {
    console.log('🔍 QuickSearch Navigation:', { 
        id: entity.id, 
        slug: entity.slug, 
        type: entity.type 
    });

    isOpen.value = false;
    searchQuery.value = '';
    
    // Map type to route
    const routeMap = {
        book: 'books.show',
        audio: 'audios.show',
        video: 'videos.show',
        manuscript: 'manuscripts.show'
    };
    
    // Explicitly pass slug to the expected parameter name
    const params = {};
    params[entity.type] = entity.slug || entity.id;
    
    router.visit(route(routeMap[entity.type], params));
};

const getIcon = (type) => {
    switch (type) {
        case 'book': return '📚';
        case 'audio': return '🎙️';
        case 'video': return '📽️';
        case 'manuscript': return '📜';
        default: return '📄';
    }
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-start justify-center pt-[15vh] p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="isOpen = false">
        <div class="w-full max-w-2xl bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden animate-in fade-in zoom-in duration-200">
            <!-- Search Header -->
            <div class="relative flex items-center border-b border-slate-100 dark:border-slate-700 p-4">
                <span class="absolute left-6 text-slate-400">
                    <svg v-if="!isSearching" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <div v-else class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                </span>
                <input 
                    id="quick-search-input"
                    v-model="searchQuery"
                    type="text" 
                    placeholder="ابحث عن محتوى (كتب، صوتيات، مخطوطات)..."
                    class="w-full pl-12 pr-4 py-2 bg-transparent border-none focus:ring-0 text-slate-800 dark:text-slate-100 text-lg text-right"
                    dir="rtl"
                />
                <kbd class="hidden sm:inline-flex items-center gap-1 px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-[10px] font-medium text-slate-400">
                    ESC
                </kbd>
            </div>

            <!-- Results List -->
            <div class="max-h-[60vh] overflow-y-auto p-2" v-if="results.length > 0">
                <div 
                    v-for="(entity, index) in results" 
                    :key="entity.id"
                    @mouseenter="selectedIndex = index"
                    @click="navigateTo(entity)"
                    class="flex items-center gap-4 p-3 rounded-xl cursor-pointer transition-colors text-right dir-rtl"
                    :class="index === selectedIndex ? 'bg-blue-50 dark:bg-blue-900/40' : 'hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                >
                    <div class="w-10 h-10 flex items-center justify-center bg-white dark:bg-slate-900 rounded-lg shadow-sm text-xl border border-slate-100 dark:border-slate-700">
                        {{ getIcon(entity.type) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate">
                            {{ entity.title }}
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                            {{ entity.author || 'غير محدد' }} • {{ entity.type }}
                        </div>
                    </div>
                    <div v-if="index === selectedIndex" class="text-xs text-blue-500 font-bold opacity-60">
                        ↵ اختيار
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="searchQuery.length >= 2" class="p-12 text-center">
                <div class="text-4xl mb-4">🔍</div>
                <div class="text-slate-800 dark:text-slate-200 font-bold">لا يوجد نتائج</div>
                <div class="text-slate-500 text-sm mt-1">جرب كلمات مختلفة أو تأكد من مزامنة البيانات</div>
            </div>

            <!-- Search Footer -->
            <div class="flex items-center justify-between px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 text-[10px] text-slate-400">
                <div class="flex gap-4">
                    <span class="flex items-center gap-1"><kbd class="px-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">↵</kbd> للانتقال</span>
                    <span class="flex items-center gap-1"><kbd class="px-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">↓↑</kbd> للتنقل</span>
                </div>
                <span>محرك بحث Entity المحلي ⚡</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-in {
    animation: animate-in 0.2s ease-out;
}
@keyframes animate-in {
    from { opacity: 0; transform: scale(0.95) translateY(-10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.dir-rtl { direction: rtl; }
</style>
