<script setup>
import { Check, Search, Minus, X, Play, FileAudio } from 'lucide-vue-next';
import { ref, nextTick } from 'vue';

const props = defineProps({
    segments: { type: Array, default: () => [] },
    activeSlug: String
});

const emit = defineEmits(['select', 'close', 'add', 'delete', 'update']); // Added 'update'

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
    <div class="playlist w-[280px] bg-[#111] border-l border-[#333] flex flex-col h-full overflow-hidden text-[#aaa] font-sans" dir="ltr">
        <!-- Header -->
        <div class="pl-header h-[30px] bg-[#1f1f1f] flex items-center justify-between px-2 text-[11px] border-b border-[#2a2a2a]">
            <span class="font-bold text-[#888]">PLAYLIST</span>
            <div class="flex gap-2">
                <Minus class="w-[10px] h-[10px] cursor-pointer hover:text-white" />
                <X class="w-[10px] h-[10px] cursor-pointer hover:text-red-500" @click="$emit('close')" />
            </div>
        </div>

        <!-- Tabs -->
        <div class="pl-tabs flex bg-[#181818] text-[11px]">
             <div class="pl-tab px-3 py-1.5 cursor-pointer border-t-2 bg-[#222] border-t-yellow-500 text-white font-medium">Default</div>
             <div class="pl-tab px-3 py-1.5 text-gray-500 cursor-pointer border-t-2 border-transparent hover:text-gray-300">History</div>
        </div>
        
        <!-- Content -->
        <div class="flex-1 overflow-y-auto custom-scrollbar py-1">
            <div 
                v-for="(seg, i) in (segments || [])" 
                :key="seg.slug || i"
                class="item px-2 py-1 border-b border-[#1a1a1a] hover:bg-[#222] cursor-pointer group flex items-start gap-2"
                :class="{'bg-[#2a2a2a] text-yellow-500': seg.slug === activeSlug}"
                @click="$emit('select', seg)"
            >
                <div>
                     <Play v-if="seg.slug === activeSlug" class="w-[10px] h-[10px] mt-1 fill-current text-yellow-500" />
                     <FileAudio v-else class="w-[10px] h-[10px] mt-1 text-gray-600 group-hover:text-gray-400" />
                </div>
                
                <div class="flex flex-col flex-1 min-w-0">
                    <!-- Editing Mode -->
                    <input 
                        v-if="editingSlug === seg.slug"
                        ref="titleInput"
                        v-model="editingTitle"
                        @blur="saveEditing"
                        @keyup.enter="saveEditing"
                        @keyup.esc="cancelEditing"
                        @click.stop
                        class="text-[11px] bg-[#333] text-white border-none py-0 px-1 rounded w-full focus:ring-1 focus:ring-yellow-500"
                    />

                    <!-- Display Mode -->
                    <span 
                        v-else
                        class="text-[11px] truncate group-hover:text-white"
                        :class="{'text-yellow-500': seg.slug === activeSlug, 'text-[#ccc]': seg.slug !== activeSlug}"
                        @dblclick.stop="startEditing(seg)"
                        title="Double click to rename"
                    >
                        {{ seg.label || seg.title }}
                    </span>

                    <span class="text-[9px] text-[#555] font-mono mt-0.5">
                        {{ formatTime((seg.end || 0) - (seg.start || 0)) }}
                    </span>
                </div>
            </div>
            
            <div v-if="(segments || []).length === 0" class="p-4 text-center text-xs text-[#555]">
                No segments available.
            </div>
        </div>

        <!-- Footer -->
        <div class="pl-footer h-[36px] bg-[#1f1f1f] flex items-center px-2 gap-1 border-t border-[#2a2a2a]">
             <button @click="$emit('add')" class="text-[9px] px-2 py-1 bg-[#333] text-[#ccc] rounded hover:bg-[#444] transition-colors">ADD</button>
             <button @click="startEditingActive" class="text-[9px] px-2 py-1 bg-[#333] text-[#ccc] rounded hover:bg-[#444] transition-colors">EDIT</button>
             <button @click="$emit('delete')" class="text-[9px] px-2 py-1 bg-[#333] text-[#ccc] rounded hover:bg-[#444] transition-colors">DEL</button>
             <div class="flex-1"></div>
             <Search class="w-3 h-3 text-gray-500 hover:text-white cursor-pointer" />
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #111;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #333;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
