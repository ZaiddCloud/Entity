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

// --- Time Helpers ---
const secondsToTime = (seconds) => {
    if (!seconds) return '00:00';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    
    if (h > 0) {
        return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }
    return `${m}:${String(s).padStart(2, '0')}`;
};

const timeToSeconds = (str) => {
    if (!str) return 0;
    const p = str.split(':').map(Number);
    let s = 0, m = 1;
    while (p.length > 0) {
        s += m * p.pop();
        m *= 60;
    }
    return s;
};

// --- Add Segment Modal State ---
const showAddModal = ref(false);
const newSegmentForm = ref({
    title: '',
    start_time: '00:00',
    end_time: '00:00',
    file_path: ''
});

const openAddModal = () => {
    newSegmentForm.value = {
        title: props.type === 'audio' ? 'مقطع جديد' : 'مشهد جديد',
        start_time: '00:00',
        end_time: '00:00', 
        file_path: ''
    };
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
};

const saveNewSegment = async () => {
    try {
        const response = await axios.post(route('api.segments.store'), {
            entity_id: props.media.id,
            entity_type: props.type,
            title: newSegmentForm.value.title,
            file_path: newSegmentForm.value.file_path,
            start_time: timeToSeconds(newSegmentForm.value.start_time),
            end_time: timeToSeconds(newSegmentForm.value.end_time)
        });
        
        // Reload page to show new segment
        router.reload({ only: ['media'] });
        closeAddModal();
    } catch (error) {
        console.error('Failed to create segment:', error);
        alert('فشل إنشاء المقطع');
    }
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
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                    {{ segments.length > 0 ? 'Tracks / Scenes' : 'Segments' }}
                </h3>
                <button
                    @click="openAddModal"
                    class="p-1.5 rounded-lg bg-primary-600 hover:bg-primary-500 text-white transition-colors"
                    title="إضافة مقطع جديد"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </div>
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
                        <span v-else>{{ secondsToTime(seg.start) }} - {{ secondsToTime(seg.end) }}</span>
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
            <button
                @click="openAddModal"
                class="mt-4 px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm rounded-lg transition-colors"
            >
                إضافة مقطع
            </button>
        </div>

      </div>
    </div>

    <!-- Add Segment Modal -->
    <div 
        v-if="showAddModal" 
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click.self="closeAddModal"
    >
        <div class="bg-gray-900 rounded-xl shadow-2xl max-w-md w-full p-6 border border-gray-800">
            <h2 class="text-xl font-bold text-white mb-4">
                {{ props.type === 'audio' ? 'إضافة مقطع جديد' : 'إضافة مشهد جديد' }}
            </h2>

            <div class="space-y-4">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">العنوان</label>
                    <input
                        v-model="newSegmentForm.title"
                        type="text"
                        class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="اسم المقطع"
                    >
                </div>

                <!-- Start Time -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">وقت البداية (00:00)</label>
                    <input
                        v-model="newSegmentForm.start_time"
                        type="text"
                        placeholder="00:00"
                        class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent font-mono"
                    >
                </div>

                <!-- End Time -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">وقت النهاية (00:00)</label>
                    <input
                        v-model="newSegmentForm.end_time"
                        type="text"
                        placeholder="00:00"
                        class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent font-mono"
                    >
                </div>

                <!-- File Path (Optional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">مسار الملف (اختياري)</label>
                    <input
                        v-model="newSegmentForm.file_path"
                        type="text"
                        class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="audios/track.mp3"
                    >
                    <p class="text-xs text-gray-500 mt-1">اتركه فارغاً للمقاطع ضمن ملف واحد</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 mt-6">
                <button
                    @click="saveNewSegment"
                    class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white font-medium rounded-lg transition-colors"
                >
                    حفظ
                </button>
                <button
                    @click="closeAddModal"
                    class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 font-medium rounded-lg transition-colors"
                >
                    إلغاء
                </button>
            </div>
        </div>
    </div>
  </div>
</template>
