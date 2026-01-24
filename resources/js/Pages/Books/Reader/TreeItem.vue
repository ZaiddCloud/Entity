<template>
  <div class="mb-1">
    <div 
      class="flex items-center group cursor-pointer py-2 px-3 rounded-xl transition-all duration-200 select-none relative border border-transparent"
      :class="[
        selectedId === item.id ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-900 dark:text-amber-400 border border-amber-200/50 dark:border-amber-700/50 shadow-sm' : 'hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-600 dark:text-slate-400',
        isOpen ? 'mb-1' : ''
      ]"
      :style="{ paddingRight: (level * 16 + 12) + 'px' }"
      @click="handleClick"
    >
      <!-- Indentation Line for children -->
      <div
        v-if="level > 0"
        class="absolute right-4 top-0 bottom-0 w-px bg-slate-200/50 dark:bg-slate-700/50"
      />

      <!-- Toggle Icon -->
      <div 
        v-if="hasChildren" 
        class="ml-2 w-5 h-5 flex items-center justify-center rounded-md transition-transform duration-300"
        :class="{ 'rotate-90': isOpen }"
        @click.stop="toggleExpand(item.id)"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-3 w-3"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="3"
            d="M15 19l-7-7 7-7"
          />
        </svg>
      </div>
      <div
        v-else
        class="ml-2 w-5"
      />

      <!-- Type Icon -->
      <span 
        class="ml-2 opacity-50 text-[10px] font-bold uppercase tracking-widest hidden group-hover:inline-block bg-slate-200 dark:bg-slate-700 px-1 rounded dark:text-slate-300"
        :title="item.type"
      >
        {{ item.type_label || (item.type ? item.type[0] : '?') }}
      </span>

      <!-- Real Link -->
      <Link 
        :href="route('books.reader', [$page.props.book.slug, item.id || item._id])"
        :only="['initialContent', 'childId']"
        class="flex-1 truncate transition-colors duration-200" 
        :class="[
          headingClasses,
          { 'text-amber-700': (selectedId === item.id || selectedId === item._id) }
        ]"
        preserve-scroll
        preserve-state
        @click="handleClick"
      >
        {{ item.title }}
      </Link>

      <!-- Status Dots -->
      <div
        v-if="selectedId === item.id || selectedId === item._id"
        class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"
      />
    </div>

    <!-- Recursive Children -->
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform -translate-y-2 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform -translate-y-2 opacity-0"
    >
      <div
        v-if="isOpen && hasChildren"
        class="overflow-hidden pl-2"
      >
        <Draggable 
          v-model="localChildren" 
          item-key="id"
          group="hierarchy" 
          ghost-class="ghost"
          @end="onDragEnd"
        >
          <template #item="{ element }">
            <TreeItem 
              :item="element" 
              :all-items="allItems"
              :selected-id="selectedId"
              :level="level + 1"
              @select="$emit('select', $event)"
            />
          </template>
        </Draggable>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { computed, inject, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Draggable from 'vuedraggable';
import axios from 'axios';

const props = defineProps({
    item: Object,
    allItems: Array,
    selectedId: String,
    level: {
        type: Number,
        default: 0
    }
});

const page = usePage();
const emit = defineEmits(['select']);

// Injected State from Index.vue
const { toggleExpand, isExpanded } = inject('sidebarContext');

// Helper to get open state
const isOpen = computed(() => isExpanded(props.item.id));

// Children Logic for Sorting
const localChildren = ref([]);

// Helper for labels
const getTypeLabel = (type) => {
    const types = {
        'sub-book': 'كتاب فرعي',
        'part': 'جزء',
        'bab': 'باب',
        'chapter': 'فصل',
        'masala': 'مسألة',
        'section': 'مبحث'
    };
    return types[type] || type;
};

const headingClasses = computed(() => {
    if (props.item.type === 'masala') {
        return 'font-normal text-slate-500 dark:text-slate-400 text-xs italic';
    }

    switch (props.level) {
        case 0: return 'font-extrabold text-slate-900 dark:text-slate-100 text-base';
        case 1: return 'font-bold text-slate-800 dark:text-slate-200 text-sm';
        case 2: return 'font-semibold text-slate-700 dark:text-slate-300 text-sm';
        case 3: return 'font-medium text-slate-700 dark:text-slate-300 text-sm';
        default: return 'font-normal text-slate-600 dark:text-slate-400 text-sm';
    }
});

// Sync localChildren efficiently
const computeChildren = () => {
    if (!props.allItems) return [];
    return props.allItems
        .filter(i => String(i.parent_id) === String(props.item.id))
        .map(i => ({ ...i, type_label: getTypeLabel(i.type) }))
        .sort((a, b) => (a.order || 0) - (b.order || 0));
};

watch(() => props.allItems, () => {
    localChildren.value = computeChildren();
}, { immediate: true });

const hasChildren = computed(() => localChildren.value.length > 0);

const handleClick = (e) => {
    if (hasChildren.value) {
        toggleExpand(props.item.id);
    }
    
    if (!e.target.closest('a')) {
        router.visit(route('books.reader', [page.props.book.slug, props.item.id]), {
            preserveScroll: true,
            preserveState: true,
            only: ['initialContent', 'childId']
        });
    }

    emit('select', props.item);
};

// Handle Drag Drop
const onDragEnd = () => {
    // 1. Update orders locally based on new index
    const updates = localChildren.value.map((child, index) => ({
        id: child.id,
        order: index,
        parent_id: props.item.id
    }));

    // 2. Send to backend
    axios.post(route('api.books.contents.reorder', page.props.book.slug), {
        items: updates
    }).then(() => {
        // Optional: Show toast
        console.log('Order updated');
    }).catch(err => {
        console.error('Failed to update order', err);
        // Revert on failure if needed
    });
};
</script>
