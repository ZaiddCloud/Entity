<script setup>
import { Music } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    src: String,
    poster: String,
    type: String, // 'audio' | 'video'
    isPlaying: Boolean,
    title: String,
    currentTime: Number,
    duration: Number
});

const emit = defineEmits(['click']);

// V1 Parity: Support hours
const formatTime = (seconds) => {
    if (!seconds || isNaN(seconds)) return "00:00";
    const date = new Date(seconds * 1000);
    const hh = date.getUTCHours();
    const mm = date.getUTCMinutes();
    const ss = date.getUTCSeconds().toString().padStart(2, '0');
    return hh ? `${hh}:${mm.toString().padStart(2, '0')}:${ss}` : `${mm}:${ss}`;
};

const mediaRef = ref(null);
defineExpose({ mediaRef });
</script>

<template>
    <div 
        class="stage bg-black flex-1 relative flex items-center justify-center overflow-hidden cursor-pointer min-h-0"
        @click="$emit('click')"
    >
        <!-- VIDEO ELEMENT -->
        <video 
            v-if="type === 'video'"
            ref="mediaRef"
            :src="src"
            :poster="poster"
            class="absolute inset-0 w-full h-full object-contain"
            style="z-index: 1;"
        ></video>
        
        <audio 
            v-else
            ref="mediaRef"
            :src="src"
            class="hidden"
        ></audio>

        <!-- Restoration: Album Art Background (Fade with mask) -->
        <div 
            v-if="type === 'audio' && poster"
            class="album-art absolute top-0 left-0 w-full h-[60%] opacity-60 bg-cover bg-center z-0"
            :style="{
                backgroundImage: `url(${poster})`,
                maskImage: 'linear-gradient(to bottom, black 50%, transparent 100%)',
                webkitMaskImage: 'linear-gradient(to bottom, black 50%, transparent 100%)'
            }"
        ></div>

        <!-- Restoration: Info Overlay -->
        <div class="info-overlay absolute bottom-5 left-5 z-10 w-[calc(100%-40px)] pointer-events-none" dir="ltr">
            <div class="flex items-end">
                <div class="big-time text-5xl font-bold text-white shadow-black drop-shadow-md leading-none mb-1">
                    {{ formatTime(currentTime) }}
                </div>
                <div class="total-time-small pb-2 text-xl text-gray-400 opacity-60 ml-2">
                    / {{ formatTime(duration) }}
                </div>
            </div>
            <div class="track-details flex items-center gap-2 mt-2 text-yellow-500 text-[13px] drop-shadow-md">
                <Music class="w-3 h-3" />
                <span class="font-bold text-white">{{ title }}</span>
            </div>
            <div class="meta flex items-center gap-3 mt-1 text-[10px] text-gray-500 font-mono">
                <span>MP3</span>
                <span>Stereo</span>
                <span>44.1kHz</span>
            </div>
        </div>

        <!-- Restoration: Audio Visualizer (If Audio) -->
        <div v-if="type === 'audio'" class="absolute bottom-5 right-5 flex items-end gap-[2px] h-10 opacity-80 z-20 pointer-events-none">
            <div 
                v-for="i in 12" 
                :key="i"
                class="w-[3px] bg-blue-500 transition-all duration-100"
                :style="{ 
                    height: isPlaying ? `${Math.random() * 100}%` : '5px'
                }"
            ></div>
        </div>
    </div>
</template>
