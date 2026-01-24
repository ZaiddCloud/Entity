<template>
  <div class="flex flex-col h-full bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-700">
    <!-- Sidebar Header -->
    <div class="p-4 border-b border-slate-100 dark:border-slate-800">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">
          فهرس المحتوى
        </h3>
        <button
          class="text-[10px] text-indigo-500 hover:text-indigo-600 font-bold"
          @click="collapseAll"
        >
          طي الكل
        </button>
      </div>
            
      <div class="relative group">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="ابحث في الفهرس..."
          class="w-full pr-9 pl-3 py-2 bg-slate-50 dark:bg-slate-800 border-transparent focus:bg-white focus:border-indigo-500 rounded-xl text-xs transition-all"
        >
        <svg
          class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        ><path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
        /></svg>
      </div>
    </div>

    <!-- Sidebar Content -->
    <div class="flex-1 overflow-y-auto p-2 custom-scrollbar">
      <template v-if="rootItems.length">
        <HierarchyTreeItem 
          v-for="item in rootItems" 
          :key="item._id"
          :item="item"
          :all-items="hierarchy"
          :selected-slug="currentSlug"
          @select="handleSelect"
        />
      </template>
      <div
        v-else
        class="flex flex-col items-center justify-center py-10 opacity-30"
      >
        <svg
          class="w-10 h-10 mb-2"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        ><path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
        /></svg>
        <span class="text-[10px] font-bold">لا يوجد نتائج</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, provide } from 'vue';
import HierarchyTreeItem from './HierarchyTreeItem.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    hierarchy: {
        type: Array,
        required: true
    },
    currentSlug: String,
    entityType: String,
    entitySlug: String
});

const searchQuery = ref('');
const expandedIds = ref(new Set());

// Root items (those without a parent)
const rootItems = computed(() => {
    let items = props.hierarchy.filter(item => !item.parent_id);
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        return props.hierarchy.filter(item => item?.title?.toLowerCase().includes(query));
    }
    return items;
});

const toggleExpand = (id) => {
    if (expandedIds.value.has(id)) {
        expandedIds.value.delete(id);
    } else {
        expandedIds.value.add(id);
    }
};

const isExpanded = (id) => expandedIds.value.has(id);

const collapseAll = () => {
    expandedIds.value.clear();
};

const handleSelect = (item) => {
    // Navigate to the new node in the editor
    const routeName = props.entityType + 's.editor';
    router.visit(route(routeName, { [props.entityType]: props.entitySlug, child: item.slug }), {
        preserveScroll: true,
        preserveState: true
    });
};

provide('sidebarContext', {
    toggleExpand,
    isExpanded
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
</style>
