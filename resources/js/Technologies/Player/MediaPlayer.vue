<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useMedia } from './Composables/useMedia';
import { 
    PlayIcon, 
    PauseIcon, 
    SpeakerWaveIcon, 
    SpeakerXMarkIcon, 
    ArrowsPointingOutIcon, 
    ArrowPathIcon, 
    ForwardIcon, 
    BackwardIcon 
} from '@heroicons/vue/24/solid';

// --- Props & Emits ---
const props = defineProps({
    src: { type: String, required: true },
    type: { type: String, default: 'video' }, // 'video' | 'audio'
    poster: { type: String, default: '' },
    segments: { type: Array, default: () => [] }, // Array of { start, end, label, color }
    autoplay: { type: Boolean, default: false }
});

const emit = defineEmits(['segment-change', 'ended', 'ready', 'timeupdate']);

// --- References ---
const mediaRef = ref(null);
const containerRef = ref(null);
const timelineRef = ref(null);
const showControls = ref(true);
const controlsTimeout = ref(null);

// --- Logic Integration (Composable) ---
const { 
    isPlaying, isMuted, isWaiting, currentTime, duration, volume, playbackRate, buffered, loopRange,
    togglePlay, seek, skip, setVolume, setPlaybackRate, toggleLoopPoint
} = useMedia(mediaRef);

// --- Computed UI Helpers ---

// Format Time (00:00)
const formatTime = (seconds) => {
    if (!seconds || isNaN(seconds)) return "00:00";
    const date = new Date(seconds * 1000);
    const hh = date.getUTCHours();
    const mm = date.getUTCMinutes();
    const ss = date.getUTCSeconds().toString().padStart(2, '0');
    return hh ? `${hh}:${mm.toString().padStart(2, '0')}:${ss}` : `${mm}:${ss}`;
};

// Active Segment
const activeSegment = computed(() => {
    return props.segments.find(s => currentTime.value >= s.start && currentTime.value <= s.end);
});

// Watch Active Segment
watch(activeSegment, (newVal) => {
    if (newVal) emit('segment-change', newVal);
});

// --- UI Interaction Handlers ---

// Hide HUD automatically
const handleActivity = () => {
    if (props.type === 'audio') {
        showControls.value = true;
        return;
    }
    showControls.value = true;
    clearTimeout(controlsTimeout.value);
    controlsTimeout.value = setTimeout(() => {
        if (isPlaying.value) showControls.value = false;
    }, 3000);
};

// Timeline Click
const handleTimelineClick = (e) => {
    if (!timelineRef.value || !duration.value) return;
    const rect = timelineRef.value.getBoundingClientRect();
    const pos = (e.clientX - rect.left) / rect.width;
    seek(pos * duration.value);
};

const hoverTime = ref(null);
const hoverLeft = ref(0);

const handleTimelineHover = (e) => {
    if (!timelineRef.value || !duration.value) return;
    const rect = timelineRef.value.getBoundingClientRect();
    const pos = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
    hoverLeft.value = pos;
    hoverTime.value = (pos / rect.width) * duration.value;
};

