<script setup>
import { ref } from 'vue';
import MediaPlayer from './MediaPlayer.vue';
import SegmentsEditor from './SegmentsEditor.vue';

const props = defineProps({
    media: Object,
    type: String
});

// Fallback to sample if no media provided
const sampleVideo = props.media?.versions?.[0]?.file_path 
    ? `/storage/${props.media.versions[0].file_path}` 
    : (props.media?.file_path ? `/storage/${props.media.file_path}` : "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4");

const samplePoster = props.media?.cover_path 
    ? `/storage/${props.media.cover_path}` 
    : "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/images/BigBuckBunny.jpg";

// Initialize with some dummy segments
const segments = ref([
    { start: 10, end: 30, label: 'Intro Scene', color: '#10b981' }, 
    { start: 60, end: 90, label: 'The Chase', color: '#f59e0b' },
    { start: 120, end: 150, label: 'Climax', color: '#ef4444' }
]);

// Shared State
const playerRef = ref(null);
const currentPlayerTime = ref(0);
const currentDuration = ref(0);

// Handlers
const onPlayerReady = () => {
    // console.log("Player Ready");
};

// Update time from player to editor (for capture)
const onTimeUpdate = ({ currentTime, duration }) => {
    currentPlayerTime.value = currentTime;
    currentDuration.value = duration;
};

// Seek from editor to player
const handleSeek = (time) => {
    if (playerRef.value?.seek) {
        playerRef.value.seek(time);
    }
};

const handleSave = (finalSegments) => {
    console.log("Saving Final Segments:", finalSegments);
    alert(`Saved ${finalSegments.length} segments! (Check Console)`);
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
            :src="sampleVideo" 
            :type="props.type || 'video'" 
            :poster="samplePoster" 
            :segments="segments"
            @ready="onPlayerReady"
            @timeupdate="onTimeUpdate"
          />
        </div>
      </div>

      <!-- RIGHT: Segments Editor Sidebar -->
      <div class="w-96 border-l border-gray-800 bg-gray-900 shrink-0 h-full overflow-y-auto">
        <SegmentsEditor 
          :current-time="currentPlayerTime"
          :duration="currentDuration"
          :initial-segments="segments"
          @update:segments="segments = $event"
          @seek="handleSeek"
          @save-final="handleSave"
        />
      </div>
    </div>
  </div>
</template>
