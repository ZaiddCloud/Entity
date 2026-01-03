<script setup>
const props = defineProps({
    isPinned: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['pin', 'close', 'minimize', 'maximize'])
</script>

<template>
    <div class="flex items-center gap-1">
        <!-- Pin Button -->
        <button 
            class="w-6 h-6 flex items-center justify-center rounded transition-colors"
            :class="isPinned ? 'text-blue-600 bg-blue-50' : 'text-gray-400 hover:bg-gray-100'"
            @click="emit('pin')"
            title="تثبيت الشريط"
        >
            <svg class="w-3 h-3 transform rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
        </button>

        <div class="h-4 w-px bg-gray-300 mx-1"></div>

        <!-- System Controls -->
        <button class="w-6 h-6 flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 rounded" title="تصغير">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
            </svg>
        </button>
        <button class="w-6 h-6 flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 rounded" title="تكبير">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
            </svg>
        </button>
        <button class="w-6 h-6 flex items-center justify-center text-gray-400 hover:bg-red-500 hover:text-white rounded transition-colors" title="إغلاق">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</template>
