<script setup>
defineProps({
    label: {
        type: String,
        required: true
    },
    icon: {
        type: String,
        default: ''
    },
    command: {
        type: String,
        default: ''
    },
    shortcut: {
        type: String,
        default: ''
    },
    active: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    danger: {
        type: Boolean,
        default: false
    },
    highlight: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['click'])
</script>

<template>
    <button 
        class="menu-btn w-full flex items-center justify-between px-3 py-1.5 text-xs rounded transition-colors"
        :class="{
            'hover:bg-gray-100 text-gray-700': !active && !danger && !highlight,
            'bg-blue-50 text-blue-600 font-medium': active,
            'text-red-500 hover:bg-red-50': danger,
            'text-amber-700 font-bold': highlight,
            'opacity-50 cursor-not-allowed': disabled
        }"
        :disabled="disabled"
        @click="emit('click')"
        :title="label"
    >
        <div class="flex items-center gap-2">
            <span v-if="icon" class="text-sm">{{ icon }}</span>
            <span>{{ label }}</span>
        </div>
        <span v-if="shortcut" class="text-[9px] text-gray-400 font-sans tracking-wide">{{ shortcut }}</span>
    </button>
</template>
