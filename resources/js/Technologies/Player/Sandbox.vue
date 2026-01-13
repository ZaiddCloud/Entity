<script setup>
import { ref } from 'vue';
import MediaPlayer from './MediaPlayer.vue';
import SegmentsEditor from './SegmentsEditor.vue';

const props = defineProps({
    media: Object,
    type: String
});

// Fallback to sample if no media provided (for backward compatibility if needed, mostly for testing)
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
  <div class="h-screen bg-black overflow-hidden flex flex-col">
    <!-- Header -->
    <div class="h-14 bg-gray-900 border-b border-gray-800 flex items-center px-6 justify-between shrink-0">
      <h1 class="text-xl font-bold text-white tracking-wide">
        Entity Media Studio
      </h1>
      <div class="text-xs text-gray-500 font-mono">
        DEV MODE
      </div>
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 flex overflow-hidden">
      <!-- LEFT: Media Player Area -->
      <div class="flex-1 bg-black flex items-center justify-center relative p-6">
        <div class="w-full max-w-5xl aspect-video bg-gray-900 rounded-xl shadow-2xl ring-1 ring-white/10 overflow-hidden relative">
          <MediaPlayer 
            ref="playerRef"
            :src="sampleVideo" 
            type="video" 
            :poster="samplePoster" 
            :segments="segments"
            @ready="onPlayerReady"
            @timeupdate="onTimeUpdate"
          />
                    
          <!-- Invisible Overlay to capture time updates if MediaPlayer doesn't emit them directly yet. 
                         Ideally MediaPlayer should emit time-update. 
                         For now, we can rely on the fact that useMedia exposes currentTime via ref if we had access, 
                         but since it's inside, let's use a quick hack or assume MediaPlayer emits 'timeupdate' or we bind to the video element.
                         
                         WAIT: MediaPlayer uses useMedia internally. It doesn't emit time updates by default in the props I saw.
                         Let's update MediaPlayer to emit 'time-update' OR use a ref to access its state.
                         My previous MediaPlayer code didn't emit generic time updates.
                         
                         BETTER APPROACH:
                         Let's rely on the fact that MediaPlayer's useMedia updates `currentTime`.
                         We can add a `ref="playerRef"` and access `playerRef.value.currentTime` ?? 
                         No, `script setup` is closed by default.

                         FIX: I will modify MediaPlayer slightly to emit `timeupdate` to parent, 
                         OR I will access the internal video element if exposed.
                         
                         Let's check MediaPlayer.vue again. It doesn't expose `currentTime`.
                         
                         Re-checking MediaPlayer.vue provided earlier...
                         It has: `const { currentTime ... } = useMedia(mediaRef);`
                         It does NOT defineExpose({ currentTime }).
                         
                         I will wrap the MediaPlayer in a div that listens to capture events? No, that's messy.
                         
                         I will update MediaPlayer.vue to `defineExpose({ currentTime, duration, seek })` 
                         so the parent can read it. This is the cleanest way.
                    -->
        </div>
      </div>

      <!-- RIGHT: Segments Editor Sidebar -->
      <div class="w-96 border-l border-gray-800 bg-gray-900 shrink-0 h-full">
        <!-- We need to pass the REAL time. Since we haven't wired MediaPlayer to emit it yet, 
                     I'll add a temporary interval or modify MediaPlayer in the next step.
                     For now, let's assume I will fix MediaPlayer to emit/expose time.
                -->
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
