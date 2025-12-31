<template>
    <div 
        v-show="visible"
        class="block-handle fixed z-50 flex items-center gap-1 transition-all duration-200 pointer-events-none"
        :style="{ top: `${top}px`, left: `${left}px` }"
    >
        <!-- Add Button -->
        <button 
            @click.stop="$emit('add')"
            class="pointer-events-auto p-1 rounded-full bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-primary hover:border-primary transition-all scale-75 hover:scale-100"
        >
            <PlusIcon class="w-4 h-4" />
        </button>

        <!-- Drag Handle -->
        <div 
            class="pointer-events-auto cursor-grab active:cursor-grabbing p-1.5 rounded-lg bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all"
            @mousedown="$emit('drag-start', $event)"
        >
            <GripVerticalIcon class="w-4 h-4" />
        </div>
    </div>
</template>

<script setup>
import { PlusIcon, GripVerticalIcon } from 'lucide-vue-next'

defineProps({
    visible: Boolean,
    top: Number,
    left: Number
})

defineEmits(['add', 'drag-start'])
</script>

<style scoped>
.block-handle {
    margin-right: -48px; /* Position to the right of the block in RTL */
}

/* Ensure handle is visible even on narrow screens */
@media (max-width: 1024px) {
    .block-handle {
        margin-right: -24px;
    }
}
</style>
