<script setup>
import { onMounted, onUnmounted, ref, watch, computed, nextTick } from 'vue';
import { Check, X } from 'lucide-vue-next';
import { useMediaStore } from '@/Technologies/Store/MediaStore';
import { useEditorStore } from '@/Technologies/Store/EditorStore';
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
    isIntegrated: Boolean,
    isStudioContext: Boolean
});

const emit = defineEmits([
    'segment-change', 'ended', 'ready', 'timeupdate', 
    'close', 'toggle-dock', 'toggle-playlist',
    'add-segment', 'delete-segment', 'update-segment',
    'navigate-full' // Added navigate-full
]);

// ... (store logic) ...



const handleUpdateSegment = (seg) => {
    emit('update-segment', seg);
}

// --- Store & Media Logic ---
const store = useMediaStore();
const editorStore = useEditorStore();
const videoScreenRef = ref(null);

const {
    isPlaying, 
    currentTime, 
    duration, 
    volume, 
    playbackRate,
    loopRange,
    togglePlay, 
    play,
    pause,
    seek,
    setVolume,
    setPlaybackRate,
    toggleLoopPoint,
    formatTime
} = useMedia(computed(() => videoScreenRef.value?.mediaRef), emit);

// --- Window State & Drag ---
const windowRef = ref(null);

