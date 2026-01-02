import { ref, onUnmounted } from 'vue'
import { useEditorStore } from '../../Store/editorStore'

export function useAutoSave(interval = 30000) {
    const store = useEditorStore()
    const autoSaveTimer = ref(null)
    const isEnabled = ref(true)

    const start = () => {
        if (autoSaveTimer.value) return

        autoSaveTimer.value = setInterval(() => {
            if (isEnabled.value && store.hasUnsavedChanges) {
                store.save()
            }
        }, interval)
    }

    const stop = () => {
        if (autoSaveTimer.value) {
            clearInterval(autoSaveTimer.value)
            autoSaveTimer.value = null
        }
    }

    const toggle = () => {
        isEnabled.value = !isEnabled.value
        if (isEnabled.value) {
            start()
        } else {
            stop()
        }
    }

    onUnmounted(() => {
        stop()
    })

    return {
        isEnabled,
        start,
        stop,
        toggle
    }
}
