<script setup>
import { inject, computed } from 'vue';

const store = inject('readerStore');
const themeClasses = inject('themeClasses');

const progressWidth = computed(() => {
    return `${store.scrollProgress}%`;
});

const handleProgressClick = (e) => {
    const bar = e.currentTarget;
    const clickX = e.offsetX;
    const width = bar.offsetWidth;
    const percentage = (clickX / width) * 100;
    
    // Emit event or call store to scroll to percentage
    // For now, we'll just log or implement a scrollTo method in store later
    console.log(`Jump to ${percentage}%`);
};
</script>

<template>
    <div 
        class="h-1 w-full bg-black/5 dark:bg-white/5 cursor-pointer relative group transition-all hover:h-2"
        @click="handleProgressClick"
    >
        <div 
            :class="['h-full transition-all duration-300', themeClasses.accent.replace('text-', 'bg-')]"
            :style="{ width: progressWidth }"
        ></div>
        
        <!-- Tooltip or marker can be added here -->
    </div>
</template>

<style scoped>
/* Ensure the progress bar is always visible but thin */
</style>
