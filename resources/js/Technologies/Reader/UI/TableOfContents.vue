<script setup>
import { ref, computed, inject, watch } from 'vue';

const store = inject('readerStore');
const themeClasses = inject('themeClasses');

const searchQuery = ref('');
const expandedNodes = ref(new Set());

/**
 * 1. Build basic tree structure to calculate visibility/expansion
 */
const treeData = computed(() => {
    const nodes = JSON.parse(JSON.stringify(store.hierarchy));
    const flatTree = [];
    const map = {};

    // First pass: create map
    nodes.forEach(node => {
        node.children = [];
        map[node._id || node.id] = node;
    });

    // Second pass: link children
    nodes.forEach(node => {
        const parentId = node.parent_id;
        if (parentId && map[parentId]) {
            map[parentId].children.push(node);
        }
    });

    // Third pass: Flatten recursively with level info
    const flatten = (items, level = 0, parentExpanded = true) => {
        items.forEach(item => {
            const id = item._id || item.id;
            const isExpanded = expandedNodes.value.has(id);
            
            flatTree.push({
                ...item,
                level,
                isExpanded,
                hasChildren: item.children && item.children.length > 0,
                isVisible: parentExpanded
            });

            if (item.children && item.children.length > 0) {
                flatten(item.children, level + 1, parentExpanded && isExpanded);
            }
        });
    };

    const roots = nodes.filter(n => !n.parent_id || !map[n.parent_id]);
    flatten(roots);

    return flatTree;
});

/**
 * 2. Filter nodes based on search while preserving structure if matches found in children
 */
const filteredHierarchy = computed(() => {
    if (!searchQuery.value.trim()) {
        return treeData.value.filter(n => n.isVisible);
    }
    
    const query = searchQuery.value.toLowerCase();
    
    // For search, we show all matching nodes and their parents (flattend list)
    // First find all matching IDs
    const matchedIds = new Set();
    const ancestorIds = new Set();
    
    store.hierarchy.forEach(node => {
        const title = node.title?.toLowerCase() || '';
        if (title.includes(query)) {
            const id = node._id || node.id;
            matchedIds.add(id);
            
            // Add all ancestors to ensure path to match is visible
            let current = node;
            while (current && current.parent_id) {
                ancestorIds.add(current.parent_id);
                current = store.hierarchy.find(n => (n._id || n.id) === current.parent_id);
            }
        }
    });

    return treeData.value.filter(node => matchedIds.has(node._id || node.id) || ancestorIds.has(node._id || node.id));
});

const toggleNode = (nodeId) => {
    if (expandedNodes.value.has(nodeId)) {
        expandedNodes.value.delete(nodeId);
    } else {
        expandedNodes.value.add(nodeId);
    }
};

const handleNavigate = (node) => {
    store.navigate(node._id || node.id);
};

const expandAll = () => {
    store.hierarchy.forEach(node => {
        const id = node._id || node.id;
        expandedNodes.value.add(id);
    });
};

const collapseAll = () => {
    expandedNodes.value.clear();
};

// Auto-expand path to active node
watch(() => store.activeChildId, (newId) => {
    if (!newId) return;
    let currentId = newId;
    while (currentId) {
        const node = store.hierarchy.find(n => (n._id || n.id) === currentId);
        if (node && node.parent_id) {
            expandedNodes.value.add(node.parent_id);
            currentId = node.parent_id;
        } else {
            currentId = null;
        }
    }
}, { immediate: true });

const emit = defineEmits(['close']);

</script>

<template>
    <div :class="['flex flex-col h-full border-l shadow-2xl transition-all duration-300', themeClasses.sidebar, themeClasses.border]">
        <!-- TOC Header -->
        <div :class="['p-6 border-b', themeClasses.border]">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📜</span>
                    <h3 class="font-bold text-xl italic tracking-tight">فهرس المحتوى</h3>
                </div>
                <button @click="emit('close')" class="p-2 hover:bg-black/5 rounded-full transition-colors lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- TOC Search & Controls -->
            <div class="flex items-center gap-2">
                <div class="relative group flex-1">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="ابحث..."
                        class="w-full pr-10 pl-4 py-2.5 bg-black/5 border-transparent focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-2xl text-xs transition-all"
                    >
                    <svg
                        class="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    ><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>

                <div class="flex items-center gap-1 shrink-0">
                    <button 
                        @click="expandAll"
                        class="p-2.5 rounded-xl bg-black/5 hover:bg-black/10 text-slate-600 transition-all"
                        title="توسيع الكل"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <button 
                        @click="collapseAll"
                        class="p-2.5 rounded-xl bg-black/5 hover:bg-black/10 text-slate-600 transition-all"
                        title="طي الكل"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- TOC List -->
        <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            <nav class="space-y-1">
                <div 
                    v-for="node in filteredHierarchy" 
                    :key="node.id || node._id"
                    :class="[
                        'w-full text-right p-3 rounded-2xl transition-all duration-200 flex items-center gap-2 group cursor-pointer border border-transparent',
                        (node._id || node.id) === store.activeChildId 
                            ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20' 
                            : 'hover:bg-black/5 dark:hover:bg-white/5'
                    ]"
                    :style="{ marginRight: (node.level * 1.2) + 'rem' }"
                    @click="handleNavigate(node)"
                >
                    <!-- Expand/Collapse Button (Logical Property for RTL) -->
                    <button 
                        v-if="node.hasChildren"
                        @click.stop="toggleNode(node._id || node.id)"
                        class="shrink-0 p-1 hover:bg-black/10 dark:hover:bg-white/10 rounded-lg transition-transform duration-300"
                        :class="{ 'rotate-0': !node.isExpanded, '-rotate-90': node.isExpanded }"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div v-else class="w-6 shrink-0 flex items-center justify-center opacity-20">•</div>

                    <div class="flex-1 min-w-0">
                        <div :class="['truncate text-sm', (node._id || node.id) === store.activeChildId ? 'font-bold' : 'font-medium']">
                            {{ node.title }}
                        </div>
                    </div>

                    <svg 
                        v-if="node.slug === store.currentNode?.slug"
                        xmlns="http://www.w3.org/2000/svg" 
                        class="h-4 w-4 shrink-0 opacity-100" 
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </div>

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
            <p class="text-[10px] font-black opacity-30 uppercase tracking-widest text-wrap">
                إجمالي الأقسام: {{ store.hierarchy.length }}
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