const startDrag = (e) => {
    // Parity: Prevent drag if clicking controls or resizing or if full/docked
    if (e.target.closest('.win-btn') || store.sizeMode === 'full' || store.isDocked) return;
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

// Listen for external seek requests (e.g. from Studio Toolbar)
watch(() => store.seekRequest, (req) => {
    if (req?.time !== undefined) {
        console.log('[MediaPlayer] External Seek Request:', req.time);
        handleSeek(req.time);
    }
}, { immediate: true });

// --- Smart Auto-Tracking (Polite Automation) ---
watch(currentTime, (now) => {
    if (!isPlaying.value) return; 
    
    // SMART GUARD: Don't override if user is explicitly in "Full View" 
    // This preserves the "Point 9" harmony where Full View is stable.
    if (editorStore.currentContentNode?.id === 'full') return;

    const found = props.segments?.find(s => 
        now >= (s.start || 0) && now < (s.end || s.start + 10)
    );

    if (found && found.slug !== store.activeSegmentSlug) {
        console.log('[MediaPlayer] Smart Auto-Tracking Segment:', found.slug);
        store.activeSegmentSlug = found.slug;
    }
});


const addQuickSegment = () => {
    pause(); // Auto-pause (Touch #25)
    // UI is now handled in PlayerPlaylist
};

const handleFinalizeAdd = (segData) => {
    store.addSegment(segData);
    emit('add-segment', segData);
};


const deleteActiveSegment = () => {
    if (store.activeSegmentSlug) {
        // Find the full segment object if possible, or just send the slug/id
        const seg = store.segments.find(s => s.slug === store.activeSegmentSlug);
        if (seg) emit('delete-segment', seg);
    }
};

const handleDeleteSegment = (slug) => {
    const seg = store.segments.find(s => s.slug === slug);
    if (seg) emit('delete-segment', seg);
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

const handleToggleDock = () => {
    const isCurrentlyFloating = store.isFloating;
    console.log('[MediaPlayer] Toggling dock. Currently Floating:', isCurrentlyFloating);

    if (!isCurrentlyFloating) {
        // We are docking OUT (Floating)
        if (windowRef.value) {
            const rect = windowRef.value.getBoundingClientRect();
            store.updatePosition(rect.left, rect.top);
        }
        store.setDockMode(false, false);
    } else {
        // We are docking IN (Integrated/Docked)
        store.setDockMode(false, true); 
    }
    
    emit('toggle-dock');
};

// --- Keyboard Shortcuts ---
const handleKeyDown = (e) => {
    // Ignore if typing in an input or contenteditable (Tiptap)
    if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName) || document.activeElement.isContentEditable) return;

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
  <Teleport to="body" :disabled="!store.isFloating && store.sizeMode !== 'full'">
    <div
        v-show="store.isOpen"
        ref="windowRef"
        dir="ltr"
        class="pot-window-v2"
        dusk="media-player"
        :class="{
            'maximized fixed inset-0 !top-[48px] z-[999999]': store.sizeMode === 'full',
            'fixed z-[90] rounded-2xl border border-white/5 shadow-[0_20px_50px_rgba(0,0,0,0.5)]': store.isFloating && store.sizeMode !== 'full',
            'relative z-50 shadow-2xl border border-white/5 rounded-2xl overflow-visible': !store.isFloating && store.sizeMode !== 'full',
            'mode-mini': store.sizeMode === 'mini',
            'mode-theater': store.sizeMode === 'theater'
        }"
        :style="store.sizeMode === 'full' ? {
            left: '0px',
            top: '48px',
            width: '100vw',
            height: 'calc(100vh - 48px)',
            transition: 'none'
        } : (store.isFloating ? {
            left: `${store.windowPos.left}px`,
            top: `${store.windowPos.top}px`,
            width: store.sizeMode === 'mini' ? '320px' : (store.sizeMode === 'theater' ? '800px' : `${store.dimensions.width || 500}px`),
            height: store.isCollapsed ? '86px' : (store.sizeMode === 'mini' ? '180px' : `${store.dimensions.height || (props.type === 'audio' ? 240 : 480)}px`),
            transition: 'none'
        } : {
            width: store.sizeMode === 'mini' ? '320px' : (store.sizeMode === 'theater' ? '100%' : `${store.dimensions.width || 500}px`),
            height: store.isCollapsed ? '86px' : (store.sizeMode === 'mini' ? '180px' : `${store.dimensions.height || (props.type === 'audio' ? 240 : 480)}px`),
            transition: 'none',
            overflow: 'visible'
        })"
    >
        <!-- RESIZE HANDLES (Floating only) -->
        <ResizeHandles 
            v-if="store.isFloating && !store.isMaximized && !store.isCollapsed" 
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
                    :is-collapsed="store.isCollapsed"
                    :size-mode="store.sizeMode"
                    @start-drag="startDrag"
                    @toggle-dock="handleToggleDock"
                    @cycle-size="store.cycleSize((props.isStudioContext || props.isIntegrated) ? ['mini', 'standard'] : ['mini', 'standard', 'theater', 'full'])"
                    @toggle-collapse="store.toggleCollapse"
                    @close="emit('close')"
                />

                <!-- Integrated Content Area (Touch #25) -->
                <div class="flex-1 flex min-h-0 overflow-hidden relative">

                    <!-- MAIN CONTENT: Video & Controls -->
                    <div class="flex-1 flex flex-col min-w-0">
                        <VideoScreen 
                            v-show="!store.isCollapsed"
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

                        <PlayerControls
                            :is-playing="isPlaying"
                            :current-time="currentTime"
                            :duration="duration"
                            :volume="volume"
                            :playback-rate="playbackRate"
                            :loop-range="loopRange"
                            :segments="store.segments"
                            :active-segment-slug="store.activeSegmentSlug"
                            :is-playlist-open="store.isPlaylistOpen"
                            @toggle-play="handleTogglePlay"
                            @seek="handleSeek"
                            @update:volume="setVolume"
                            @set-playback-rate="setPlaybackRate"
                            @toggle-loop="toggleLoopPoint"
                            @toggle-playlist="() => { store.togglePlaylist(); emit('toggle-playlist'); }"
                            @toggle-fullscreen="handleToggleFullscreen"
                            @add-segment="addQuickSegment"
                            @delete-segment="handleDeleteSegment"
                            @segment-change="handleSegmentSelect"
                        />
                    </div>

                    <!-- SIDEBAR: Playlist (Instant) -->
                    <div v-if="store.isPlaylistOpen" class="w-[180px] h-full shrink-0 relative z-[100]">
                        <PlayerPlaylist 
                            v-if="store.isPlaylistOpen"
                            ref="playlistRef"
                            class="w-[180px] h-full shrink-0 bg-[#0a0a0a] shadow-[-10px_0_30px_rgba(0,0,0,0.5)] border-l border-[#222]"
                            :title="title"
                            :segments="store.segments"
                            :active-slug="store.activeSegmentSlug"
                            :current-time="currentTime"
                            :duration="duration"
                            @close="store.isPlaylistOpen = false"
                            @select="handleSegmentSelect"
                            @add="addQuickSegment"
                            @commit-add="handleFinalizeAdd"
                            @delete="deleteActiveSegment"
                            @update="handleUpdateSegment"
                            @navigate-full="emit('navigate-full')"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
  </Teleport>
</template>

<style scoped>
/* Drawer Slide Animation */
.drawer-slide-enter-active,
.drawer-slide-leave-active {
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.drawer-slide-enter-from,
.drawer-slide-leave-to {
    transform: translateX(100%);
}

.fade-enter-active, .fade-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
}
</style>
