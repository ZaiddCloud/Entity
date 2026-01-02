<script setup>
defineProps({
    filename: {
        type: String,
        default: 'مستند جديد'
    }
})
</script>

<template>
    <div class="filename-badge">
        <span class="filename-badge__icon">📄</span>
        <span class="filename-badge__text">{{ filename }}</span>
    </div>
</template>

<style scoped>
.filename-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    color: #374151;
}

.filename-badge__icon {
    font-size: 14px;
}

.filename-badge__text {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
