<script setup>
import { Play, Pause, SkipBack, SkipForward, Volume2, VolumeX, Maximize2, List, Repeat, Menu } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    isPlaying: Boolean,
    currentTime: Number,
    duration: Number,
    volume: Number,
    playbackRate: { type: Number, default: 1 },
    loopRange: { type: Object, default: () => ({ start: null, end: null, active: false }) },
    isPlaylistOpen: Boolean
});

const emit = defineEmits([
    'toggle-play', 'seek', 'update:volume', 'toggle-playlist', 'toggle-fullscreen',
    'set-playback-rate', 'toggle-loop'
]);

// V1 Parity: Support hours
const formatTime = (seconds) => {
    if (!seconds || isNaN(seconds)) return "00:00";
    const date = new Date(seconds * 1000);
    const hh = date.getUTCHours();
    const mm = date.getUTCMinutes();
    const ss = date.getUTCSeconds().toString().padStart(2, '0');
    return hh ? `${hh}:${mm.toString().padStart(2, '0')}:${ss}` : `${mm}:${ss}`;
};

const progressPercent = computed(() => {
    if (!props.duration) return 0;
    return (props.currentTime / props.duration) * 100;
});

const showSpeedMenu = ref(false);
const timelineRef = ref(null);

const handleTimelineClick = (e) => {
    if (!timelineRef.value || !props.duration) return;
    const rect = timelineRef.value.getBoundingClientRect();
    const pos = (e.clientX - rect.left) / rect.width;
    emit('seek', pos * props.duration);
};

const handleVolumeClick = (e) => {
    const rect = e.target.closest('.vol-slider').getBoundingClientRect();
    const vol = (e.clientX - rect.left) / rect.width;
    emit('update:volume', Math.max(0, Math.min(vol, 1)));
};
</script>

<template>
    <div class="footer h-14 bg-[#141414] border-t border-[#333] flex flex-col" dir="ltr">
        <!-- Timeline -->
        <div 
            ref="timelineRef"
            class="timeline h-3 w-full cursor-pointer relative group flex items-center"
            @click="handleTimelineClick"
        >
            <div class="seek-bg absolute inset-0 bg-transparent h-1 bg-[#333] top-1"></div>
            <div 
                class="progress h-1 bg-[#eab308] relative transition-all group-hover:h-1.5 top-0" 
                :style="{ width: `${progressPercent}%`, top: '4px' }"
            >
                <div class="thumb w-2.5 h-2.5 bg-white rounded-full absolute -right-1.25 -top-[3px] opacity-100 shadow"></div>
            </div>
        </div>

        <!-- Controls Row -->
        <div class="controls flex-1 flex items-center justify-between px-3 h-9 text-[#888]">
            <!-- Left: Playback Controls (PotPlayer Style) -->
            <div class="flex items-center gap-2">
                 <button class="hover:text-white" title="Previous 10s" @click="$emit('seek', currentTime - 10)">
                    <SkipBack class="w-4 h-4" />
                </button>
                <button class="hover:text-white p-1" title="Play/Pause" @click="$emit('toggle-play')">
                     <component :is="isPlaying ? Pause : Play" class="w-5 h-5 fill-current text-gray-200" />
                </button>
                <button class="hover:text-white" title="Next 10s" @click="$emit('seek', currentTime + 10)">
                    <SkipForward class="w-4 h-4" />
                </button>
            </div>

            <!-- Center: Volume & Time -->
            <div class="flex flex-1 items-center justify-center gap-4">
                 <div class="text-[11px] font-mono text-gray-400">
                     {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
                 </div>
                 
                 <!-- Custom Volume Slider -->
                 <div class="flex items-center gap-2 group cursor-pointer">
                    <button @click="$emit('update:volume', volume === 0 ? 1 : 0)">
                        <component :is="volume === 0 ? VolumeX : Volume2" class="w-3 h-3 text-gray-500 group-hover:text-white transition-colors" />
                    </button>
                    <div class="vol-slider w-16 h-1 bg-[#444] rounded overflow-hidden cursor-pointer" @click="handleVolumeClick">
                        <div class="vol-fill h-full bg-[#eab308]" :style="{ width: `${volume * 100}%` }"></div>
                    </div>
                 </div>
            </div>

            <!-- Right: Utils -->
            <div class="flex items-center gap-1 relative">
                <!-- Speed -->
                <button class="text-[10px] font-bold text-yellow-600 hover:text-yellow-400 w-auto px-1" @click="showSpeedMenu = !showSpeedMenu">
                    {{ playbackRate }}x
                </button>
                <!-- Speed Menu -->
                <div v-if="showSpeedMenu" class="absolute bottom-full right-0 mb-2 bg-[#1a1a1a] border border-[#333] rounded shadow-xl flex flex-col py-1 z-50 min-w-[60px]">
                    <button v-for="rate in [0.5, 1.0, 1.5, 2.0]" :key="rate" class="px-2 py-1 text-xs hover:bg-[#333] text-left text-gray-300" 
                        @click="$emit('set-playback-rate', rate); showSpeedMenu = false">
                        {{ rate }}x
                    </button>
                </div>

                <button class="hover:text-white" title="Repeat" @click="$emit('toggle-loop')">
                    <Repeat class="w-3 h-3" :class="{'text-yellow-500': loopRange.start !== null}" />
                </button>
                <button class="hover:text-white" title="Playlist" @click="$emit('toggle-playlist')">
                     <Menu class="w-3 h-3" :class="{'text-white': isPlaylistOpen}" />
                </button>
                <button class="hover:text-white" title="Segments">
                    <List class="w-3 h-3" />
                </button>
                <button class="hover:text-white" title="Fullscreen" @click="$emit('toggle-fullscreen')">
                    <Maximize2 class="w-3 h-3" />
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.vol-fill {
    background-color: #eab308;
}
</style>
