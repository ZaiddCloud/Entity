import { ref, onUnmounted } from 'vue'
import { useEditorStore } from '@/Technologies/Store/EditorStore'
import axios from 'axios'

export function useEditorSave() {
    const store = useEditorStore()
    const autoSaveInterval = ref(null)

    const save = async () => {
        if (!store.currentEntity || store.isSaving) return

        store.isSaving = true
        try {
            console.log('Saving to server...')

            const type = store.editorMode
            const slug = store.currentContentNode?.slug

            // Prepare Payload
            let payload = {
                html: store.content, // Default fallback
                json: null,
                text: null
            }

            if (store.editor) {
                payload = {
                    html: store.editor.getHTML(),
                    json: store.editor.getJSON(),
                    text: store.editor.getText(),
                }
                // Sync local content ref to HTML to prevent "unsaved changes" flag
                store.content = payload.html
            }

            const response = await axios.post(`/studio/${type}/${slug}/save`, {
                content: payload
            })

            if (response.data.last_saved) {
                store.lastSaved = new Date(response.data.last_saved)
                // Update local node state
                if (store.currentContentNode) {
                    store.currentContentNode.content = payload.html
                }
                return true
            }
            return false
        } catch (error) {
            console.error('Save failed', error)
            alert('فشل الحفظ: ' + (error.response?.data?.message || error.message))
            return false
        } finally {
            store.isSaving = false
        }
    }

    const startAutoSave = () => {
        if (autoSaveInterval.value) clearInterval(autoSaveInterval.value)
        autoSaveInterval.value = setInterval(() => {
            if (store.hasUnsavedChanges) {
                save()
            }
        }, 30000)
    }

    const stopAutoSave = () => {
        if (autoSaveInterval.value) {
            clearInterval(autoSaveInterval.value)
            autoSaveInterval.value = null
        }
    }

    onUnmounted(() => {
        stopAutoSave()
    })

    return {
        save,
        startAutoSave,
        stopAutoSave
    }
}
