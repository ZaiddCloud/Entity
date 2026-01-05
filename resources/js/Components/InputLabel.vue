<script setup>
defineProps({
    value: {
        type: String,
    },
    optional: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <label class="block font-bold text-sm text-gray-700 dark:text-gray-300 mb-2">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
        <span v-if="optional" class="text-xs text-gray-400 font-normal mr-1">(اختياري)</span>
    </label>
</template>
