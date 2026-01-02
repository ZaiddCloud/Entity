<script setup>
import { computed } from 'vue'

const props = defineProps({
    icon: String,
    label: String,
    shortcut: String,
    active: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['click'])

const buttonClass = computed(() => ({
    'toolbar-button': true,
    'toolbar-button--active': props.active,
    'toolbar-button--disabled': props.disabled
}))
</script>

<template>
    <button 
        :class="buttonClass"
        :disabled="disabled"
        :title="`${label}${shortcut ? ' (' + shortcut + ')' : ''}`"
        @click="emit('click')"
    >
        <span class="toolbar-button__icon">{{ icon }}</span>
    </button>
</template>

<style scoped>
.toolbar-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 4px;
    border: 1px solid transparent;
    background: transparent;
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 16px;
}

.toolbar-button:hover:not(.toolbar-button--disabled) {
    background: #f3f4f6;
    border-color: #e5e7eb;
}

.toolbar-button--active {
    background: #dbeafe;
    border-color: #93c5fd;
}

.toolbar-button--disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.toolbar-button__icon {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
