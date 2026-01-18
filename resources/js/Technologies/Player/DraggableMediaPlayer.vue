<script setup>
import { ref, computed, onMounted } from 'vue';
import { useMedia } from './Composables/useMedia';
import { 
    Play, Pause, Volume2, VolumeX, Maximize2, Minimize2, 
    X, Pin, Minus, Square, ChevronDown, Music, 
    SkipBack, SkipForward, Repeat, Menu, List, Settings,
    FileAudio, Search, Sidebar
} from 'lucide-vue-next';

// --- Props & Emits ---
const props = defineProps({
    src: { type: String, required: true },
    title: { type: String, default: 'Unknown Track' },
    type: { type: String, default: 'audio' },
    poster: { type: String, default: '' },
    segments: { type: Array, default: () => [] },
    autoplay: { type: Boolean, default: false },
    isDocked: { type: Boolean, default: false },
    isIntegrated: { type: Boolean, default: false } // NEW
});

const emit = defineEmits(['segment-change', 'ended', 'ready', 'timeupdate', 'close', 'toggle-playlist', 'toggle-dock']);

// --- References ---
const mediaRef = ref(null);
const windowRef = ref(null);
const timelineRef = ref(null);

// --- Window State ---
const isPlaylistOpen = ref(true);
const isMinimized = ref(false);
const isMaximized = ref(false);
const showSpeedMenu = ref(false);

// --- Logic Integration ---
const {
    isPlaying, isMuted, isWaiting, currentTime, duration, volume, playbackRate,
    togglePlay, seek, skip, setVolume, setPlaybackRate, toggleLoopPoint, loopRange
} = useMedia(mediaRef, emit);

// --- Computed Helpers ---
const formatTime = (seconds) => {
    if (!seconds || isNaN(seconds)) return "00:00";
    const date = new Date(seconds * 1000);
    const hh = date.getUTCHours();
    const mm = date.getUTCMinutes();
    const ss = date.getUTCSeconds().toString().padStart(2, '0');
    return hh ? `${hh}:${mm.toString().padStart(2, '0')}:${ss}` : `${mm}:${ss}`;
};

// --- Drag Logic ---
const isDragging = ref(false);
const dragOffset = ref({ x: 0, y: 0 });
const windowPos = ref({ left: window.innerWidth / 2 - 400, top: window.innerHeight / 2 - 240 });

