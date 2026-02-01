<script setup>
import { Check, Search, Minus, X, Play, FileAudio, Bookmark, ChevronDown, ChevronRight } from 'lucide-vue-next';
import { ref, nextTick, computed, watch, onMounted } from 'vue';

const props = defineProps({
    title: String,
    segments: { type: Array, default: () => [] },
    activeSlug: String
});

const emit = defineEmits(['select', 'close', 'add', 'delete', 'update']); // Added 'update'

const isSegmentsExpanded = ref(true);
const scrollContainer = ref(null);
const rootElement = ref(null);
const segmentElements = ref([]);

const isArabic = (text) => {
    if (!text) return true;
    const arabicPattern = /[\u0600-\u06FF]/;
    return arabicPattern.test(text);
};

const titleDirection = computed(() => isArabic(props.title) ? 'rtl' : 'ltr');

const scrollToActive = () => {
    nextTick(() => {
        let target = null;
        if (!props.activeSlug) {
            target = rootElement.value;
        } else {
            // Find the active segment element
            const activeIndex = props.segments.findIndex(s => s.slug === props.activeSlug);
            if (activeIndex !== -1 && segmentElements.value[activeIndex]) {
                target = segmentElements.value[activeIndex];
            }
        }

        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest'
            });
        }
    });
};

watch(() => props.activeSlug, () => {
    scrollToActive();
});

onMounted(() => {
    scrollToActive();
});

// --- Editing Logic ---
const editingSlug = ref(null);
const editingTitle = ref('');
const titleInput = ref(null);

const startEditing = (seg) => {
    editingSlug.value = seg.slug;
    editingTitle.value = seg.label || seg.title || '';
    nextTick(() => {
        if (titleInput.value && titleInput.value[0]) {
            titleInput.value[0].focus();
        }
    });
};

const cancelEditing = () => {
    editingSlug.value = null;
    editingTitle.value = '';
};

const saveEditing = () => {
    if (!editingSlug.value) return;
    
    // Find segment
    const seg = props.segments.find(s => s.slug === editingSlug.value);
    if (seg && editingTitle.value.trim() !== '') {
        // Optimistic update (optional, but good for UX)
        // emit update event
        emit('update', { ...seg, title: editingTitle.value });
    }
    cancelEditing();
};

const startEditingActive = () => {
    if (!props.activeSlug) return;
    const seg = props.segments.find(s => s.slug === props.activeSlug);
    if (seg) startEditing(seg);
};

// V1 Parity: Support hours
const formatTime = (seconds) => {
    if (!seconds || isNaN(seconds)) return "00:00";
    const date = new Date(seconds * 1000);
    const hh = date.getUTCHours();
    const mm = date.getUTCMinutes();
    const ss = date.getUTCSeconds().toString().padStart(2, '0');
    return hh ? `${hh}:${mm.toString().padStart(2, '0')}:${ss}` : `${mm}:${ss}`;
};
</script>

<template>
    <div class="playlist w-[180px] bg-[#111]/95 backdrop-blur-xl border-l border-[#333] flex flex-col h-full overflow-hidden text-[#aaa] font-sans shadow-2xl" dir="ltr">
        <!-- Minimal Window Controls (Transparent) -->
        <div class="pl-header h-[20px] flex items-center justify-end px-2 shrink-0 gap-2 mt-1">
            <Minus class="w-3 h-3 cursor-pointer hover:text-white opacity-40 hover:opacity-100 transition-opacity" />
            <X class="w-3 h-3 cursor-pointer hover:text-red-500 opacity-40 hover:opacity-100 transition-opacity" @click="$emit('close')" />
        </div>

        <!-- Fixed Media Root (Always Phosphorescent Green + Compact Toggle) -->
        <div 
            ref="rootElement"
            class="px-3 py-1 flex items-center justify-between cursor-pointer transition-all text-lime-400 font-bold group/root border-b border-white/[0.03]"
            :class="[isArabic(title) ? 'flex-row-reverse' : 'flex-row']"
            @click="isSegmentsExpanded = !isSegmentsExpanded"
        >
            <span class="text-[10px] truncate tracking-tight uppercase flex-1" :class="[isArabic(title) ? 'text-right' : 'text-left']">{{ title || 'Original Full View' }}</span>
            
            <!-- Toggle Icon (Smart Position) -->
            <div class="shrink-0 flex items-center justify-center w-4 h-4 hover:bg-lime-500/10 rounded transition-colors ml-1">
                <component 
                    :is="isSegmentsExpanded ? ChevronDown : (isArabic(title) ? ChevronRight : ChevronRight)" 
                    class="w-3 h-3 text-lime-400"
                    :class="[!isSegmentsExpanded && isArabic(title) ? 'rotate-180' : '']"
                />
            </div>
        </div>

        <!-- Scrollable Tree Content -->
        <div ref="scrollContainer" class="flex-1 overflow-y-auto custom-scrollbar pt-0 pb-2">
            <!-- Level 1: Segments (Phosphorescent Blue | Green when Active) -->
            <div v-if="isSegmentsExpanded" class="flex flex-col relative ml-3 pl-0.5">
                <div 
                    v-for="(seg, i) in (segments || [])" 
                    :key="seg.slug || i"
                    ref="segmentElements"
                    class="item px-2 py-0.5 mb-0.5 rounded-l-md cursor-pointer group flex items-center relative transition-all"
                    :class="[seg.slug === activeSlug ? 'text-lime-400 font-bold' : 'text-blue-400 font-bold hover:text-blue-300']"
                    @click="$emit('select', seg)"
                >
                    <div class="flex items-center min-w-0 gap-1.5 overflow-hidden" :class="[isArabic(seg.label || seg.title) ? 'flex-row-reverse ml-auto' : 'flex-row mr-auto']">
                        <!-- Label Part (Leads for the eye) -->
                         <div class="min-w-0 max-w-[120px]">
                            <input 
                                v-if="editingSlug === seg.slug"
                                ref="titleInput"
                                v-model="editingTitle"
                                @blur="saveEditing"
                                @keyup.enter="saveEditing"
                                @keyup.esc="cancelEditing"
                                @click.stop
                                class="text-[10px] bg-[#333] text-white border-none py-0 px-1 rounded w-full focus:ring-1 focus:ring-lime-500 font-sans"
                            />
                            <span 
                                v-else
                                class="text-[10px] truncate block transition-colors"
                                :class="[isArabic(seg.label || seg.title) ? 'text-right' : 'text-left']"
                                @dblclick.stop="startEditing(seg)"
                            >
                                {{ seg.label || seg.title }}
                            </span>
                         </div>

                         <!-- Time Part (Follows immediately) -->
                        <span class="text-[8px] shrink-0 font-mono" :class="[seg.slug === activeSlug ? 'text-lime-400' : 'text-blue-400']">
                            {{ formatTime(seg.start || 0) }}
                        </span>
                    </div>
                </div>

                <div v-if="(segments || []).length === 0" class="p-6 text-center text-[9px] text-[#444] italic font-sans border-t border-white/[0.03] mt-1">
                    No segments
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
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.1);
}
</style>
