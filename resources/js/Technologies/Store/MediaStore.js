import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { usePresence } from '@/Core/Sync/usePresence';
import { useSoftLock } from '@/Core/Sync/useSoftLock';

export const useMediaStore = defineStore('media-global', () => {
    // --- Window State ---
    const isDocked = ref(false);     // Sidebar dock
    const isIntegrated = ref(false); // Text-wrap mode
    const isMaximized = ref(false);
    const isPlaylistOpen = ref(false);
    const isOpen = ref(true); // Central player visibility state
    const isCollapsed = ref(false); // Minimized to header-only bar
    const sizeMode = ref('standard'); // 'mini' | 'standard' | 'theater' | 'full'

    // Position & Dimensions
    const windowPos = ref({ left: 343, top: 150 });
    const dimensions = ref({ width: 500, height: 480 }); // Default for Video

    // --- Media State ---
    const currentMedia = ref(null); // The media object (title, poster, etc.)
    const segments = ref([]);
    const activeSegmentSlug = ref(null);
    const type = ref('video'); // 'audio' | 'video'
    const seekRequest = ref({ time: 0, showGuide: false }); // Added seekRequest

    // Presence & Soft Locking
    const presence = usePresence()
    const softLock = useSoftLock()

    // --- Actions: Window ---
    const setDockMode = (docked, integrated) => {
        isDocked.value = docked;
        isIntegrated.value = integrated;
    };

    const togglePlaylist = () => {
        isPlaylistOpen.value = !isPlaylistOpen.value;
    };

    const updatePosition = (left, top) => {
        // Simple clamping to ensure visibility
        const safeLeft = Math.min(Math.max(0, left), window.innerWidth - (dimensions.value.width || 300));
        const safeTop = Math.min(Math.max(0, top), window.innerHeight - 30); // 30 = header height approx

        windowPos.value = { left: safeLeft, top: safeTop };
    };

    const updateDimensions = (width, height) => {
        // Enforce minimums
        dimensions.value = {
            width: Math.max(300, width),
            height: Math.max(150, height)
        };
    };

    const setIntegratedMode = (status) => {
        isIntegrated.value = status;
        // Reset specialized states if needed
        if (status) {
            isMaximized.value = false;
        }
    };

    // --- Actions: Media ---
    const loadMedia = async (mediaData, mediaType = 'video', segmentData = []) => {
        // Default to server data first (instant render)
        currentMedia.value = mediaData;
        type.value = mediaType;
        segments.value = segmentData;

        // Join presence for this media
        if (mediaData && mediaData.slug) {
            presence.join(mediaType, mediaData.slug)
        }

        // Auto-sizing logic
        if (mediaType === 'audio') {
            dimensions.value.height = 240;
        } else {
            dimensions.value.height = 480;
        }

        // --- LOCAL-FIRST OVERRIDE (Phase 13) ---
        // Check if we have a newer version in IndexedDB
        // We do this AFTER setting server data to ensure UI appears immediately, 
        // then snaps to local version if available (Optimistic)
        const { loadEntity } = await import('@/Core/Sync/useResilientSync');
        // Note: Dynamic import to avoid circular dependency if any, 
        // though useResilientSync is a composable so standard import is better if possible.
        // Let's use standard import at top of file, but for now dynamic is safe.
        // Actually, Pinia stores are pure JS, so standard useResilientSync import checks.
        // But let's stick to the pattern used in EditorStore if possible.
        // EditorStore imports it inside the action? No, let's check EditorStore again.
        // EditorStore imports it inside action: const { loadEntity } = useResilientSync()
        // But EditorStore imports useResilientSync at top? No.

        try {
            const { useResilientSync } = await import('@/Core/Sync/useResilientSync');
            const { loadEntity } = useResilientSync();

            const entityId = mediaData.id || mediaData.slug;
            const localVersion = await loadEntity(entityId, mediaType);

            if (localVersion) {
                console.log('[MediaStore] 📦 Found local override for media:', entityId);

                // Merge/Override Media Metadata
                if (localVersion.title) currentMedia.value.title = localVersion.title;
                if (localVersion.description) currentMedia.value.description = localVersion.description;

                // Override Segments if available
                const loadedSegments = localVersion.children || localVersion.segments;
                if (loadedSegments && Array.isArray(loadedSegments)) {
                    console.log('[MediaStore] 📼 Loaded local segments:', loadedSegments.length);
                    segments.value = loadedSegments;
                }
            }
        } catch (e) {
            console.warn('[MediaStore] Local load check failed:', e);
        }
    };

    const requestSeek = (time, showGuide = false) => {
        seekRequest.value = { time: Number(time), showGuide, timestamp: Date.now() };
    };

    const setActiveSegment = (slug) => {
        activeSegmentSlug.value = slug;
    };

    // --- Actions: Window Interactions ---
    const isDragging = ref(false);
    const dragOffset = ref({ x: 0, y: 0 });

    const startDrag = (e) => {
        if (isDocked.value || isIntegrated.value || isMaximized.value) return;

        isDragging.value = true;
        const rect = e.target.closest('.pot-window-v2')?.getBoundingClientRect();
        if (!rect) return;

        dragOffset.value = {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };

        const onDrag = (e) => {
            if (!isDragging.value) return;
            updatePosition(
                e.clientX - dragOffset.value.x,
                e.clientY - dragOffset.value.y
            );
        };

        const stopDrag = () => {
            isDragging.value = false;
            window.removeEventListener('mousemove', onDrag);
            window.removeEventListener('mouseup', stopDrag);
        };

        window.addEventListener('mousemove', onDrag);
        window.addEventListener('mouseup', stopDrag);
    };

    const isResizing = ref(false);
    const resizeDirection = ref('se');
    const resizeStart = ref({ x: 0, y: 0, w: 0, h: 0 });

    const startResize = (e, dir = 'se') => {
        if (isMaximized.value) return;

        isResizing.value = true;
        resizeDirection.value = dir;

        const rect = e.target.closest('.pot-window-v2')?.getBoundingClientRect();
        if (!rect) return;

        resizeStart.value = {
            x: e.clientX,
            y: e.clientY,
            w: rect.width,
            h: rect.height
        };

        const onResize = (e) => {
            if (!isResizing.value) return;

            const dx = e.clientX - resizeStart.value.x;
            const dy = e.clientY - resizeStart.value.y;

            let newWidth = dimensions.value.width;
            let newHeight = dimensions.value.height;

            if (resizeDirection.value.includes('e')) {
                newWidth = Math.max(300, resizeStart.value.w + dx);
            }
            if (resizeDirection.value.includes('s')) {
                newHeight = Math.max(150, resizeStart.value.h + dy);
            }

            updateDimensions(newWidth, newHeight);
        };

        const stopResize = () => {
            isResizing.value = false;
            window.removeEventListener('mousemove', onResize);
            window.removeEventListener('mouseup', stopResize);
        };

        window.addEventListener('mousemove', onResize);
        window.addEventListener('mouseup', stopResize);
    };

    const toggleMaximize = () => {
        if (isDocked.value || isIntegrated.value) return;
        isMaximized.value = !isMaximized.value;
    };

    const setOpen = (status) => {
        isOpen.value = status;
    }

    const toggleCollapse = () => {
        isCollapsed.value = !isCollapsed.value;
    };

    const resetLayout = () => {
        // Reset to default starting position (Top-Left for RTL context)
        windowPos.value = { left: 20, top: 130 };

        // Reset dimensions based on type (Ensure store is synced)
        const isAudio = type.value === 'audio';
        dimensions.value = {
            width: 500,
            height: isAudio ? 240 : 480
        };

        // Reset modes (Force safe modes)
        sizeMode.value = 'standard';
        isMaximized.value = false;
        isCollapsed.value = false;

        console.log('[MediaStore] Layout Reset to:', { type: type.value, pos: windowPos.value });
    };

    const cycleSize = (allowedModes = ['mini', 'standard', 'theater', 'full']) => {
        const currentIndex = allowedModes.indexOf(sizeMode.value);
        const nextIndex = (currentIndex + 1) % allowedModes.length;
        sizeMode.value = allowedModes[nextIndex];

        // Sync legacy isMaximized for backward compatibility if needed
        isMaximized.value = (sizeMode.value === 'full');
    };

    const formatTime = (seconds) => {
        if (!seconds || isNaN(seconds)) return "00:00";
        const date = new Date(seconds * 1000);
        const hh = date.getUTCHours();
        const mm = date.getUTCMinutes();
        const ss = date.getUTCSeconds().toString().padStart(2, '0');
        if (hh > 0) {
            return `${hh}:${mm.toString().padStart(2, '0')}:${ss}`;
        }
        return `${mm}:${ss}`;
    };

    const addSegment = (segData) => {
        const newSeg = {
            ...segData,
            slug: segData.slug || `seg-${Date.now()}`
        };
        // Add and sort chronologically (Touch #25)
        const newSegments = [...segments.value, newSeg];
        newSegments.sort((a, b) => (Number(a.start) || 0) - (Number(b.start) || 0));
        segments.value = newSegments;

        activeSegmentSlug.value = newSeg.slug;
    };

    const updateSegment = (updatedSeg) => {
        // Find and update the segment
        const index = segments.value.findIndex(s => (s.id || s.slug) === (updatedSeg.id || updatedSeg.slug));
        if (index !== -1) {
            // Create new array with updated segment
            const newSegments = [...segments.value];
            newSegments[index] = { ...newSegments[index], ...updatedSeg };

            // Re-sort if time changed
            newSegments.sort((a, b) => (Number(a.start) || 0) - (Number(b.start) || 0));
            segments.value = newSegments;
        }
    };

    // Computed
    const isFloating = computed(() => !isDocked.value && !isIntegrated.value);

    const cleanup = () => {
        presence.leave()
        softLock.stopMonitoring()
    }

    return {
        // State
        isDocked,
        isIntegrated,
        isMaximized,
        isPlaylistOpen,
        isOpen,
        isCollapsed,
        sizeMode,
        windowPos,
        dimensions,
        currentMedia,
        segments,
        activeSegmentSlug,
        type,
        seekRequest, // Added state

        // Computed
        isFloating,

        // Actions
        setDockMode,
        togglePlaylist,
        updatePosition,
        updateDimensions,
        setIntegratedMode,
        loadMedia,
        startDrag,
        startResize,
        setActiveSegment,
        toggleMaximize,
        setOpen,
        formatTime,
        cycleSize,
        toggleCollapse,
        resetLayout,
        requestSeek,
        addSegment,
        updateSegment,
        presence, softLock, cleanup
    };
});
