<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import MediaPlayer from './MediaPlayer.vue';
import SegmentsEditor from './SegmentsEditor.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    media: Object,
    activeSlug: String, 
    type: String
});

// --- Computed State ---

// 1. Get Segments (Tracks/Scenes) from Children
const segments = computed(() => {
    if (props.media?.children?.length) {
        return props.media.children.map(child => ({
            id: child.id,
            slug: child.slug,
            title: child.title,
            file_path: child.file_path,
            start: child.start_time || 0,
            end: child.end_time || (child.duration || 0),
            // Custom coloring for UI
            color: child.metadata?.color || '#3b82f6'
        }));
    }
    return [];
});

// 2. Determine Current Source
const currentSource = computed(() => {
    // A. Priority: Active Segment (Bundle Mode)
    if (props.activeSlug && segments.value.length) {
        const activeSeg = segments.value.find(s => s.slug === props.activeSlug);
        if (activeSeg?.file_path) {
            return `/storage/${activeSeg.file_path}`;
        }
    }

    // B. Fallback: Main Version File (Single File Mode)
    const mainFile = props.media?.versions?.[0]?.file_path || props.media?.file_path;
    if (mainFile) return `/storage/${mainFile}`;

    // C. Fallback: Sample
    return props.type === 'audio' 
        ? "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4" // Dummy Audio
        : "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4";
});

const currentPoster = computed(() => {
    return props.media?.cover_path 
        ? `/storage/${props.media.cover_path}` 
        : "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/images/BigBuckBunny.jpg";
});

// --- Player State ---
const playerRef = ref(null);
const currentPlayerTime = ref(0);
const currentDuration = ref(0);

// --- Handlers ---

const onPlayerReady = () => {
    // Auto-seek if segment has start time (for single file mode)
    if (props.activeSlug && segments.value.length) {
        const activeSeg = segments.value.find(s => s.slug === props.activeSlug);
        // If single file but segmented (start_time > 0), seek to it
        if (activeSeg && !activeSeg.file_path && activeSeg.start > 0) {
            playerRef.value?.seek(activeSeg.start);
        }
    }
};

const onTimeUpdate = ({ currentTime, duration }) => {
    currentPlayerTime.value = currentTime;
    currentDuration.value = duration;
};

// Navigation (Clicking a track in sidebar)
const handleSegmentClick = (segment) => {
    if (segment.slug === props.activeSlug) return;
    
    // Visit new slug
    router.visit(route('studio.show', { type: props.type, slug: segment.slug }), {
        preserveState: true,
        preserveScroll: true,
        only: ['activeSlug', '_legacy'] 
    });
};

const handleSeek = (time) => {
    playerRef.value?.seek(time);
};
</script>

<template>
  <div class="h-full w-full bg-black flex flex-col overflow-hidden">
    <!-- Main Workspace -->
    <div class="flex-1 flex overflow-hidden">
      
      <!-- LEFT: Media Player Area -->
      <div class="flex-1 bg-black flex items-center justify-center relative p-6">
        <div class="w-full max-w-5xl aspect-video bg-gray-900 rounded-xl shadow-2xl ring-1 ring-white/10 overflow-hidden relative">
          <MediaPlayer 
            ref="playerRef"
            :src="currentSource" 
            :type="props.type || 'video'" 
            :poster="currentPoster" 
            :segments="segments" 
            :autoplay="!!props.activeSlug"
            @ready="onPlayerReady"
            @timeupdate="onTimeUpdate"
          />
        </div>
      </div>

      <!-- RIGHT: Playlist / Segments Sidebar -->
      <div class="w-80 border-l border-gray-800 bg-gray-900 shrink-0 h-full overflow-y-auto flex flex-col">
        
        <!-- Header -->
        <div class="p-4 border-b border-gray-800 bg-gray-900/50 backdrop-blur sticky top-0 z-10">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">
                {{ segments.length > 0 ? 'Tracks / Scenes' : 'Segments' }}
            </h3>
            <p class="text-sm text-white font-medium truncate">
                {{ props.media?.title }}
            </p>
        </div>

        <!-- List -->
        <div v-if="segments.length" class="flex-1 p-2 space-y-1">
            <button
                v-for="(seg, idx) in segments"
                :key="seg.id"
                class="w-full text-right px-3 py-3 rounded-lg flex items-center gap-3 transition-all group"
                :class="seg.slug === props.activeSlug 
                    ? 'bg-primary-600/20 ring-1 ring-primary-500/50' 
                    : 'hover:bg-white/5'"
                @click="handleSegmentClick(seg)"
            >
                <!-- Number -->
                <span 
                    class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold shrink-0 transition-colors"
                    :class="seg.slug === props.activeSlug ? 'bg-primary-500 text-white' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700'"
                >
                    {{ idx + 1 }}
                </span>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div 
                        class="text-sm font-medium truncate"
                        :class="seg.slug === props.activeSlug ? 'text-primary-400' : 'text-gray-300 group-hover:text-white'"
                    >
                        {{ seg.title }}
                    </div>
                    <!-- Duration or Time Range -->
                    <div class="text-[10px] text-gray-500 font-mono mt-0.5">
                        <span v-if="seg.file_path">Double-click to play</span>
                        <span v-else>{{ seg.start }}s - {{ seg.end }}s</span>
                    </div>
                </div>

                <!-- Active Indicator -->
                <div v-if="seg.slug === props.activeSlug" class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></div>
            </button>
        </div>

        <!-- Empty State -->
        <div v-else class="flex-1 flex flex-col items-center justify-center text-gray-500 py-10">
            <span class="text-4xl mb-2 opacity-20">💿</span>
            <span class="text-sm">No tracks found</span>
        </div>

      </div>
    </div>
  </div>
</template>
