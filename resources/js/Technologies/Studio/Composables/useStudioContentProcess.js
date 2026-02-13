import { useEditorStore } from '../../Store/EditorStore'
import { useMediaStore } from '../../Store/MediaStore'
import axios from 'axios'

/**
 * Step 2: useStudioContentProcess (The Orchestrator) 🎼
 * 
 * Orchestrates the "Universal Execution" across Editor, Player, and Backend.
 */
export function useStudioContentProcess() {
    const editorStore = useEditorStore()
    const mediaStore = useMediaStore()

    const insertNode = async (type, title, time = null) => {
        if (!editorStore.currentEntity) return

        try {
            // 1. Optimistic UI: Emit Event (Step 4 Requirement)
            window.dispatchEvent(new CustomEvent('studio:insert-node', {
                detail: { type, title, time }
            }))

            // 2. Media Store Update (Step 2 Requirement)
            if (time !== null && (editorStore.editorMode === 'audio' || editorStore.editorMode === 'video')) {
                mediaStore.addSegment && mediaStore.addSegment({
                    title: title,
                    start: time
                })
            }

            // 3. Persistence (Step 5 Requirement)
            console.log('[useStudioContentProcess] Sending persistence request...')

            const response = await axios.post(`/studio/${editorStore.editorMode}/${editorStore.currentEntity.slug}/nodes`, {
                type: type,
                title: title,
                time: time
            })

            console.log('[useStudioContentProcess] Persistence successful:', response.data.message)

            // Step 5 Result: Ensure redirect on success
            if (response.data.redirect) {
                // Use router.visit to maintain Inertia state if possible, or window.location for full reload
                window.location.href = response.data.redirect
            }

        } catch (error) {
            console.error('[useStudioContentProcess] Integration failed:', error)
            // Rollback/Notification logic would go here in a production app
        }
    }

    return {
        insertNode
    }
}
