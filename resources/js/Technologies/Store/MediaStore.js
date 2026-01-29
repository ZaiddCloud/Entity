import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useMediaStore = defineStore('media-global', () => {
    // --- Window State ---
    const isDocked = ref(false);     // Sidebar dock
    const isIntegrated = ref(false); // Text-wrap mode
    const isMaximized = ref(false);
    const isPlaylistOpen = ref(false);
    const isOpen = ref(true); // Central player visibility state
    const sizeMode = ref('standard'); // 'mini' | 'standard' | 'theater' | 'full'

    // Position & Dimensions
    const windowPos = ref({ left: 100, top: 100 });
    const dimensions = ref({ width: 500, height: 480 }); // Default for Video

    // --- Media State ---
    const currentMedia = ref(null); // The media object (title, poster, etc.)
    const segments = ref([]);
    const activeSegmentSlug = ref(null);
    const type = ref('video'); // 'audio' | 'video'

    // --- Actions: Window ---
    const setDockMode = (docked, integrated) => {
        isDocked.value = docked;
        isIntegrated.value = integrated;
    };

    const togglePlaylist = () => {
        isPlaylistOpen.value = !isPlaylistOpen.value;
    };

    const updatePosition = (left, top) => {
        if (!isDocked.value && !isIntegrated.value && !isMaximized.value) {
            windowPos.value = { left, top };
        }
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
    const loadMedia = (mediaData, mediaType = 'video', segmentData = []) => {
        currentMedia.value = mediaData;
        type.value = mediaType;
        segments.value = segmentData;

        // Auto-sizing logic
        if (mediaType === 'audio') {
            dimensions.value.height = 240;
        } else {
            dimensions.value.height = 480;
        }
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

    const cycleSize = (allowedModes = ['mini', 'standard', 'theater', 'full']) => {
        const currentIndex = allowedModes.indexOf(sizeMode.value);
        const nextIndex = (currentIndex + 1) % allowedModes.length;
        sizeMode.value = allowedModes[nextIndex];

        // Sync legacy isMaximized for backward compatibility if needed
        isMaximized.value = (sizeMode.value === 'full');
    };

    // Computed
    const isFloating = computed(() => !isDocked.value && !isIntegrated.value);

    return {
        // State
        isDocked,
        isIntegrated,
        isMaximized,
        isPlaylistOpen,
        isOpen,
        sizeMode,
        windowPos,
        dimensions,
        currentMedia,
        segments,
        activeSegmentSlug,
        type,

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
        toggleMaximize,
        setOpen,
        cycleSize
    };
});
