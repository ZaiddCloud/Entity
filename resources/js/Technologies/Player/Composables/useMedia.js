import { ref, watch, onUnmounted } from 'vue';

const formatTime = (seconds) => {
    if (!seconds || isNaN(seconds)) return "00:00";
    const date = new Date(seconds * 1000);
    const hh = date.getUTCHours();
    const mm = date.getUTCMinutes();
    const ss = date.getUTCSeconds().toString().padStart(2, '0');
    return hh ? `${hh}:${mm.toString().padStart(2, '0')}:${ss}` : `${mm}:${ss}`;
};

export function useMedia(mediaRef, emit = null) {
    // State
    const isPlaying = ref(false);
    const isMuted = ref(false);
    const isWaiting = ref(false);
    const currentTime = ref(0);
    const duration = ref(0);
    const volume = ref(1);
    const playbackRate = ref(1);
    const buffered = ref(0);

    // Loop State
    const loopRange = ref({ start: null, end: null, active: false });

    // Core Methods
    const togglePlay = () => {
        if (!mediaRef.value) return;
        mediaRef.value.paused ? mediaRef.value.play() : mediaRef.value.pause();
    };

    const seek = (time) => {
        if (!mediaRef.value) return;
        const t = Math.max(0, Math.min(time, duration.value));
        mediaRef.value.currentTime = t;
        currentTime.value = t;
    };

    const skip = (seconds) => {
        seek(currentTime.value + seconds);
    };

    const setVolume = (val) => {
        if (!mediaRef.value) return;
        const v = Math.max(0, Math.min(val, 1));
        mediaRef.value.volume = v;
        volume.value = v;
        isMuted.value = v === 0;
    };

    const setPlaybackRate = (rate) => {
        if (!mediaRef.value) return;
        mediaRef.value.playbackRate = rate;
        playbackRate.value = rate;
    };

    const toggleLoopPoint = () => {
        const t = currentTime.value;
        if (loopRange.value.start === null) {
            loopRange.value.start = t;
        } else if (loopRange.value.end === null) {
            if (t > loopRange.value.start) {
                loopRange.value.end = t;
                loopRange.value.active = true;
                mediaRef.value.currentTime = loopRange.value.start;
                mediaRef.value.play();
            } else {
                loopRange.value.start = t;
            }
        } else {
            loopRange.value = { start: null, end: null, active: false };
        }
    };

    // Event Handlers
    const updateState = () => isPlaying.value = !mediaRef.value.paused; // General handler
    const onWaiting = () => isWaiting.value = true;
    const onPlaying = () => { isWaiting.value = false; isPlaying.value = true; };

    const onTimeUpdate = () => {
        if (!mediaRef.value) return;
        currentTime.value = mediaRef.value.currentTime;
        if (emit) emit('timeupdate', { currentTime: currentTime.value, duration: duration.value });

        // Loop Enforcement
        if (loopRange.value.active && loopRange.value.end !== null) {
            if (currentTime.value >= loopRange.value.end) {
                mediaRef.value.currentTime = loopRange.value.start;
            }
        }
    };

    const onLoadedMetadata = () => {
        if (!mediaRef.value) return;
        duration.value = mediaRef.value.duration;
        volume.value = mediaRef.value.volume;
        isMuted.value = mediaRef.value.muted;
        if (emit) emit('ready');
    };

    const onEnded = () => {
        if (emit) emit('ended');
    };

    const onProgress = () => {
        if (mediaRef.value && mediaRef.value.buffered.length > 0) {
            const end = mediaRef.value.buffered.end(mediaRef.value.buffered.length - 1);
            buffered.value = (end / duration.value) * 100;
        }
    };

    // Setup & Cleanup
    const events = [
        ['play', updateState],
        ['pause', updateState],
        ['waiting', onWaiting],
        ['playing', onPlaying],
        ['timeupdate', onTimeUpdate],
        ['loadedmetadata', onLoadedMetadata],
        ['ended', onEnded],
        ['progress', onProgress]
    ];

    // Setup & Cleanup (Reactive to el changes)
    watch(() => (mediaRef.value?.value || mediaRef.value), (newEl, oldEl) => {
        if (oldEl) {
            events.forEach(([evt, handler]) => oldEl.removeEventListener(evt, handler));
        }
        if (newEl) {
            events.forEach(([evt, handler]) => newEl.addEventListener(evt, handler));
            // Trigger initial metadata load if already ready
            if (newEl.readyState >= 1) onLoadedMetadata();
        }
    }, { immediate: true });

    onUnmounted(() => {
        const el = (mediaRef.value?.value || mediaRef.value);
        if (el) {
            events.forEach(([evt, handler]) => el.removeEventListener(evt, handler));
        }
    });

    return {
        isPlaying, isMuted, isWaiting, currentTime, duration, volume, playbackRate, buffered, loopRange,
        togglePlay, seek, skip, setVolume, setPlaybackRate, toggleLoopPoint, formatTime
    };
}
