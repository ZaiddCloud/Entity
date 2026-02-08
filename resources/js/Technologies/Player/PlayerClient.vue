<script setup>
import { ref, computed, watch, inject, onMounted } from 'vue';
import DraggableMediaPlayer from './MediaPlayer.vue';
import { router } from '@inertiajs/vue3';
import { useMediaStore } from '@/Technologies/Store/MediaStore';
import { useResilientSync } from '@/Core/Sync/useResilientSync';

const props = defineProps({
    media: Object,
    activeChildId: String, 
    type: String,
    isIntegrated: { type: Boolean, default: false },
    isEmbedded: { type: Boolean, default: false },
    isStudioContext: { type: Boolean, default: false }
});

const emit = defineEmits(['timeupdate', 'segment-change', 'seek', 'toggle-dock', 'navigate', 'navigate-full']);

const mediaStore = useMediaStore();
const isPlayerDocked = inject('isPlayerDocked', { value: false });

onMounted(() => {
    if (props.isEmbedded || props.isIntegrated) {
        mediaStore.setDockMode(false, true);
    }
});

// Sync props to store dynamically (Fix for "Broken" floating logic)
watch(() => props.isIntegrated, (val) => {
    mediaStore.setIntegratedMode(val);
});
watch(() => props.isEmbedded, (val) => {
    if (val) mediaStore.setIntegratedMode(true);
});

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
            content: child.content || child.html_content || '', // Pass content for client-side nav
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
    if (props.activeChildId && segments.value.length) {
        const activeSeg = segments.value.find(s => (s.id || s.slug) === props.activeChildId);
        if (activeSeg?.file_path) {
            // Use streaming route for range request support
            const streamType = props.type === 'audio' ? 'audio' : 'videos';
            const path = `/stream/${streamType}/${activeSeg.file_path}`;
            console.log('[PlayerClient] Using segment source:', path);
            return path;
        }
    }

    // B. Fallback: Main Version File (Single File Mode)
    const mainFile = props.media?.versions?.[0]?.file_path || props.media?.file_path;
    if (mainFile) {
        // Use streaming route for range request support
        const streamType = props.type === 'audio' ? 'audio' : 'videos';
        const path = `/stream/${streamType}/${mainFile}`;
        console.log('[PlayerClient] Using main file source:', path);
        return path;
    }

    // C. Fallback: Sample
    const fallback = props.type === 'audio' 
        ? "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4" 
        : "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4";
    console.log('[PlayerClient] Using fallback source:', fallback);
    return fallback;
});

const currentPoster = computed(() => {
    const poster = props.media?.cover_path 
        ? `/storage/${props.media.cover_path}` 
        : null;
    console.log('[PlayerClient] Poster:', poster);
    return poster;
});

// --- Navigation ---
const handleSegmentChange = (segment) => {
    // When a segment is clicked in the playlist, emit navigate to parent
    const identifier = segment.id || segment._id || segment.slug;
    console.log('[PlayerClient] Segment selected:', segment, 'identifier:', identifier);
    if (identifier) {
        emit('navigate', identifier);
    } else {
        console.warn('[PlayerClient] Segment has no valid identifier:', segment);
    }
};

// --- Store Sync (Metadata) ---
// --- Store Sync (Metadata) ---
// Unified Watcher to trigger loadMedia (which handles Local-First logic)
watch([() => props.media, () => props.type, segments], ([newMedia, newType, newSegments]) => {
    if (newMedia) {
        mediaStore.loadMedia(newMedia, newType || 'video', newSegments);
    }
}, { immediate: true });


// Handle closing the player
// --- Persistence Actions ---

