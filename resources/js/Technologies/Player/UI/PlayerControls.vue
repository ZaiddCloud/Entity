<script setup>
import { Play, Pause, SkipBack, SkipForward, Volume2, VolumeX, Maximize2, List, Repeat, Menu, Plus, X } from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    isPlaying: Boolean,
    currentTime: Number,
    duration: Number,
    volume: Number,
    playbackRate: { type: Number, default: 1 },
    loopRange: { type: Object, default: () => ({ start: null, end: null, active: false }) },
    isPlaylistOpen: Boolean,
    segments: { type: Array, default: () => [] },
    activeSegmentSlug: String
});

const emit = defineEmits([
    'toggle-play', 'seek', 'update:volume', 'toggle-playlist', 'toggle-fullscreen',
    'set-playback-rate', 'toggle-loop', 'add-segment', 'delete-segment', 'segment-change'
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
    const seekTime = pos * props.duration;
    
    emit('seek', seekTime);
};

const isDraggingVolume = ref(false);

const updateVolumeFromEvent = (e, slider) => {
    const rect = slider.getBoundingClientRect();
    const vol = (e.clientX - rect.left) / rect.width;
    emit('update:volume', Math.max(0, Math.min(vol, 1)));
};

const handleVolumeClick = (e) => {
    const slider = e.target.closest('.vol-slider');
    updateVolumeFromEvent(e, slider);
};

const startVolumeDrag = (e) => {
    isDraggingVolume.value = true;
    const slider = e.target.closest('.vol-slider');
    
    const onMouseMove = (moveEvent) => {
        updateVolumeFromEvent(moveEvent, slider);
    };
    
    const onMouseUp = () => {
        isDraggingVolume.value = false;
        window.removeEventListener('mousemove', onMouseMove);
        window.removeEventListener('mouseup', onMouseUp);
    };
    
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', onMouseUp);
    
    updateVolumeFromEvent(e, slider);
};

const isVolumeLocked = ref(false);
const volumeRef = ref(null);
const lastVolume = ref(1);
const showSegmentsMenu = ref(false);

const toggleVolumeLock = (e) => {
    e.stopPropagation(); // Prevent bubbling to window listener
    isVolumeLocked.value = !isVolumeLocked.value;
};

const toggleMute = () => {
    if (props.volume > 0) {
        lastVolume.value = props.volume;
        emit('update:volume', 0);
    } else {
        emit('update:volume', lastVolume.value || 0.5); // Restore or default to 50%
    }
};

const handleClickOutside = (e) => {
    if (isVolumeLocked.value && volumeRef.value && !volumeRef.value.contains(e.target)) {
        isVolumeLocked.value = false;
    }
    // Simple click outside for segments menu
    if (showSegmentsMenu.value && !e.target.closest('.segments-container')) {
        showSegmentsMenu.value = false;
    }
};

const addQuickSegment = () => {
    const newSeg = {
        slug: `seg-${Date.now()}`,
        start: props.currentTime,
        end: props.duration,
        label: `Segment at ${formatTime(props.currentTime)}`
    };
    emit('add-segment', newSeg);
};

// Volume Wheel Control
const handleVolumeWheel = (e) => {
    const step = 0.05;
    const newVol = e.deltaY < 0 ? props.volume + step : props.volume - step;
    emit('update:volume', Math.max(0, Math.min(newVol, 1)));
};

// Global Keyboard Shortcuts
const handleKeydown = (e) => {
    if (['ArrowUp', 'ArrowDown'].includes(e.key)) {
        // Ignore if user is typing in an input
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
        
        e.preventDefault();
        const step = 0.05;
        const direction = e.key === 'ArrowUp' ? 1 : -1;
        const newVol = props.volume + (step * direction);
        emit('update:volume', Math.max(0, Math.min(newVol, 1)));
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    window.addEventListener('mousedown', handleClickOutside); // Changed from click to mousedown
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    window.removeEventListener('mousedown', handleClickOutside);
});

</script>

<template>
    <div class="footer h-14 bg-transparent flex flex-col" dir="ltr">
        <!-- Timeline -->
        <div 
            ref="timelineRef"
            class="timeline h-5 w-full cursor-pointer relative group flex items-center transition-all duration-300 px-0"
            @click="handleTimelineClick"
        >
            <!-- Glass Seek Background -->
            <div class="seek-bg absolute inset-x-0 bg-white/10 backdrop-blur-[2px] h-1.5 rounded-full top-1/2 -translate-y-1/2 transition-all group-hover:h-2 group-hover:bg-white/15"></div>
            
            <!-- Segment Markers -->
            <div 
                v-for="seg in (segments || [])" 
                :key="seg.slug"
                class="absolute h-1.5 w-[2px] bg-white/40 z-10 top-1/2 -translate-y-1/2 group-hover:h-3 group-hover:bg-yellow-500/80 transition-all pointer-events-none"
                :style="{ left: `${duration > 0 ? (seg.start / duration) * 100 : 0}%` }"
                :title="seg.label || seg.title"
            ></div>
            
            <!-- Golden Gradient Progress with Glow -->
            <div 
                class="progress h-1.5 rounded-full relative transition-all duration-300 group-hover:h-2 top-0" 
                :style="{ 
                    width: `${progressPercent}%`, 
                    background: 'linear-gradient(90deg, #ca8a04 0%, #eab308 100%)',
                    boxShadow: '0 0 15px rgba(234, 179, 8, 0.4)'
                }"
            >
                <!-- Interactive Thumb -->
                <div class="thumb w-3 h-3 bg-white rounded-full absolute -right-1.5 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-300 scale-50 group-hover:scale-100 shadow-[0_0_10px_rgba(255,255,255,0.5)] border-2 border-yellow-600"></div>
            </div>
        </div>

        <!-- Controls Row -->
        <div class="grid grid-cols-3 items-center h-9 px-3 text-[#888]">
            <!-- LEFT: Time & Volume -->
            <div class="flex items-center gap-4 justify-start">
                 <div class="text-[10px] font-mono text-gray-400">
                     {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
                 </div>

                 <!-- Volume Control Group -->
                 <div class="flex items-center gap-0.5 ml-2">
                     <!-- Dedicated Mute Toggle -->
                     <button 
                        @click="toggleMute" 
                        class="text-gray-500 hover:text-white transition-colors p-0.5"
                        :title="volume === 0 ? 'Unmute' : 'Mute'"
                     >
                        <component :is="volume === 0 ? Volume2 : VolumeX" class="w-3 h-3" />
                     </button>
                     
                     <!-- Center Overlay Dynamic Volume -->
                     <div ref="volumeRef" class="relative group/vol flex items-center justify-center w-5 h-full" @wheel.prevent="handleVolumeWheel">
                        <!-- Icon (Visible, fades slightly on hover or when locked) -->
                        <button @click="toggleVolumeLock($event)" class="z-10 transition-opacity duration-300" :class="{'opacity-25': isVolumeLocked, 'group-hover/vol:opacity-25': !isVolumeLocked}">
                            <component :is="volume === 0 ? VolumeX : Volume2" class="w-3 h-3 text-gray-500 group-hover/vol:text-white" :class="{'text-white': isVolumeLocked}" />
                        </button>
                        
                        <!-- Slider Overlay (Centers on icon, Expands Outwards) -->
                        <div 
                            class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 h-8 bg-[#141414] border border-[#333] rounded-full flex items-center justify-center gap-2 transition-all duration-300 ease-out overflow-hidden shadow-2xl z-20"
                            :class="[
                                isVolumeLocked || isDraggingVolume ? 'w-24 opacity-100' : 'w-0 opacity-0 group-hover/vol:w-24 group-hover/vol:opacity-100',
                                {'pointer-events-none': !isVolumeLocked && !isDraggingVolume}
                            ]"
                        >
                            <!-- Slider Bar -->
                            <div 
                                class="vol-slider w-12 h-1 bg-[#333] rounded-full cursor-pointer relative group/slider"
                                @mousedown.prevent="startVolumeDrag"
                                @click="handleVolumeClick"
                            >
                                 <!-- Hit Area Boost -->
                                 <div class="absolute -inset-y-2 -inset-x-0 z-0"></div>
                                <div class="vol-fill h-full bg-[#eab308] relative z-10" :style="{ width: `${volume * 100}%` }"></div>
                            </div>
                            
                            <!-- Numeric Percent -->
                            <span class="text-[9px] font-mono text-gray-300 min-w-[20px] text-right select-none">{{ Math.round(volume * 100) }}</span>
                        </div>
                     </div>
                 </div>
            </div>

            <!-- CENTER: Minimal Playback Controls -->
            <div class="flex items-center justify-center gap-3">
                 <button class="hover:text-white transition-transform active:scale-90" title="Previous 10s" @click="$emit('seek', currentTime - 10)">
                    <SkipBack class="w-2.5 h-2.5" />
                </button>
                <button class="hover:text-white p-1 transition-transform active:scale-95" title="Play/Pause" @click="$emit('toggle-play')">
                     <component :is="isPlaying ? Pause : Play" class="w-3 h-3 fill-current text-gray-200" />
                </button>
                <button class="hover:text-white transition-transform active:scale-90" title="Next 10s" @click="$emit('seek', currentTime + 10)">
                    <SkipForward class="w-2.5 h-2.5" />
                </button>
            </div>

            <!-- RIGHT: Utils -->
            <div class="flex items-center gap-2 justify-end relative">
                <!-- Speed -->
                <button class="text-[9px] font-bold text-gray-500 hover:text-white w-auto px-1 transition-colors" @click="showSpeedMenu = !showSpeedMenu">
                    {{ playbackRate }}x
                </button>
                
                <!-- Animated Speed Menu -->
                <transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="transform scale-95 opacity-0 translate-y-2"
                    enter-to-class="transform scale-100 opacity-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="transform scale-100 opacity-100 translate-y-0"
                    leave-to-class="transform scale-95 opacity-0 translate-y-2"
                >
                    <div v-if="showSpeedMenu" class="absolute bottom-full right-8 mb-2 bg-[#1a1a1a] border border-[#333] rounded shadow-xl flex flex-col py-1 z-50 min-w-[70px] origin-bottom-right">
                         <div class="text-[10px] text-gray-500 font-bold px-2 py-1 border-b border-[#333]">Speed</div>
                        <button v-for="rate in [0.25, 0.5, 0.75, 1.0, 1.25, 1.5, 2.0]" :key="rate" 
                            class="px-2 py-1.5 text-xs hover:bg-[#333] text-left transition-colors flex justify-between items-center" 
                            :class="{'text-yellow-500 font-bold': rate === playbackRate, 'text-gray-300': rate !== playbackRate}"
                            @click="$emit('set-playback-rate', rate); showSpeedMenu = false">
                            {{ rate === 1.0 ? 'Normal' : rate + 'x' }}
                            <span v-if="rate === playbackRate" class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                        </button>
                    </div>
                </transition>

                <button class="hover:text-white transition-transform active:scale-95" title="Repeat" @click="$emit('toggle-loop')">
                    <Repeat class="w-2.5 h-2.5" :class="{'text-yellow-500': loopRange.start !== null}" />
                </button>
                <button class="hover:text-white transition-transform active:scale-95" title="Playlist" @click="$emit('toggle-playlist')">
                     <Menu class="w-2.5 h-2.5" :class="{'text-white': isPlaylistOpen}" />
                </button>
                <div class="relative segments-container flex items-center">
                    <button class="hover:text-white transition-transform active:scale-95" title="Segments" @click="showSegmentsMenu = !showSegmentsMenu">
                        <List class="w-2.5 h-2.5" :class="{'text-yellow-500': showSegmentsMenu}" />
                    </button>

                    <!-- Quick Chapters Menu -->
                    <transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="transform scale-95 opacity-0 translate-y-2"
                        enter-to-class="transform scale-100 opacity-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="transform scale-100 opacity-100 translate-y-0"
                        leave-to-class="transform scale-95 opacity-0 translate-y-2"
                    >
                        <div v-if="showSegmentsMenu" class="absolute bottom-full right-0 mb-3 bg-[#111111]/90 backdrop-blur-xl border border-white/10 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.6)] flex flex-col py-1 z-50 min-w-[200px] origin-bottom-right overflow-hidden">
                             <div class="text-[10px] text-gray-400 font-bold px-3 py-2 border-b border-white/5 flex justify-between items-center bg-white/5">
                                <span class="tracking-widest uppercase opacity-70">Chapters</span>
                                <button @click="addQuickSegment" class="hover:text-yellow-500 transition-all hover:scale-110 active:scale-95 flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-yellow-500/10 text-yellow-500/80 border border-yellow-500/20" title="Add segment at current time">
                                    <Plus class="w-2.5 h-2.5" />
                                    <span class="text-[9px]">ADD</span>
                                </button>
                             </div>
                             <div class="max-h-[250px] overflow-y-auto custom-scrollbar">
                                <div v-for="seg in (segments || [])" :key="seg.slug" 
                                    class="px-3 py-2.5 text-[11px] hover:bg-white/5 transition-all flex justify-between items-center group/item border-b border-white/[0.02] last:border-0"
                                    :class="{'bg-yellow-500/5 !border-l-2 !border-l-yellow-500': seg.slug === activeSegmentSlug, 'text-gray-300': seg.slug !== activeSegmentSlug}"
                                >
                                    <div class="truncate flex-1 cursor-pointer pr-2 flex flex-col gap-0.5" @click="$emit('segment-change', seg); $emit('seek', seg.start); showSegmentsMenu = false">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-[9px] px-1 rounded bg-white/5 text-gray-500 group-hover/item:text-yellow-500/70 group-hover/item:bg-yellow-500/5 transition-colors">{{ formatTime(seg.start) }}</span>
                                            <span class="truncate group-hover/item:text-white transition-colors">{{ seg.label || seg.title }}</span>
                                        </div>
                                    </div>
                                    <button @click="$emit('delete-segment', seg.slug)" class="opacity-0 group-hover/item:opacity-100 p-1.5 hover:bg-red-500/10 hover:text-red-500 rounded-lg transition-all text-gray-600">
                                        <X class="w-3 h-3" />
                                    </button>
                                </div>
                                <div v-if="(segments || []).length === 0" class="p-8 text-center flex flex-col items-center gap-2">
                                    <List class="w-6 h-6 opacity-10 text-white" />
                                    <span class="text-[10px] text-gray-600 italic">No chapters defined</span>
                                </div>
                             </div>
                        </div>
                    </transition>
                </div>
                <button class="hover:text-white transition-transform active:scale-95" title="Fullscreen" @click="$emit('toggle-fullscreen')">
                    <Maximize2 class="w-2.5 h-2.5" />
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
