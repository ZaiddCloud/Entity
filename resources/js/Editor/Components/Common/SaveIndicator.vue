<script setup>
import { computed } from 'vue'

const props = defineProps({
    isSaving: {
        type: Boolean,
        default: false
    },
    lastSaved: {
        type: Date,
        default: null
    }
})

const statusText = computed(() => {
    if (props.isSaving) return 'جاري الحفظ...'
    if (props.lastSaved) {
        return `آخر حفظ: ${props.lastSaved.toLocaleTimeString('ar-SA')}`
    }
    return 'لم يتم الحفظ'
})

const statusClass = computed(() => ({
    'save-indicator': true,
    'save-indicator--saving': props.isSaving,
    'save-indicator--saved': !props.isSaving && props.lastSaved
}))
</script>

<template>
    <div :class="statusClass">
        <span v-if="isSaving" class="save-indicator__dot"></span>
        <span class="save-indicator__text">{{ statusText }}</span>
    </div>
</template>

<style scoped>
.save-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #6b7280;
}

.save-indicator__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #f59e0b;
    animation: pulse 1.5s ease-in-out infinite;
}

.save-indicator--saved .save-indicator__text {
    color: #10b981;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>