const handleAddSegment = async (data) => {
    try {
        // Hybrid Approach: Direct API when online, sync queue when offline
        if (navigator.onLine) {
            // Direct API call for immediate response
            await axios.post(route('api.segments.store'), {
                entity_id: props.media.id,
                entity_type: props.type,
                title: data.title,
                start_time: data.start
            });
            
            // Refresh props to get the new segment from server
            router.reload({ only: ['media'] });
        } else {
            // Offline: Queue for sync
            const { saveEntity } = useResilientSync();
            const payload = {
                id: `new-${Date.now()}`,
                entity_type: props.type,
                entity_id: props.media.id,
                title: data.title,
                start_time: data.start,
                file_path: null,
                method: 'POST',
                url: route('api.segments.store')
            };

            await saveEntity(payload);
            
            if (window.notifySync) {
                window.notifySync(`✅ تم إضافة "${data.title}" محلياً (وضع عدم الاتصال)`, 'success');
            }

            // Optimistically update UI
            mediaStore.addSegment({
                id: payload.id,
                title: data.title,
                start: data.start
            });
        }
    } catch (error) {
        console.error('[PlayerClient] Error adding segment:', error);
        
        // Fallback to sync queue if API fails
        try {
            const { saveEntity } = useResilientSync();
            const payload = {
                id: `new-${Date.now()}`,
                entity_type: props.type,
                entity_id: props.media.id,
                title: data.title,
                start_time: data.start,
                file_path: null,
                method: 'POST',
                url: route('api.segments.store')
            };

            await saveEntity(payload);
            window.notifySync?.('⚠️ تم حفظ المقطع محلياً (سيتم المزامنة لاحقاً)', 'warning');
        } catch (fallbackError) {
            console.error('[PlayerClient] Fallback also failed:', fallbackError);
            window.notifySync?.('❌ فشل إضافة المقطع', 'error');
        }
    }
};

const handleDeleteSegment = async (segment) => {
    try {
        const id = segment.id || segment.slug;
        
        // Hybrid Approach: Direct API when online, sync queue when offline
        if (navigator.onLine) {
            // Direct API call for immediate deletion
            await axios.delete(route('api.segments.destroy', id), {
                data: {
                    entity_id: props.media.id,
                    entity_type: props.type
                }
            });
            
            // Navigate to full view after deletion
            router.visit(route('studio.show', { type: props.type, slug: props.media.slug }));
        } else {
            // Offline: Queue for sync
            const { saveEntity } = useResilientSync();
            const payload = {
                id: id,
                entity_type: props.type,
                entity_id: props.media.id,
                method: 'DELETE',
                url: route('api.segments.destroy', id),
                priority: 'CRITICAL'
            };

            await saveEntity(payload);

            if (window.notifySync) {
                window.notifySync(`🗑️ تم جدولة حذف "${segment.title || segment.label}" (وضع عدم الاتصال)`, 'warning');
            }

            // Redirect to full view
            router.visit(route('studio.show', { type: props.type, slug: props.media.slug }), {
                only: ['entity'],
                onSuccess: () => {
                     window.notifySync?.('✅ تم تحديث الواجهة', 'info');
                }
            });
        }
    } catch (error) {
        console.error('[PlayerClient] Error deleting segment:', error);
        
        // Fallback to sync queue if API fails
        try {
            const { saveEntity } = useResilientSync();
            const id = segment.id || segment.slug;
            const payload = {
                id: id,
                entity_type: props.type,
                entity_id: props.media.id,
                method: 'DELETE',
                url: route('api.segments.destroy', id),
                priority: 'CRITICAL'
            };

            await saveEntity(payload);
            window.notifySync?.('⚠️ تم جدولة الحذف محلياً (سيتم المزامنة لاحقاً)', 'warning');
            
            // Still navigate away
            router.visit(route('studio.show', { type: props.type, slug: props.media.slug }));
        } catch (fallbackError) {
            console.error('[PlayerClient] Fallback also failed:', fallbackError);
            window.notifySync?.('❌ فشل حذف المقطع', 'error');
        }
    }
};

