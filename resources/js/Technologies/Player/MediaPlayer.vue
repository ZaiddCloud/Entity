<script setup>
import { onMounted, onUnmounted, ref, watch, computed } from 'vue';
import { useMediaStore } from '@/Technologies/Store/MediaStore';
import { useMedia } from '@/Technologies/Player/Composables/useMedia';

// UI Components
import PlayerHeader from './UI/PlayerHeader.vue';
import PlayerControls from './UI/PlayerControls.vue';
import VideoScreen from './UI/VideoScreen.vue';
import PlayerPlaylist from './UI/PlayerPlaylist.vue';
import ResizeHandles from './UI/ResizeHandles.vue';

const props = defineProps({
    src: String,
    title: String,
    type: String, // 'audio' | 'video'
    poster: String,
    segments: Array,
    isDocked: Boolean, 
    isIntegrated: Boolean
});

const emit = defineEmits([
    'segment-change', 'ended', 'ready', 'timeupdate', 
    'close', 'toggle-dock', 'toggle-playlist'
]);

// --- Store & Media Logic ---
const store = useMediaStore();
const videoScreenRef = ref(null);

const {
    isPlaying, 
    currentTime, 
    duration, 
    volume, 
    playbackRate,
    loopRange,
    togglePlay, 
    seek,
    setPlaybackRate,
    toggleLoopPoint,
    formatTime
} = useMedia(computed(() => videoScreenRef.value?.mediaRef), emit);

// --- Window State & Drag ---
const windowRef = ref(null);

const startDrag = (e) => {
    // Parity: Prevent drag if clicking controls or resizing
    if (e.target.closest('.win-btn') || store.isMaximized || store.isDocked) return;
    store.startDrag(e);
};

// --- Mount Logic: Parity Centering & Sizing ---
onMounted(() => {
    if (!store.isDocked && !store.isIntegrated) {
        // V1 Parity: Set correct height based on media type
        const correctHeight = props.type === 'audio' ? 240 : 480;
        const correctWidth = store.dimensions.width || 500;
        
        // Always update dimensions to match media type
        store.updateDimensions(correctWidth, correctHeight);
        
        // Position in top-left corner (better for Arabic text)
        const initialLeft = 20; // 20px from left edge
        const initialTop = 130;  // 130px from top (below navbar + toolbar)
        store.updatePosition(initialLeft, initialTop);
    }
});

const handleTogglePlay = () => {
    togglePlay();
};

const handleSeek = (time) => {
    seek(time);
};

const handleSegmentSelect = (seg) => {
    seek(seg.start || 0);
    store.activeSegmentSlug = seg.slug; // Update Store
    emit('segment-change', seg);
};

const handleToggleFullscreen = () => {
    if (!windowRef.value) return;
    if (!document.fullscreenElement) {
        windowRef.value.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
};

// --- Keyboard Shortcuts ---
const handleKeyDown = (e) => {
    // Ignore if typing in an input
    if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;

    switch (e.key.toLowerCase()) {
        case ' ':
            e.preventDefault();
            handleTogglePlay();
            break;
        case 'arrowright':
            e.preventDefault();
            handleSeek(currentTime.value + 10);
            break;
        case 'arrowleft':
            e.preventDefault();
            handleSeek(currentTime.value - 10);
            break;
        case 'm':
            volume.value = volume.value === 0 ? 1 : 0;
            break;
        case 'f':
            handleToggleFullscreen();
            break;
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

defineExpose({
    seek
});
</script>

<template>
    <div
        ref="windowRef"
        dir="ltr"
        class="pot-window-v2"
        :class="{
            'maximized': store.isMaximized,
            'fixed z-[999999]': !store.isDocked && !store.isIntegrated,
            'relative !left-auto !top-auto !transform-none shadow-2xl border border-[#333] rounded-sm': store.isDocked || store.isIntegrated
        }"
        :style="store.isFloating ? {
            left: store.isMaximized ? '0px' : `${store.windowPos.left}px`,
            top: store.isMaximized ? '0px' : `${store.windowPos.top}px`,
            width: store.isMaximized ? '100%' : `${store.dimensions.width || (store.isPlaylistOpen ? 800 : 500)}px`,
            height: store.isMaximized ? '100%' : `${store.dimensions.height || (props.type === 'audio' ? 240 : 480)}px`,
        } : {
            width: `${store.dimensions.width || (store.isPlaylistOpen ? 800 : 500)}px`,
            height: `${store.dimensions.height || (props.type === 'audio' ? 240 : 480)}px`,
        }"
    >
        <!-- RESIZE HANDLES (Floating only) -->
        <ResizeHandles 
            v-if="store.isFloating && !store.isMaximized" 
            @start-resize="store.startResize" 
        />

        <!-- MAIN LAYOUT -->
        <div class="flex h-full w-full overflow-hidden bg-[#141414] shadow-black drop-shadow-2xl">
            <!-- PLAYER CORE -->
            <div class="flex-1 flex flex-col min-w-0 border-r border-[#222]">
                <!-- HEADER -->
                <PlayerHeader 
                    :title="title"
                    :is-docked="store.isDocked"
                    :is-integrated="store.isIntegrated"
                    :is-maximized="store.isMaximized"
                    @start-drag="startDrag"
                    @toggle-dock="() => { store.setDockMode(!store.isDocked, store.isIntegrated); emit('toggle-dock'); }"
                    @toggle-max="store.toggleMaximize"
                    @close="emit('close')"
                />

                <!-- VIDEO AREA -->
                <VideoScreen 
                    ref="videoScreenRef"
                    :src="src"
                    :poster="poster"
                    :type="type"
                    :is-playing="isPlaying"
                    :title="title"
                    :current-time="currentTime"
                    :duration="duration"
                    @click="handleTogglePlay"
                />

                <!-- CONTROLS -->
                <PlayerControls 
                    :is-playing="isPlaying"
                    :current-time="currentTime"
                    :duration="duration"
                    :volume="volume"
                    :playback-rate="playbackRate"
                    :loop-range="loopRange"
                    :is-playlist-open="store.isPlaylistOpen"
                    @toggle-play="handleTogglePlay"
                    @seek="handleSeek"
                    @update:volume="(v) => volume = v"
                    @set-playback-rate="setPlaybackRate"
                    @toggle-loop="toggleLoopPoint"
                    @toggle-playlist="() => { store.togglePlaylist(); emit('toggle-playlist'); }"
                    @toggle-fullscreen="handleToggleFullscreen"
                />
            </div>

            <!-- PLAYLIST SIDEBAR -->
            <PlayerPlaylist 
                v-show="store.isPlaylistOpen"
                :segments="segments"
                :active-slug="store.activeSegmentSlug"
                @select="handleSegmentSelect"
                @close="store.togglePlaylist"
            />
        </div>
    </div>
</template>
