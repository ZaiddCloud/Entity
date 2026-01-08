import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useMediaStore = defineStore('media', () => {
    const segments = ref([])
    const mode = ref('audio') // 'audio' or 'video'

    const setMode = (newMode) => {
        mode.value = newMode
    }

    const setSegments = (data) => {
        if (Array.isArray(data)) {
            segments.value = data
        } else {
            segments.value = []
        }
    }

    const addSegment = () => {
        if (mode.value === 'audio') {
            segments.value.push({
                id: Date.now(),
                startTime: '00:00',
                endTime: '00:00',
                label: 'مقطع جديد',
                text: ''
            })
        } else {
            segments.value.push({
                id: Date.now(),
                timestamp: '00:00',
                title: 'مشهد جديد',
                description: ''
            })
        }
    }

    const removeSegment = (index) => {
        segments.value.splice(index, 1)
    }

    const updateSegment = (index, data) => {
        if (segments.value[index]) {
            segments.value[index] = { ...segments.value[index], ...data }
        }
    }

    return {
        segments,
        mode,
        setMode,
        setSegments,
        addSegment,
        removeSegment,
        updateSegment
    }
})
