import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

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

    const requestSeek = (time, showGuide = false) => {
        seekRequest.value = { time: Number(time), showGuide, timestamp: Date.now() };
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

    // Computed
    const isFloating = computed(() => !isDocked.value && !isIntegrated.value);

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
        toggleMaximize,
        setOpen,
        formatTime,
        cycleSize,
        toggleCollapse,
        resetLayout,
        requestSeek // Added action
    };
});
