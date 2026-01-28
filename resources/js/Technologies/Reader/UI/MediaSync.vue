<script setup>
import { ref, watch, inject, onMounted, computed } from 'vue';

const props = defineProps({
    currentTime: {
        type: Number,
        default: 0
    },
    hierarchy: {
        type: Array,
        default: () => []
    },
    activeSlug: String
});

const emit = defineEmits(['seek']);

const store = inject('readerStore');
const themeClasses = inject('themeClasses');

// Find active segment in hierarchy based on current playback time
const activeSegmentIndex = computed(() => {
    return props.hierarchy.findIndex(node => 
        props.currentTime >= node.start_time && props.currentTime <= node.end_time
    );
});

// Auto-scroll logic to keep current segment in view
const segmentRefs = ref([]);

// 1. Watch time for auto-scroll during playback
watch(activeSegmentIndex, (newIndex) => {
    if (newIndex !== -1 && segmentRefs.value[newIndex]) {
        segmentRefs.value[newIndex].scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
});

// 2. Watch activeSlug for manual navigation (from TOC etc.)
watch(() => props.activeSlug, (newSlug) => {
    if (!newSlug) return;
    const index = props.hierarchy.findIndex(n => n.slug === newSlug);
    if (index !== -1 && segmentRefs.value[index]) {
        // Only scroll if it's not already the active time segment 
        // to avoid double scrolling or interfering with play scroll
        segmentRefs.value[index].scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
}, { immediate: true });

const formatTime = (seconds) => {
    if (!seconds) return '00:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

const handleSegmentClick = (node) => {
    if (node.slug !== props.activeSlug) {
        store.navigate(node.slug);
    }
    emit('seek', node.start_time);
};
</script>

<template>
    <div class="flex flex-col h-full overflow-hidden">
        <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-6">
            <div 
                v-for="(node, index) in hierarchy" 
                :key="node.id"
                :ref="el => segmentRefs[index] = el"
                :class="[
                    'p-6 rounded-3xl transition-all duration-500 cursor-pointer border-2 relative group',
                    activeSegmentIndex === index 
                        ? 'bg-blue-500/10 border-blue-500/30 shadow-xl shadow-blue-500/5' 
                        : 'bg-black/5 dark:bg-white/5 border-transparent hover:border-black/10 dark:hover:border-white/10'
                ]"
                @click="handleSegmentClick(node)"
            >
                <!-- Active Indicator -->
                <div 
                    v-if="activeSegmentIndex === index"
                    class="absolute -right-1 top-6 w-2 h-10 bg-blue-500 rounded-full shadow-[0_0_15px_rgba(59,130,246,0.5)]"
                ></div>

                <div class="flex items-start justify-between mb-4">
                    <span :class="['text-xs font-black tracking-tighter uppercase', activeSegmentIndex === index ? 'text-blue-500' : 'text-slate-400']">
                        مقطع {{ index + 1 }}
                    </span>
                    <span class="text-xs font-mono font-bold opacity-40 group-hover:opacity-100 transition-opacity">
                        {{ formatTime(node.start_time) }} - {{ formatTime(node.end_time) }}
                    </span>
                </div>

                <h3 class="font-bold text-lg mb-4 leading-relaxed">{{ node.title }}</h3>
                
                <p 
                    :class="['text-sm leading-8 transition-colors duration-500', activeSegmentIndex === index ? '' : 'opacity-40']"
                    v-html="node.content || 'لا يوجد محتوى متاح لهذا المقطع'"
                ></p>

                <!-- Progress inside segment -->
                <div 
                    v-if="activeSegmentIndex === index"
                    class="mt-6 h-1 w-full bg-blue-500/10 rounded-full overflow-hidden"
                >
                    <div 
                        class="h-full bg-blue-500 transition-all duration-300"
                        :style="{ width: `${((currentTime - node.start_time) / (node.end_time - node.start_time)) * 100}%` }"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 10px;
}
.theme-dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.05);
}
</style>
