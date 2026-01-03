<script setup>
import { ref } from 'vue'

const props = defineProps({
    width: {
        type: String,
        default: 'auto' // or specific w- class like 'w-56'
    }
})
</script>

<template>
    <div 
        class="mega-menu absolute top-full right-0 bg-white border border-gray-200 shadow-lg rounded-lg z-50 mt-1 overflow-hidden"
        :class="[width]"
    >
        <div class="menu-grid flex flex-col gap-1 p-1">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.mega-menu {
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    min-width: 200px;
    transform-origin: top right;
    animation: menu-slide 0.2s ease-out;
}

@keyframes menu-slide {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-5px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>
