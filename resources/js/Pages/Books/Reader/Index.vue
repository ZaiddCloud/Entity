<template>
    <div class="flex flex-col h-screen bg-slate-50 text-slate-900 font-sans" dir="rtl">
        <!-- Top Navigation -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shadow-sm z-20">
            <div class="flex items-center gap-4">
                <Link href="/books" class="p-2 hover:bg-slate-100 rounded-full transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-800">{{ book.title }}</h1>
                    <p class="text-xs text-slate-500">جاري القراءة...</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <button @click="toggleDarkMode" class="p-2 hover:bg-slate-100 rounded-lg text-slate-600 transition-colors">
                    <svg v-if="!isDark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>
                <div class="h-6 w-px bg-slate-200 mx-2"></div>
                <span class="text-sm font-medium text-slate-600">المحقق: {{"غير محدد"}}</span>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden relative">
            <!-- Sidebar: Hierarchy -->
            <aside 
                class="w-80 bg-white border-l border-slate-200 overflow-y-auto transition-all duration-300 z-10"
                :class="{ '-mr-80': sidebarCollapsed }"
            >
                <div class="p-4 sticky top-0 bg-white z-10 border-b border-slate-100 mb-2">
                    <input 
                        id="sidebar-search"
                        name="sidebar-search"
                        type="text" 
                        placeholder="ابحث في الفهرس..." 
                        v-model="searchQuery"
                        class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                    />
                </div>
                
                <nav class="px-2 pb-10">
                    <TreeItem 
                        v-for="item in rootItems" 
                        :key="item.id" 
                        :item="item" 
                        :all-items="hierarchy"
                        :selected-id="selectedId"
                        @select="selectChapter"
                    />
                </nav>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-8 lg:p-12 relative bg-[#fcfbf9] selection:bg-amber-100">
                <!-- Toggle Sidebar Button (Vertical) -->
                <button 
                    @click="sidebarCollapsed = !sidebarCollapsed" 
                    class="absolute top-1/2 -right-3 -translate-y-1/2 p-1 bg-white border border-slate-200 rounded-full shadow-lg hover:bg-slate-50 transition-all z-20 group"
                    :class="{ 'rotate-180 -left-3 right-auto': sidebarCollapsed }"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 group-hover:text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div class="max-w-3xl mx-auto min-h-screen">
                    <Transition 
                        name="fade-slide" 
                        mode="out-in"
                    >
                        <div v-if="loading" key="loading" class="flex flex-col items-center justify-center pt-32 space-y-4">
                            <div class="w-12 h-12 border-4 border-amber-200 border-t-amber-600 rounded-full animate-spin"></div>
                            <p class="text-slate-400 text-sm animate-pulse">جاري تحميل المحتوى...</p>
                        </div>

                        <div v-else-if="currentChapter" :key="currentChapter.id" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
                            <!-- Chapter Header -->
                            <div class="text-center mb-12">
                                <span class="inline-block px-3 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-bold mb-4 tracking-wider uppercase">
                                    {{ getTypeName(currentChapter.type) }}
                                </span>
                                <h2 class="text-4xl md:text-5xl font-extrabold text-slate-800 leading-tight font-serif tracking-tight">
                                    {{ currentChapter.title }}
                                </h2>
                                <div class="w-24 h-1 bg-gradient-to-r from-transparent via-amber-400 to-transparent mx-auto mt-8"></div>
                            </div>

                            <!-- Content Blocks -->
                            <div class="space-y-10">
                                <template v-if="contentBlocks.length > 0">
                                    <BlockRenderer 
                                        v-for="block in contentBlocks" 
                                        :key="block.id" 
                                        :block="block" 
                                    />
                                </template>
                                <div v-else class="py-12 px-8 bg-amber-50/30 rounded-3xl border border-amber-100/50">
                                    <div class="flex items-center gap-4 mb-6">
                                        <div class="p-3 bg-amber-100 rounded-2xl text-amber-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-amber-900">وصف القسم</h3>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed italic">
                                        {{ metadata.description || 'هذا القسم يعمل كمنظم للفصول والمواد العلمية التالية، لا يحتوي على نص مباشر حالياً.' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Footer Navigation -->
                            <div class="pt-20 pb-10 flex justify-between items-center border-t border-slate-100">
                                <button class="group flex items-center gap-2 text-slate-500 hover:text-amber-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 bg-slate-100 rounded p-1 group-hover:bg-amber-100 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    <span class="text-sm font-medium">السابق</span>
                                </button>
                                <button class="group flex items-center gap-2 text-slate-500 hover:text-amber-600 transition-colors">
                                    <span class="text-sm font-medium">التالي</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 bg-slate-100 rounded p-1 group-hover:bg-amber-100 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div v-else key="empty" class="flex flex-col items-center justify-center pt-48 opacity-40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <p class="mt-6 text-xl font-serif">اختر فصلاً من الفهرس للبدء بالقراءة</p>
                        </div>
                    </Transition>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import TreeItem from './TreeItem.vue';
import BlockRenderer from './BlockRenderer.vue';
import { debounce } from 'lodash';

const props = defineProps({
    book: Object,
    initialHierarchy: Array,
    initialContent: Object,
    childId: String
});

const hierarchy = ref(props.initialHierarchy);
const selectedId = ref(props.childId);
const currentChapter = ref(props.initialContent);
const contentBlocks = computed(() => currentChapter.value?.content_blocks || []);
const loading = ref(false);
const sidebarCollapsed = ref(false);
const isDark = ref(false);
const searchQuery = ref('');

// Metadata for the current chapter/volume
const metadata = computed(() => currentChapter.value?.metadata || {});

const rootItems = computed(() => {
    if (searchQuery.value.trim()) {
        // Flat list of matches when searching
        return hierarchy.value.filter(item => 
            item.title.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }
    return hierarchy.value.filter(item => !item.parent_id);
});

// Helper to determine if we are in search mode for the template
const isSearching = computed(() => !!searchQuery.value.trim());

// Watch for URL changes to load content without full page reload if possible
// Though Inertia will handle the prop updates, we might want to sync local state
watch(() => props.childId, (newId) => {
    selectedId.value = newId;
});

watch(() => props.initialContent, (newContent) => {
    if (newContent) {
        currentChapter.value = newContent;
    } else if (!props.childId) {
        currentChapter.value = null;
    }
}, { immediate: true });

const fetchChapterContent = async (id) => {
    if (!id) return;
    loading.value = true;
    try {
        const response = await axios.get(route('book-contents.show', id));
        currentChapter.value = { id, ...response.data };
        contentBlocks.value = response.data.content_blocks || [];
    } catch (error) {
        console.error("Error loading chapter content:", error);
    } finally {
        loading.value = false;
    }
};

const selectChapter = (item) => {
    // This now just handles UI state if needed, navigation is in TreeItem
    selectedId.value = item.id;
};

const getTypeName = (type) => {
    const types = {
        'part': 'جزء',
        'sub-book': 'كتاب فرعي',
        'chapter': 'فصل',
        'masala': 'مسألة',
        'section': 'مبحث'
    };
    return types[type] || 'قسم';
};

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    // Implementation for dark mode toggle on body if needed
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Outfit:wght@300;400;600;700&display=swap');

.font-sans {
    font-family: 'Outfit', sans-serif;
}

.font-serif {
    font-family: 'Amiri', serif;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