const startDrag = (e) => {
    if (e.target.closest('.win-btn') || isMaximized.value || props.isDocked) return;

    if (props.isIntegrated) {
        // Capture current position before popping out
        const rect = windowRef.value.getBoundingClientRect();
        windowPos.value = {
            left: rect.left,
            top: rect.top
        };
        emit('toggle-dock');
        return; // Exit early - next drag will work in floating mode
    }

    isDragging.value = true;
    const rect = windowRef.value.getBoundingClientRect();
    dragOffset.value = {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
    window.addEventListener('mousemove', onDrag);
    window.addEventListener('mouseup', stopDrag);
};

const onDrag = (e) => {
    if (!isDragging.value) return;
    windowPos.value = {
        left: e.clientX - dragOffset.value.x,
        top: e.clientY - dragOffset.value.y
    };
};

const stopDrag = () => {
    isDragging.value = false;
    window.removeEventListener('mousemove', onDrag);
    window.removeEventListener('mouseup', stopDrag);
};

// --- Timeline Logic ---
const handleTimelineClick = (e) => {
    if (!timelineRef.value || !duration.value) return;
    const rect = timelineRef.value.getBoundingClientRect();
    const pos = (e.clientX - rect.left) / rect.width;
    seek(pos * duration.value);
};

// --- Window Controls ---
const togglePlaylist = () => {
    isPlaylistOpen.value = !isPlaylistOpen.value;
};

const closePlayer = () => {
    emit('close');
};

const toggleMaximize = () => {
    if (props.isDocked || props.isIntegrated) return; // Disable maximize in non-floating modes
    isMaximized.value = !isMaximized.value;
    if (isMaximized.value) {
        windowPos.value = { left: 0, top: 0 };
    } else {
        windowPos.value = { left: window.innerWidth / 2 - 400, top: window.innerHeight / 2 - 240 };
    }
};

onMounted(() => {
    if (!props.isDocked && !props.isIntegrated) {
        windowPos.value = {
            left: window.innerWidth / 2 - (isPlaylistOpen.value ? 400 : 250),
            top: window.innerHeight / 2 - 240
        };
    }
});
</script>

<template>
    <div
        ref="windowRef"
        class="pot-window"
        dir="ltr"
        :class="{
            'maximized': isMaximized,
            'fixed': !isDocked && !isIntegrated,
            'relative !left-auto !top-auto !transform-none !shadow-2xl border border-[#333] rounded-sm': isDocked || isIntegrated
        }"
        :style="(!isDocked && !isIntegrated) ? {
            left: isMaximized ? '0px' : `${windowPos.left}px`,
            top: isMaximized ? '0px' : `${windowPos.top}px`,
            width: isMaximized ? '100%' : (isPlaylistOpen ? '800px' : '500px'),
            height: isMaximized ? '100%' : '480px',
            transform: 'none'
        } : {
            width: isPlaylistOpen ? '800px' : '500px',
            height: '480px',
            left: 'auto',
            top: 'auto',
            position: (isDocked || isIntegrated) ? 'relative' : 'fixed'
        }"
    >
        <!-- === PLAYER SECTION === -->
        <div class="player-section flex flex-col relative border-r border-[#222] overflow-hidden" :class="{'flex-1': true}">
            <!-- ... (Content remains same, styles below handle it) ... -->

            <!-- Header (Draggable) -->
            <div dir="rtl" class="header" @mousedown="startDrag" :class="{'cursor-default': isDocked || isIntegrated}">

                 <!-- Window Controls (Right in RTL) -->
                 <div class="header-controls">
                    <!-- Dock Toggle (Restored for Integrated mode) -->
                    <div class="win-btn" :title="isIntegrated ? 'فك الدمج (عائم)' : (isDocked ? 'فك التثبيت (عائم)' : 'تثبيت جانبي')" @click="$emit('toggle-dock')">
                        <Sidebar class="w-3 h-3 text-[#aaaaaa]" :class="(isDocked || isIntegrated) ? 'text-yellow-500' : ''" />
                    </div>

                    <template v-if="!isDocked && !isIntegrated">
                        <div class="win-btn" title="Pin"><Pin class="w-3 h-3 text-[#aaaaaa] rotate-45" /></div>
                        <div class="win-btn" title="Minimize"><Minus class="w-3 h-3 text-[#aaaaaa]" /></div>
                        <div class="win-btn" @click="toggleMaximize" title="Maximize"><Square class="w-3 h-3 text-[#aaaaaa]" /></div>
                        <div class="win-btn close" @click="closePlayer" title="Close"><X class="w-3 h-3 text-[#aaaaaa]" /></div>
                    </template>
                </div>

                <!-- Title (Left in RTL) -->
                 <div class="header-left" dir="ltr">
                    <div class="pot-logo ml-2 hover:text-white transition-colors cursor-pointer flex items-center gap-1">
                        PotPlayer <ChevronDown class="w-3 h-3" />
                    </div>
                    <span class="file-info text-yellow-500 opacity-80 text-[11px] mx-2">MP3</span>
                    <span class="border-l border-gray-700 h-3 mx-1"></span>
                    <span class="track-title text-gray-400 text-[11px] max-w-[200px] truncate">
                        {{ title }}
                    </span>
                </div>
            </div>

            <!-- Stage (Content) -->
            <div class="stage bg-black flex-1 relative flex items-center justify-center overflow-hidden">
                <audio ref="mediaRef" :src="src" :autoplay="autoplay" crossorigin="anonymous" @ended="$emit('ended')"></audio>
                
                <!-- Background Art -->
                <div class="album-art absolute top-0 left-0 w-full h-[60%] opacity-60 bg-cover bg-center"
                     :style="`background-image: url('${poster || 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?q=80&w=2670&auto=format&fit=crop'}'); mask-image: linear-gradient(to bottom, black 50%, transparent 100%);`">
                </div>

                <!-- Info Overlay -->
                <div class="info-overlay absolute bottom-5 left-5 z-10 w-[calc(100%-40px)]" dir="ltr">
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

                <!-- Fake Viz -->
                <div class="viz-container absolute bottom-5 right-5 flex items-end gap-[2px] h-10 opacity-80">
                    <div v-for="i in 12" :key="i" class="v-bar bg-blue-500 w-[3px]"
                         :style="{ height: isPlaying ? `${Math.random() * 100}%` : '5px', transition: 'height 0.1s' }">
                    </div>
                </div>
            </div>

            <!-- Footer Controls -->
            <div class="footer bg-[#141414] pb-2 relative" dir="ltr">
                <!-- Seek Bar -->
                <div ref="timelineRef" class="seek-bar-wrapper h-3 w-full cursor-pointer relative mb-1 group" @click="handleTimelineClick">
                    <div class="seek-bg absolute inset-0 bg-transparent"></div>
                    <div class="seek-progress h-1 bg-yellow-500 relative transition-all group-hover:h-1.5"
                         :style="{ width: `${(currentTime / duration) * 100}%` }">
                        <div class="absolute -right-1.5 -top-[3px] w-2.5 h-2.5 bg-white rounded-full shadow opacity-100"></div>
                    </div>
                </div>

                <!-- Buttons Row -->
                <div class="controls-row flex items-center justify-between px-3 h-9">
                    
                    <!-- Left: Playback Controls -->
                    <div class="flex items-center gap-2">
                         <button class="p-btn" title="Previous 10s" @click="skip(-10)">
                            <SkipBack class="w-4 h-4 text-gray-400 hover:text-white" />
                        </button>
                        <button class="p-btn p-btn-lg text-white" title="Play/Pause" @click="togglePlay">
                             <component :is="isPlaying ? Pause : Play" class="w-5 h-5 fill-current text-gray-200 hover:text-white" />
                        </button>
                        <button class="p-btn" title="Next 10s" @click="skip(10)">
                            <SkipForward class="w-4 h-4 text-gray-400 hover:text-white" />
                        </button>
                    </div>

                    <!-- Center: Volume & Time -->
                    <div class="flex flex-1 items-center justify-center gap-4">
                         <div class="text-[11px] font-mono text-gray-400">
                             {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
                         </div>
                         
                         <!-- Volume -->
                         <div class="flex items-center gap-2 group cursor-pointer">
                            <button @click="setVolume(isMuted ? 1 : 0)">
                                <component :is="isMuted || volume === 0 ? VolumeX : Volume2" class="w-3 h-3 text-gray-500 group-hover:text-white transition-colors" />
                            </button>
                            <div class="vol-slider w-16 h-1 bg-[#444] rounded overflow-hidden cursor-pointer" @click="(e) => {
                                const rect = e.target.getBoundingClientRect();
                                const vol = (e.clientX - rect.left) / rect.width;
                                setVolume(vol);
                            }">
                                <div class="vol-fill h-full bg-yellow-500" :style="{ width: `${volume * 100}%` }"></div>
                            </div>
                         </div>
                    </div>

                    <!-- Right: Utils -->
                    <div class="flex items-center gap-1 relative">
                        <!-- Speed -->
                        <button class="p-btn text-[10px] font-bold text-yellow-600 hover:text-yellow-400 w-auto px-1" @click="showSpeedMenu = !showSpeedMenu">
                            {{ playbackRate }}x
                        </button>
                        <!-- Speed Menu -->
                        <div v-if="showSpeedMenu" class="absolute bottom-full right-0 mb-2 bg-[#1a1a1a] border border-[#333] rounded shadow-xl flex flex-col py-1 z-50 min-w-[60px]">
                            <button v-for="rate in [0.5, 1.0, 1.5, 2.0]" :key="rate" class="px-2 py-1 text-xs hover:bg-[#333] text-left text-gray-300" 
                                @click="setPlaybackRate(rate); showSpeedMenu = false">
                                {{ rate }}x
                            </button>
                        </div>

                        <button class="p-btn" title="Repeat" @click="toggleLoopPoint">
                            <Repeat class="w-3 h-3 text-gray-400 hover:text-white" :class="{'text-yellow-500': loopRange.start !== null}" />
                        </button>
                        <button class="p-btn" title="Playlist" @click="togglePlaylist">
                             <Menu class="w-3 h-3 text-gray-400 hover:text-white" :class="{'text-white': isPlaylistOpen}" />
                        </button>
                         <button class="p-btn" title="Segments">
                            <List class="w-3 h-3 text-gray-400 hover:text-white" />
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- === PLAYLIST SECTION === -->
        <div v-show="isPlaylistOpen" class="playlist-section w-[280px] bg-[#111] border-l border-[#333] flex flex-col" dir="ltr">
            <div class="pl-header h-[30px] bg-[#1f1f1f] flex items-center justify-between px-2 text-[11px] text-[#aaa] border-b border-[#2a2a2a]">
                <span>Playlist</span>
                <div class="flex gap-2">
                    <Minus class="w-[10px] h-[10px] cursor-pointer hover:text-white" />
                    <X class="w-[10px] h-[10px] cursor-pointer hover:text-red-500" @click="togglePlaylist" />
                </div>
            </div>

            <div class="pl-tabs flex bg-[#181818] text-[11px]">
                 <div class="pl-tab px-3 py-1.5 text-gray-400 cursor-pointer border-t-2 border-transparent hover:text-gray-200 active bg-[#222] border-t-yellow-500 text-white">Default</div>
                 <div class="pl-tab px-3 py-1.5 text-gray-400 cursor-pointer border-t-2 border-transparent hover:text-gray-200">History</div>
            </div>

            <div class="pl-content flex-1 overflow-y-auto py-1">
                <div class="pl-item flex gap-2 px-2 py-1 text-[11px] text-[#bbb] cursor-pointer bg-[#2a2a2a] text-yellow-500">
                     <Play class="w-[10px] h-[10px] mt-1 fill-current" />
                     <div class="flex-1 truncate font-sans">{{ title }}</div>
                     <div class="text-gray-600">{{ formatTime(duration) }}</div>
                </div>
                <!-- Segments as Playlist Items -->
                <div v-for="(seg, idx) in segments" :key="idx" 
                     class="pl-item flex gap-2 px-2 py-1 text-[11px] text-[#bbb] cursor-pointer hover:bg-[#222] opacity-80"
                     @click="seek(seg.start)">
                    <FileAudio class="w-[10px] h-[10px] text-gray-500 mt-1" />
                    <div class="flex-1 truncate font-sans">{{ seg.label || `Segment ${idx+1}` }}</div>
                    <div class="text-gray-600">{{ formatTime(seg.end - seg.start) }}</div>
                </div>
            </div>

             <div class="pl-footer h-[36px] bg-[#1f1f1f] flex items-center px-2 gap-1 border-t border-[#2a2a2a]">
                 <button class="pl-btn text-[9px] px-2 py-1 bg-[#333] text-[#ccc] rounded hover:bg-[#444]">ADD</button>
                 <button class="pl-btn text-[9px] px-2 py-1 bg-[#333] text-[#ccc] rounded hover:bg-[#444]">DEL</button>
                 <div class="flex-1"></div>
                 <Search class="w-3 h-3 text-gray-500" />
             </div>
        </div>
    </div>
