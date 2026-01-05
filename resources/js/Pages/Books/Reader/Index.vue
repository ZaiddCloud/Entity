<template>
    <div class="flex flex-col h-screen bg-amber-50/30 dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-sans" dir="rtl">
        <!-- Top Navigation -->
        <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-20">
            <div class="flex items-center gap-4">
                <Link href="/books" class="p-2 hover:bg-slate-100 rounded-full transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 font-serif">{{ book.title }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="h-6 w-px bg-slate-200 mx-2"></div>
                <span class="text-sm font-medium text-slate-600">المحقق: {{"غير محدد"}}</span>
                <div class="h-6 w-px bg-slate-200 mx-2"></div>
                <!-- Edit Button: Redirects to the Unified Editor -->
                <Link
                    v-if="currentChapter"
                    :href="route('editor.show', { type: 'book', slug: currentChapter.id })" 
                    class="btn-secondary px-4 py-1.5 rounded-lg text-sm font-bold flex items-center gap-2 text-indigo-600 hover:bg-indigo-50 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    تعديل
                </Link>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden relative">
            <!-- Sidebar: Hierarchy -->
            <aside
                class="w-80 bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 overflow-y-auto transition-all duration-300 z-10"
                :class="{ '-mr-80': sidebarCollapsed }"
            >
                <div class="p-4 sticky top-0 bg-white dark:bg-slate-800 z-10 border-b border-slate-100 dark:border-slate-700 mb-2">
                    <input
                        id="sidebar-search"
                        name="sidebar-search"
                        type="text"
                        placeholder="ابحث في الفهرس..."
                        v-model="searchQuery"
                        class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all dark:text-slate-100 dark:placeholder-slate-400"
                    />

                    <div class="flex items-center justify-between mt-3 px-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">الفهرس</span>
                        <div class="flex items-center gap-1">
                            <button
                                @click="expandAll"
                                class="p-1 hover:bg-slate-100 dark:hover:bg-slate-700 rounded transition-colors text-slate-400 hover:text-amber-600 group"
                                title="توسيع الكل"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 5l-7 7-7-7" class="opacity-50" />
                                </svg>
                            </button>
                            <button
                                @click="collapseAll"
                                class="p-1 hover:bg-slate-100 dark:hover:bg-slate-700 rounded transition-colors text-slate-400 hover:text-amber-600"
                                title="طي الكل"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19l7-7 7 7" class="opacity-50" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <nav class="px-2 pb-10">
                    <!-- Read-only Hierarchy Tree -->
                    <div v-for="item in rootItems" :key="item.id">
                         <TreeItem
                            :item="item"
                            :all-items="hierarchy"
                            :selected-id="selectedId"
                            @select="navigateToChapter"
                        />
                    </div>
                </nav>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-8 lg:p-12 relative bg-[#fcfbf9] dark:bg-slate-900 selection:bg-amber-100 dark:selection:bg-amber-900">
                <!-- Toggle Sidebar Button (Vertical) -->
                <button
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    class="absolute top-1/2 -right-3 -translate-y-1/2 p-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full shadow-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-all z-20 group"
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
                                <h2 class="text-4xl md:text-5xl font-extrabold text-slate-800 dark:text-slate-100 leading-tight font-serif tracking-tight">
                                    {{ currentChapter.title }}
                                </h2>
                                <div class="w-24 h-1 bg-gradient-to-r from-transparent via-amber-400 to-transparent mx-auto mt-8"></div>
                            </div>

                            <!-- Content Blocks (Unified TipTap Reader) -->
                            <div class="reader-container prose prose-slate dark:prose-invert max-w-none prose-lg animate-in fade-in duration-1000">
                                <!-- Reusing the Unified Editor Component in Read-Only Mode -->
                                <TiptapEditor 
                                    v-if="currentChapter.content_blocks"
                                    :model-value="currentChapter.content_blocks"
                                    :editable="false"
                                />

                                <div v-if="!currentChapter.content_blocks || !currentChapter.content_blocks.length" class="py-12 px-8 bg-amber-50/30 rounded-3xl border border-amber-100/50">
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
                                <button
                                    @click="navigateTo(prevChapter)"
                                    :disabled="!prevChapter"
                                    :class="{'opacity-50 cursor-not-allowed': !prevChapter, 'group hover:text-amber-600': prevChapter}"
                                    class="flex items-center gap-2 text-slate-500 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 bg-slate-100 rounded p-1 group-hover:bg-amber-100 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    <span class="text-sm font-medium">
                                        {{ prevChapter ? prevChapter.title : 'البداية' }}
                                    </span>
                                </button>

                                <button
                                    @click="navigateTo(nextChapter)"
                                    :disabled="!nextChapter"
                                    :class="{'opacity-50 cursor-not-allowed': !nextChapter, 'group hover:text-amber-600': nextChapter}"
                                    class="flex items-center gap-2 text-slate-500 transition-colors"
                                >
                                    <span class="text-sm font-medium">
                                        {{ nextChapter ? nextChapter.title : 'النهاية' }}
                                    </span>
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
import { ref, computed, onMounted, watch, provide } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import TreeItem from './TreeItem.vue';
// Use the Unified Editor Engine
import TiptapEditor from '../../Editor/Components/Content/TiptapEditor.vue';

const props = defineProps({
    book: Object,
    initialHierarchy: Array,
    initialContent: Object,
    childId: String
});

const hierarchy = ref(props.initialHierarchy);
const selectedId = ref(props.childId);
const currentChapter = ref(props.initialContent);
const loading = ref(false);
const sidebarCollapsed = ref(typeof window !== 'undefined' ? window.innerWidth < 768 : false);
const searchQuery = ref('');

// Metadata for the current chapter/volume
const metadata = computed(() => currentChapter.value?.metadata || {});

const rootItems = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    if (query) {
        return hierarchy.value.filter(item =>
            item.title.toLowerCase().includes(query)
        );
    }
    return hierarchy.value
        .filter(item => !item.parent_id)
        .sort((a, b) => (a.order || 0) - (b.order || 0));
});

