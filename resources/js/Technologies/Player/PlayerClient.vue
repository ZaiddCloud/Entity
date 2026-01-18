<script setup>
import { ref, computed, watch, inject } from 'vue';
import DraggableMediaPlayer from './DraggableMediaPlayer.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    media: Object,
    activeSlug: String, 
    type: String,
    isIntegrated: { type: Boolean, default: false } // NEW
});

const isPlayerDocked = inject('isPlayerDocked', { value: false });
const toggleDock = inject('toggleDock', () => {});

// --- Computed State ---

// 1. Get Segments (Tracks/Scenes) from Children
const segments = computed(() => {
    if (props.media?.children?.length) {
        return props.media.children.map(child => ({
            id: child.id,
            slug: child.slug,
            label: child.title, // Map 'title' to 'label' for DraggableMediaPlayer
            title: child.title,
            file_path: child.file_path,
            start: child.start_time || 0,
            end: child.end_time || (child.duration || 0),
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
        ? "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4" 
        : "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4";
});

const currentPoster = computed(() => {
    return props.media?.cover_path 
        ? `/storage/${props.media.cover_path}` 
        : "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/images/BigBuckBunny.jpg";
});

// --- Navigation ---
const handleSegmentChange = (segment) => {
    // Navigate via Inertia if needed, or just play
    // Since DraggableMediaPlayer emits 'segment-change' but handles seeking internally for 'start/end',
    // we might just want to support slug navigation if it's a separate file.
    
    // For now, simpler integration: 
    // If the player asks to change segment (e.g. via playlist click), we can update the route if we want deep linking.
    // DraggableMediaPlayer's playlist mostly invokes 'seek'.
};

// Handle closing the player (optional, maybe hide or navigate away)
const closePlayer = () => {
    // For now, maybe just redirect to dashboard or do nothing (since it's persistent in studio)
    // or arguably, minimize it?
};
</script>

<template>
    <!-- 
        The Draggable Player is mounted here.
        Since it uses 'fixed' positioning, it will float above everything.
        This container should not have visible dimensions that block the UI.
    -->
    <DraggableMediaPlayer
        :src="currentSource"
        :title="media?.title || 'Unknown Media'"
        :type="type || 'video'"
        :poster="currentPoster"
        :segments="segments"
        :is-docked="isPlayerDocked.value"
        :is-integrated="isIntegrated"
        @close="closePlayer"
        @toggle-dock="() => { toggleDock(); $emit('toggle-dock'); }"
    />
</template>
