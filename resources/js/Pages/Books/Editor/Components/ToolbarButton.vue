<template>
    <button 
        type="button"
        @click="$emit('click')"
        :class="[
            'p-2 rounded-lg transition-all duration-200 group relative',
            active 
                ? 'bg-primary/10 text-primary' 
                : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-slate-200'
        ]"
    >
        <component :is="icon" class="w-4 h-4" />
        
        <!-- Tooltip -->
        <span v-if="tooltip" class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50">
            {{ tooltip }}
        </span>
    </button>
</template>

<script setup>
defineProps({
    active: Boolean,
    icon: Object,
    tooltip: String
})

defineEmits(['click'])
</script>