// Watch for URL changes to sync state
watch(() => props.childId, (newId) => {
    selectedId.value = newId;
    if (newId && (!currentChapter.value || currentChapter.value.id !== newId)) {
        fetchChapterContent(newId);
    }
});

watch(() => props.initialContent, (newContent) => {
    if (newContent) {
        currentChapter.value = newContent;
    }
}, { immediate: true });

const fetchChapterContent = async (id) => {
    if (!id) return;
    loading.value = true;
    try {
        const response = await axios.get(route('book-contents.show', id));
        currentChapter.value = { id, ...response.data };
    } catch (error) {
        console.error("Error loading chapter content:", error);
    } finally {
        loading.value = false;
    }
};

const navigateToChapter = (item) => {
    router.visit(route('books.reader', [props.book.slug, item.id]));
};

// Navigation Logic
const flattenedHierarchy = computed(() => {
    const flatten = (parentId = null) => {
        return hierarchy.value
            .filter(item => item.parent_id === parentId)
            .sort((a, b) => (a.order || 0) - (b.order || 0))
            .reduce((acc, item) => {
                return [...acc, item, ...flatten(item.id)];
            }, []);
    };
    return flatten();
});

const prevChapter = computed(() => {
    if (!currentChapter.value) return null;
    const index = flattenedHierarchy.value.findIndex(item => item.id === currentChapter.value.id);
    return index > 0 ? flattenedHierarchy.value[index - 1] : null;
});

const nextChapter = computed(() => {
    if (!currentChapter.value) return null;
    const index = flattenedHierarchy.value.findIndex(item => item.id === currentChapter.value.id);
    return index !== -1 && index < flattenedHierarchy.value.length - 1 ? flattenedHierarchy.value[index + 1] : null;
});

const navigateTo = (chapter) => {
    if (!chapter) return;
    router.visit(route('books.reader', [props.book.slug, chapter.id]));
};

const getTypeName = (type) => {
    const types = {
        'sub-book': 'كتاب فرعي',
        'part': 'جزء',
        'bab': 'باب',
        'chapter': 'فصل',
        'masala': 'مسألة',
        // Fallbacks or others
        'section': 'قسم',
        'prologue': 'تمهيد'
    };
    return types[type] || 'قسم';
};

// Persistence Logic for Sidebar
const getStoredExpanded = () => {
    if (typeof window === 'undefined') return new Set();
    const key = `book_reader_expanded_${props.book.id}`;
    try {
        const stored = localStorage.getItem(key);
        return stored ? new Set(JSON.parse(stored)) : new Set();
    } catch (e) {
        return new Set();
    }
};

const expandedIds = ref(getStoredExpanded());

// Save to LocalStorage whenever it changes
watch(expandedIds, (newSet) => {
    const key = `book_reader_expanded_${props.book.id}`;
    localStorage.setItem(key, JSON.stringify(Array.from(newSet)));
}, { deep: true });

const toggleExpand = (id) => {
    const newSet = new Set(expandedIds.value);
    if (newSet.has(id)) {
        newSet.delete(id);
    } else {
        newSet.add(id);
    }
    expandedIds.value = newSet;
};

const isExpanded = (id) => expandedIds.value.has(id);

const expandAll = () => {
    const parentIds = new Set();
    hierarchy.value.forEach(item => {
        if (item.parent_id) {
            parentIds.add(String(item.parent_id));
        }
    });
    expandedIds.value = parentIds;
};

const collapseAll = () => {
    expandedIds.value = new Set();
};

// Provide to recursive children
provide('sidebarContext', {
    expandedIds,
    toggleExpand,
    isExpanded
});
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
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