</template>

<style scoped>
/* Scoped Styles based on Mockup */
.pot-window {
    position: fixed;
    z-index: 9999; /* Ensure high z-index */
    background: #141414;
    color: #e0e0e0;
    display: flex;
    box-shadow: 0 20px 50px rgba(0,0,0,0.8);
    border: 1px solid #333;
    user-select: none;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    direction: ltr; /* Ensure LTR internally */
}

/* Explicit Flex Classes to override potential global resets */
.flex { display: flex; }
.flex-col { flex-direction: column; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.justify-center { justify-content: center; }
.flex-1 { flex: 1 1 0%; }
.bg-black { background-color: #000; }

.header {
    background: #1f1f1f;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #2a2a2a;
    cursor: move;
}
.header-controls {
    display: flex;
    height: 100%;
}
.header-left {
    display: flex;
    align-items: center;
}
.win-btn {
    width: 28px;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #888;
    cursor: pointer;
}
.win-btn:hover { background: #333; color: white; }
.win-btn.close:hover { background: #d00; }

.p-btn {
    background: transparent;
    border: none;
    color: #bbb;
    cursor: pointer;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
}
.p-btn:hover { color: white; }
.p-btn-lg { width: 36px; height: 36px; }

/* Seek Bar */
.seek-bar-wrapper {
    position: relative;
    height: 12px;
    width: 100%;
    cursor: pointer;
}
.seek-progress {
    height: 4px;
    background-color: #eab308; /* yellow-500 */
    position: relative;
}

/* Volume Slider */
.vol-slider {
    width: 60px;
    height: 4px;
    background: #444;
    border-radius: 2px;
    position: relative;
}
.vol-fill {
    height: 100%;
    background: #eab308;
}

/* Scrollbar */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: #111; }
::-webkit-scrollbar-thumb { background: #333; border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: #555; }
</style>
