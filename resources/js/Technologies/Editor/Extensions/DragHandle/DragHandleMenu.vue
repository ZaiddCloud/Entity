<script setup>
defineProps({
    editor: {
        type: Object,
        required: true,
    }
})
</script>

<template>
    <div class="drag-handle" draggable="true" data-drag-handle>
        <i class="ri-draggable text-gray-400 hover:text-gray-600"></i>
    </div>
</template>

<style scoped>
.drag-handle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    cursor: grab;
    border-radius: 0.25rem;
    transition: background-color 0.2s;
}

.drag-handle:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.drag-handle:active {
    cursor: grabbing;
}
</style>
