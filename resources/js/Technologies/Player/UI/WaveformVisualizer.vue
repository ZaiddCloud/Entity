<script setup>
import { onMounted, onUnmounted, ref, watch, nextTick } from 'vue'
import WaveSurfer from 'wavesurfer.js'

const props = defineProps({
    src: { type: String, required: true },
    isPlaying: { type: Boolean, default: false },
    currentTime: { type: Number, default: 0 },
    height: { type: Number, default: 64 },
    barWidth: { type: Number, default: 2 },
    barGap: { type: Number, default: 2 },
    progressColor: { type: String, default: '#3b82f6' },
    waveColor: { type: String, default: 'rgba(255, 255, 255, 0.2)' },
    cursorColor: { type: String, default: 'transparent' }
})

const emit = defineEmits(['seek', 'ready'])

const containerRef = ref(null)
const wavesurfer = ref(null)
const isReady = ref(false)

const initWaveSurfer = () => {
    if (!containerRef.value) return 

    // cleanup if exists
    if (wavesurfer.value) wavesurfer.value.destroy()

    wavesurfer.value = WaveSurfer.create({
        container: containerRef.value,
        waveColor: props.waveColor,
        progressColor: props.progressColor,
        cursorColor: props.cursorColor,
        barWidth: props.barWidth,
        barGap: props.barGap,
        barRadius: 2,
        height: props.height,
        responsive: true,
        normalize: true,
        // partialRender: true, // Optimzation for large files?
        url: props.src
    })

    wavesurfer.value.on('ready', () => {
        isReady.value = true
        emit('ready', wavesurfer.value.getDuration())
        // Sync initial state
        if (props.isPlaying) wavesurfer.value.play()
        // Sync time if needed (though audio/video element usually drivers this)
    })

    wavesurfer.value.on('interaction', (newTime) => {
        emit('seek', newTime)
    })
    
    // We want the wavesurfer to just be a visualizer, driven by the main audio element?
    // If VideoScreen uses an <audio> tag, we can bind WaveSurfer to it using 'media' option!
    // But VideoScreen manages its own ref. 
    // Option A: Use WaveSurfer as the player.
    // Option B: Use WaveSurfer purely as visualizer synced to props.
    // Option B allows us to keep VideoScreen logic intact.
    
    // For Option B, we mute wavesurfer or just set volume 0 and sync time?
    // Actually, passing the media element is best for sync.
    // But props.src is string. We don't have the media element ref passed in effectively until mounted?
    // Let's try separate sync first (Option B) for flexibility, or ask VideoScreen to pass element.
    
    wavesurfer.value.setVolume(0) // Mute internal player to avoid echo if external player exists
}

watch(() => props.src, () => {
    isReady.value = false
    initWaveSurfer()
})

watch(() => props.isPlaying, (playing) => {
    if (!wavesurfer.value || !isReady.value) return
    // We don't necessarily want wavesurfer to play audio, just animate?
    // If we rely on currentTime prop, we don't need play().
    // EXCEPT: seekTo only moves head. To animate, we need consistent updates.
})

watch(() => props.currentTime, (time) => {
    if (!wavesurfer.value || !isReady.value) return
    // Avoid seeking loops if interaction triggered this
    const current = wavesurfer.value.getCurrentTime()
    if (Math.abs(current - time) > 0.1) {
        // Use seekTo (0..1) or setTime? v7 has setTime
        wavesurfer.value.setTime(time)
    }
})

onMounted(() => {
    nextTick(initWaveSurfer)
})

onUnmounted(() => {
    if (wavesurfer.value) wavesurfer.value.destroy()
})
</script>

<template>
    <div ref="containerRef" class="w-full relative z-10" dir="ltr"></div>
</template>
