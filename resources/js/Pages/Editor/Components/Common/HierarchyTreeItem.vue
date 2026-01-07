<template>
    <div class="mb-1">
        <div 
            @click="handleClick"
            class="flex items-center group cursor-pointer py-2 px-3 rounded-xl transition-all duration-200"
            :class="[
                isSelected ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100 shadow-sm' : 'hover:bg-slate-50 text-slate-600',
                isChildItem ? 'mr-4' : ''
            ]"
        >
            <!-- Toggle Icon -->
            <div 
                v-if="hasChildren" 
                @click.stop="toggle"
                class="w-5 h-5 flex items-center justify-center rounded hover:bg-slate-200 transition-colors ml-1"
                :class="{ 'rotate-0': !isExpanded, '-rotate-90': isExpanded }"
            >
                <svg class="w-2.5 h-2.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
            </div>
            <div v-else class="w-5 ml-1"></div>

            <!-- Icon by Type -->
            <span class="text-xs ml-2 opacity-60">
                <template v-if="item?.type === 'chapter'">📂</template>
                <template v-else-if="item?.type === 'page'">📄</template>
                <template v-else-if="item?.type === 'folio'">🖼️</template>
                <template v-else>🔸</template>
            </span>

            <span class="text-[11px] truncate flex-1">{{ item?.title }}</span>
        </div>

        <!-- Recursive Children -->
        <div v-if="isExpanded && hasChildren" class="mr-2">
            <HierarchyTreeItem 
                v-for="child in children" 
                :key="child._id"
                :item="child"
                :all-items="allItems"
                :selected-slug="selectedSlug"
                :is-child-item="true"
                @select="$emit('select', $event)"
            />
        </div>
    </div>
</template>

<script setup>
import { computed, inject } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    allItems: {
        type: Array,
        required: true
    },
    selectedSlug: String,
    isChildItem: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['select']);

const context = inject('sidebarContext');

const isExpanded = computed(() => context.isExpanded(props.item._id));
const isSelected = computed(() => props.item.slug === props.selectedSlug);

const children = computed(() => {
    return props.allItems.filter(i => i.parent_id === props.item._id);
});

const hasChildren = computed(() => children.value.length > 0);

const toggle = () => {
    context.toggleExpand(props.item._id);
};

const handleClick = () => {
    if (!isSelected.value) {
        emit('select', props.item);
    }
};
</script>