const updateSegment = async (segment) => {
    try {
        const id = segment.id || segment.slug;
        
        // Hybrid Approach: Direct API when online, sync queue when offline
        if (navigator.onLine) {
            // Direct API call for immediate update
            const payload = {
                entity_id: props.media.id,
                entity_type: props.type,
                title: segment.title
            };
            
            // Include start_time if it exists
            if (segment.start !== undefined) {
                payload.start_time = segment.start;
            }
            
            const response = await axios.put(route('api.segments.update', id), payload);
            
            console.log('[PlayerClient] Segment updated:', response.data);

            // Update store immediately for instant UI feedback
            mediaStore.updateSegment({
                id: segment.id,
                slug: segment.slug,
                title: segment.title,
                label: segment.title,
                start: segment.start
            });

            // Refresh props (Update entity to refresh Toolbar/Sidebar)
            router.reload({ only: ['entity'] });
        } else {
            // Offline: Queue for sync
            const { saveEntity } = useResilientSync();
            const payload = {
                id: id,
                entity_type: props.type,
                entity_id: props.media.id,
                title: segment.title,
                start_time: segment.start,
                method: 'PUT',
                url: route('api.segments.update', id)
            };
            
            await saveEntity(payload);
            
            if (window.notifySync) {
                window.notifySync(`💾 تم حفظ "${segment.title}" محلياً (وضع عدم الاتصال)`, 'success');
            }

            // Update store immediately for instant UI feedback
            mediaStore.updateSegment({
                id: segment.id,
                slug: segment.slug,
                title: segment.title,
                label: segment.title,
                start: segment.start
            });
        }
    } catch (error) {
         console.error('[PlayerClient] Error updating segment:', error);
         
         // Fallback to sync queue if API fails
         try {
             const { saveEntity } = useResilientSync();
             const id = segment.id || segment.slug;
             const payload = {
                 id: id,
                 entity_type: props.type,
                 entity_id: props.media.id,
                 title: segment.title,
                 start_time: segment.start,
                 method: 'PUT',
                 url: route('api.segments.update', id)
             };
             
             await saveEntity(payload);
             window.notifySync?.('⚠️ تم حفظ التعديلات محلياً (سيتم المزامنة لاحقاً)', 'warning');
             
             // Update store for UI feedback
             mediaStore.updateSegment({
                 id: segment.id,
                 slug: segment.slug,
                 title: segment.title,
                 label: segment.title,
                 start: segment.start
             });
         } catch (fallbackError) {
             console.error('[PlayerClient] Fallback also failed:', fallbackError);
             window.notifySync?.('❌ فشل حفظ التعديلات', 'error');
         }
    }
};

const closePlayer = () => {
    console.log('[PlayerClient] Closing player. setting isOpen to false');
    mediaStore.setOpen(false);
};

const playerRef = ref(null);
const seek = (time) => playerRef.value?.seek(time);

defineExpose({
    seek
});
</script>

<template>
    <!-- 
        The Draggable Player is mounted here.
        Since it uses 'fixed' positioning, it will float above everything.
        This container should not have visible dimensions that block the UI.
    -->
    <DraggableMediaPlayer
        v-show="mediaStore.isOpen"
        ref="playerRef"
        :src="currentSource"
        :title="media?.title || 'Unknown Media'"
        :type="type || 'video'"
        :poster="currentPoster"
        :segments="segments"
        :is-docked="isPlayerDocked.value"
        :is-integrated="isIntegrated || isEmbedded"
        :is-studio-context="isStudioContext"
        @close="closePlayer"
        @toggle-dock="() => { toggleDock(); $emit('toggle-dock'); }"
        @segment-change="(seg) => { emit('segment-change', seg); handleSegmentChange(seg); }"
        @timeupdate="(time) => emit('timeupdate', time)"
        @add-segment="handleAddSegment"
        @delete-segment="handleDeleteSegment"
        @update-segment="updateSegment"
        @navigate-full="$emit('navigate-full')"
    />
</template>