const toggleFullscreen = () => {
    if (!containerRef.value) return;
    if (!document.fullscreenElement) {
        containerRef.value.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
};

// Keyboard Shortcuts
const handleKeydown = (e) => {
    // Ignore if focus is outside player
    if (!containerRef.value?.contains(document.activeElement)) return;
    
    // Prevent Scroll
    if(['Space', 'ArrowLeft', 'ArrowRight'].includes(e.code)) e.preventDefault();
    
    switch(e.code) {
        case 'Space': togglePlay(); break;
        case 'ArrowLeft': skip(-10); break;
        case 'ArrowRight': skip(10); break;
        case 'KeyL': toggleLoopPoint(); break;
        case 'KeyM': setVolume(isMuted.value ? 0.5 : 0); break;
        case 'KeyF': if(props.type === 'video') toggleFullscreen(); break;
    }
};

onMounted(() => {
    if (props.type === 'audio') showControls.value = true;
    containerRef.value?.addEventListener('keydown', handleKeydown);
    emit('ready');
});

// Expose internal state to parent (Sandbox)
defineExpose({
    currentTime,
    duration,
    seek: (t) => seek(t), // Proxy the seek function
    setVolume
});
</script>

<template>
    <div 
        ref="containerRef"
        tabindex="0"
        class="relative w-full bg-gray-950 overflow-hidden rounded-xl group select-none outline-none ring-1 ring-white/10 shadow-2xl font-sans"
        :class="type === 'audio' ? 'h-[180px]' : 'aspect-video'"
        @mousemove="handleActivity"
        @click="handleActivity"
        @mouseleave="type === 'video' && isPlaying ? showControls = false : null"
    >
        <!-- ========================================== -->
        <!-- 1. MEDIA LAYER (Video/Audio Elements)      -->
        <!-- ========================================== -->
        
        <video 
            v-if="type === 'video'"
            ref="mediaRef"
            :src="src"
            :poster="poster"
            class="w-full h-full object-contain bg-black"
            crossorigin="anonymous"
            :autoplay="autoplay"
            @click="togglePlay"
        ></video>

        <audio 
            v-else
            ref="mediaRef"
            :src="src"
            class="hidden"
            crossorigin="anonymous"
            :autoplay="autoplay"
        ></audio>

        <!-- ========================================== -->
        <!-- 2. VISUALIZER LAYER (Audio Only)           -->
        <!-- ========================================== -->
        
        <div v-if="type === 'audio'" class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-900 to-black overflow-hidden">
            <!-- Background Image Blur -->
            <div v-if="poster" class="absolute inset-0 opacity-20 bg-cover bg-center blur-md scale-110" :style="`background-image: url('${poster}')`"></div>
            
            <!-- Animated Bars -->
            <div class="flex items-end justify-center gap-1 h-24 z-10 w-full px-10 opacity-80">
                <div v-for="i in 32" :key="i" 
                     class="w-2 bg-gradient-to-t from-primary-600 to-primary-400 rounded-t-sm transition-all duration-[50ms]"
                     :class="{'animate-music-bar': isPlaying}"
                     :style="{ 
                         height: isPlaying ? '30%' : '10%', // Animation overridden by CSS keyframes below
                         animationDelay: `${Math.random() * 0.5}s`,
                         opacity: isPlaying ? 1 : 0.3
                     }"
                ></div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 3. OVERLAYS (Spinner, Big Play, Toast)     -->
        <!-- ========================================== -->
        
        <!-- Buffering Spinner -->
        <div v-if="isWaiting" class="absolute inset-0 z-30 flex items-center justify-center bg-black/40 backdrop-blur-sm pointer-events-none">
            <div class="w-14 h-14 border-4 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Big Play Button (Video Paused) -->
        <div 
            v-if="type === 'video' && !isPlaying && !isWaiting" 
            class="absolute inset-0 z-20 flex items-center justify-center bg-black/20 cursor-pointer"
            @click="togglePlay"
        >
            <div class="w-20 h-20 bg-black/40 backdrop-blur-md rounded-full flex items-center justify-center border border-white/20 shadow-2xl hover:scale-110 hover:bg-primary-600/80 transition-all duration-300 group/bigbtn">
                <PlayIcon class="w-10 h-10 text-white ml-1 group-hover/bigbtn:text-white" />
            </div>
        </div>

        <!-- Segment Info Toast (Floating Top Center) -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 -translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-4"
        >
            <div v-if="activeSegment && showControls" class="absolute top-6 left-1/2 -translate-x-1/2 z-30">
                <div class="flex items-center gap-3 bg-black/60 backdrop-blur-md px-5 py-2.5 rounded-full border border-gray-700/50 shadow-xl">
                    <span class="w-2.5 h-2.5 rounded-full animate-pulse shadow-[0_0_10px_currentColor]" :style="{ color: activeSegment.color || '#3b82f6', backgroundColor: activeSegment.color || '#3b82f6' }"></span>
                    <span class="text-sm font-medium text-white tracking-wide max-w-[250px] truncate">
                        {{ activeSegment.label }}
                    </span>
                    <span class="text-xs text-gray-400 font-mono border-l border-gray-600 pl-3 ml-1">
                        {{ formatTime(activeSegment.start) }} - {{ formatTime(activeSegment.end) }}
                    </span>
                </div>
            </div>
        </Transition>

        <!-- ========================================== -->
        <!-- 4. HUD (Heads-Up Display) Controls         -->
        <!-- ========================================== -->
        
        <div 
            class="absolute bottom-0 left-0 right-0 z-40 px-5 pb-5 pt-16 bg-gradient-to-t from-black/95 via-black/70 to-transparent transition-all duration-500"
            :class="showControls ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0 pointer-events-none'"
            dir="ltr" 
        >
            <!-- Timeline Track Container -->
            <div 
                ref="timelineRef"
                class="relative h-2.5 w-full bg-gray-700/40 rounded-full cursor-pointer group/timeline mb-5 hover:h-4 transition-all duration-300"
                @click="handleTimelineClick"
                @mousemove="handleTimelineHover"
                @mouseleave="hoverTime = null"
            >
                <!-- Buffer Bar -->
                <div class="absolute top-0 left-0 h-full bg-gray-500/30 rounded-full transition-all duration-500" :style="{ width: `${buffered}%` }"></div>

                <!-- Segments Markers Layer -->
                <div v-for="(seg, idx) in segments" :key="idx"
                     class="absolute top-0 h-full hover:brightness-125 transition-all z-10 pointer-events-none opacity-60"
                     :style="{ 
                        left: `${(seg.start / duration) * 100}%`, 
                        width: `${((seg.end - seg.start) / duration) * 100}%`,
                        backgroundColor: seg.color || '#3b82f6'
                     }"
                >
                    <!-- Tiny white markers at edges -->
                    <div class="absolute left-0 top-0 bottom-0 w-[1px] bg-white/40"></div>
                    <div class="absolute right-0 top-0 bottom-0 w-[1px] bg-white/40"></div>
                </div>

                <!-- A-B Loop Region Layer -->
                <div v-if="loopRange.start !== null" 
                     class="absolute top-0 h-full border-x-2 border-yellow-400 bg-yellow-400/20 z-15 pointer-events-none animate-pulse"
                     :style="{
                        left: `${(loopRange.start / duration) * 100}%`,
                        width: loopRange.end ? `${((loopRange.end - loopRange.start) / duration) * 100}%` : '0px'
                     }"
                >
                    <!-- Start Flag -->
                    <div class="absolute -top-3 -left-1.5 text-[10px] text-yellow-400 font-bold">A</div>
                    <!-- End Flag -->
                    <div v-if="loopRange.end" class="absolute -top-3 -right-1.5 text-[10px] text-yellow-400 font-bold">B</div>
                </div>

                <!-- Play Progress Bar -->
                <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-primary-600 to-primary-400 rounded-full flex items-center justify-end z-20" 
                     :style="{ width: `${(currentTime / duration) * 100}%` }">
                    <!-- Thumb (Glows on Hover) -->
                    <div class="w-4 h-4 bg-white rounded-full shadow-[0_0_10px_rgba(255,255,255,0.5)] scale-0 group-hover/timeline:scale-100 transition-transform duration-200"></div>
                </div>

                <!-- Hover Tooltip -->
                <div v-if="hoverTime !== null" 
                     class="absolute -top-12 -translate-x-1/2 bg-black/80 backdrop-blur text-white text-xs px-2.5 py-1.5 rounded-lg font-mono border border-gray-700 shadow-xl"
                     :style="{ left: `${hoverLeft}px` }"
                >
                    {{ formatTime(hoverTime) }}
                </div>
            </div>

            <!-- Controls Toolbar -->
            <div class="flex items-center justify-between">
                
                <!-- Left: Transport Controls -->
                <div class="flex items-center gap-4">
                    <!-- Play/Pause -->
                    <button @click="togglePlay" class="text-white hover:text-primary-400 hover:scale-110 transition-all active:scale-95">
                        <component :is="isPlaying ? PauseIcon : PlayIcon" class="w-9 h-9 drop-shadow-md" />
                    </button>
                    
                    <!-- Skips -->
                    <div class="flex items-center gap-1">
                        <button @click="skip(-10)" class="group p-2 rounded-full hover:bg-white/10 text-gray-300 hover:text-white transition-all" title="Back 10s">
                            <BackwardIcon class="w-5 h-5 group-active:-translate-x-1 transition-transform" />
                        </button>
                        <button @click="skip(10)" class="group p-2 rounded-full hover:bg-white/10 text-gray-300 hover:text-white transition-all" title="Forward 10s">
                            <ForwardIcon class="w-5 h-5 group-active:translate-x-1 transition-transform" />
                        </button>
                    </div>

                    <!-- Volume -->
                    <div class="group/vol flex items-center gap-2 relative pl-2">
                        <button @click="setVolume(isMuted ? 1 : 0)" class="text-white hover:text-primary-400 transition-colors">
                            <component :is="isMuted || volume === 0 ? SpeakerXMarkIcon : SpeakerWaveIcon" class="w-6 h-6" />
                        </button>
                        <div class="w-0 overflow-hidden group-hover/vol:w-24 transition-all duration-300 ease-out">
                            <input 
                                type="range" min="0" max="1" step="0.05" 
                                :value="isMuted ? 0 : volume" 
                                @input="e => setVolume(e.target.value)"
                                class="h-1.5 w-20 bg-gray-600 rounded-lg appearance-none cursor-pointer accent-primary-500"
                            >
                        </div>
                    </div>

                    <!-- Time Display -->
                    <div class="hidden sm:block text-xs font-mono tracking-wider text-gray-400 border-l border-gray-700 pl-4 ml-2">
                        <span class="text-white font-semibold">{{ formatTime(currentTime) }}</span> 
                        <span class="mx-1 opacity-50">/</span> 
                        <span>{{ formatTime(duration) }}</span>
                    </div>
                </div>

                <!-- Right: Tools (Loop, Speed, Fullscreen) -->
                <div class="flex items-center gap-3">
                    
                    <!-- A-B Loop Button -->
                    <button 
                        @click="toggleLoopPoint"
                        class="relative p-2 rounded-lg transition-all border border-transparent"
                        :class="loopRange.active 
                            ? 'bg-yellow-500/20 text-yellow-400 border-yellow-500/50 shadow-[0_0_10px_rgba(234,179,8,0.2)]' 
                            : 'text-gray-400 hover:text-white hover:bg-white/10'"
                        title="A-B Loop (L)"
                    >
                        <ArrowPathIcon class="w-5 h-5" />
                        <!-- Indicator Dot -->
                        <span v-if="loopRange.start !== null" class="absolute top-1 right-1 w-2 h-2 bg-yellow-400 rounded-full animate-ping"></span>
                    </button>

                    <!-- Speed Control Dropup -->
                    <div class="relative group/speed">
                        <button class="flex items-center justify-center w-10 h-9 text-xs font-bold border border-gray-600 rounded-lg text-gray-300 hover:text-white hover:border-gray-400 hover:bg-white/5 transition-all">
                            {{ playbackRate }}x
                        </button>
                        <div class="absolute bottom-full right-0 mb-3 hidden group-hover/speed:block bg-black/80 backdrop-blur-xl rounded-xl border border-gray-700 shadow-2xl overflow-hidden min-w-[80px] py-1 animate-fade-in-up">
                            <button 
                                v-for="rate in [0.5, 0.75, 1, 1.25, 1.5, 2]" 
                                :key="rate"
                                @click="setPlaybackRate(rate)"
                                class="block w-full px-4 py-2 text-xs text-center hover:bg-primary-600 hover:text-white transition-colors"
                                :class="playbackRate === rate ? 'text-primary-400 font-bold bg-white/5' : 'text-gray-300'"
                            >
                                {{ rate }}x
                            </button>
                        </div>
                    </div>

                    <!-- Fullscreen -->
                    <button 
                        v-if="type === 'video'" 
                        @click="toggleFullscreen" 
                        class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                        title="Fullscreen (F)"
                    >
                        <ArrowsPointingOutIcon class="w-6 h-6" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom Keyframes for Audio Visualizer */
@keyframes music-bar {
    0%, 100% { height: 10%; }
    50% { height: 60%; }
}
.animate-music-bar {
    animation: music-bar 1s ease-in-out infinite;
}

/* Custom Animation for Dropups */
@keyframes fade-in-up {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fade-in-up 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Range Input Reset & Styling */
input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
    height: 12px;
    width: 12px;
    border-radius: 50%;
    background: #ffffff;
    cursor: pointer;
    box-shadow: 0 0 5px rgba(0,0,0,0.5);
    transition: transform 0.1s;
}
input[type=range]:active::-webkit-slider-thumb {
    transform: scale(1.3);
}
</style>
