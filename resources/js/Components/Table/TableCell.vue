<script setup>
defineProps({
    wrap: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <td 
        :class="[
            'px-8 py-6 text-sm',
            wrap ? '' : 'whitespace-nowrap'
        ]"
    >
        <slot />
    </td>
</template>
