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

    // Helper: Correctly Unwrap Media Element (Fix for Nested Refs)
    const getMediaEl = () => mediaRef.value?.value || mediaRef.value;

    // Core Methods
    const play = () => getMediaEl()?.play();
    const pause = () => getMediaEl()?.pause();

    const togglePlay = () => {
        const el = getMediaEl();
        if (!el) return;
        el.paused ? play() : pause();
    };

    const seek = (time) => {
        const el = getMediaEl();

        if (!el) {
            console.warn('[useMedia] Cannot seek: No media element found');
            return;
        }

        const t = Math.max(0, Math.min(time, duration.value || el.duration || Infinity));

        // Check if media is ready for seeking
        if (el.readyState < 2) {
            // Queue the seek for when metadata is loaded
            const onCanSeek = () => {
                el.removeEventListener('loadedmetadata', onCanSeek);
                el.removeEventListener('canplay', onCanSeek);
                seek(time);
            };
            el.addEventListener('loadedmetadata', onCanSeek, { once: true });
            el.addEventListener('canplay', onCanSeek, { once: true });
            return;
        }

        // Check if we have any seekable data
        if (el.seekable && el.seekable.length > 0) {
            const seekableStart = el.seekable.start(0);
            const seekableEnd = el.seekable.end(el.seekable.length - 1);

            // If target is outside seekable range, try to wait for more data
            if (t < seekableStart || t > seekableEnd) {
                console.warn('[useMedia] Target time', t, 'outside seekable range - waiting for buffer...');

                // Wait for progress event to see if more data becomes available
                const onProgress = () => {
                    if (el.seekable.length > 0) {
                        const newEnd = el.seekable.end(el.seekable.length - 1);
                        if (t <= newEnd) {
                            el.removeEventListener('progress', onProgress);
                            seek(time);
                        }
                    }
                };
                el.addEventListener('progress', onProgress, { once: true });

                // Fallback: try anyway after a short delay
                setTimeout(() => {
                    el.removeEventListener('progress', onProgress);
                    try {
                        el.currentTime = t;
                        currentTime.value = t;
                    } catch (error) {
                        // Ignore errors on fallback
                    }
                }, 500);
                return;
            }
        } else if (el.duration && el.duration > 0) {
            // No seekable ranges yet, but we have duration - wait for canplay
            const onCanPlay = () => {
                el.removeEventListener('canplay', onCanPlay);
                seek(time);
            };
            el.addEventListener('canplay', onCanPlay, { once: true });
            return;
        }

        // All checks passed, perform the seek
        try {
            el.currentTime = t;
            currentTime.value = t;
        } catch (error) {
            console.error('[useMedia] Seek error:', error);
        }
    };

    const skip = (seconds) => {
        seek(currentTime.value + seconds);
    };

    const setVolume = (val) => {
        const el = getMediaEl();
        if (!el) return;
        const v = Math.max(0, Math.min(val, 1));
        el.volume = v;
        volume.value = v;
        isMuted.value = v === 0;
    };

    const setPlaybackRate = (rate) => {
        const el = getMediaEl();
        if (!el) return;
        el.playbackRate = rate;
        playbackRate.value = rate;
    };

    const toggleLoopPoint = () => {
        const t = currentTime.value;
        const el = getMediaEl();

        if (loopRange.value.start === null) {
            loopRange.value.start = t;
        } else if (loopRange.value.end === null) {
            if (t > loopRange.value.start) {
                loopRange.value.end = t;
                loopRange.value.active = true;
                if (el) {
                    el.currentTime = loopRange.value.start;
                    el.play();
                }
            } else {
                loopRange.value.start = t;
            }
        } else {
            loopRange.value = { start: null, end: null, active: false };
        }
    };

    // Event Handlers
    const updateState = () => {
        const el = getMediaEl();
        if (el) isPlaying.value = !el.paused;
    };
    const onWaiting = () => isWaiting.value = true;
    const onPlaying = () => { isWaiting.value = false; isPlaying.value = true; };

    const onTimeUpdate = () => {
        const el = getMediaEl();
        if (!el) return;
        currentTime.value = el.currentTime;
        if (emit) emit('timeupdate', { currentTime: currentTime.value, duration: duration.value });

        // Loop Enforcement
        if (loopRange.value.active && loopRange.value.end !== null) {
            if (currentTime.value >= loopRange.value.end) {
                el.currentTime = loopRange.value.start;
            }
        }
    };

    const onLoadedMetadata = () => {
        const el = getMediaEl();
        if (!el) return;
        duration.value = el.duration;
        volume.value = el.volume;
        isMuted.value = el.muted;
        if (emit) emit('ready');
    };

    const onEnded = () => {
        if (emit) emit('ended');
    };

    const onProgress = () => {
        const el = getMediaEl();
        if (el && el.buffered.length > 0) {
            const end = el.buffered.end(el.buffered.length - 1);
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
    watch(() => getMediaEl(), (newEl, oldEl) => {
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
        const el = getMediaEl();
        if (el) {
            events.forEach(([evt, handler]) => el.removeEventListener(evt, handler));
        }
    });

    return {
        isPlaying, isMuted, isWaiting, currentTime, duration, volume, playbackRate, buffered, loopRange,
        togglePlay, play, pause, seek, skip, setVolume, setPlaybackRate, toggleLoopPoint, formatTime
    };
}
