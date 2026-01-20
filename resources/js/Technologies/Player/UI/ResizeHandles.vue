<script setup>
const emit = defineEmits(['start-resize']);
</script>

<template>
    <!-- Resize Handles Container -->
    <div class="resize-handles-layer">
        <!-- SE (Corner) -->
        <div 
             class="resize-handle absolute bottom-0 right-0 w-4 h-4 z-50 cursor-se-resize flex items-end justify-end p-[2px]"
             @mousedown.stop.prevent="(e) => emit('start-resize', e, 'se')"
        >
            <div class="w-2 h-2 border-r-2 border-b-2 border-gray-500 hover:border-white opacity-50 transition-opacity"></div>
        </div>
        <!-- E (Right Edge) -->
        <div 
            class="resize-handle-e absolute top-0 right-0 bottom-4 w-1 z-40 cursor-e-resize hover:bg-yellow-500/50 transition-colors"
            @mousedown.stop.prevent="(e) => emit('start-resize', e, 'e')"
        ></div>
        <!-- S (Bottom Edge) -->
        <div 
            class="resize-handle-s absolute left-0 bottom-0 right-4 h-1 z-40 cursor-s-resize hover:bg-yellow-500/50 transition-colors"
            @mousedown.stop.prevent="(e) => emit('start-resize', e, 's')"
        ></div>
    </div>
</template>
