import { useEditorStore } from '../../Store/EditorStore'
import { useMediaStore } from '@/Technologies/Store/MediaStore'
import { useTiptapStore } from '../../Editor/Core/TiptapStore'
import { router } from '@inertiajs/vue3'

/**
 * Step 2: useStudioContentProcess (The Orchestrator) 🎼
 * 
 * Orchestrates the "Universal Execution" across Editor, Player, and Backend.
 */
export function useStudioContentProcess() {
    const editorStore = useEditorStore()
    const mediaStore = useMediaStore()
    const tiptapStore = useTiptapStore()

    const findParentIdFromEditor = () => {
        const editor = tiptapStore.editor
        if (!editor) return null

        const { state } = editor
        const { selection } = state
        const { $from } = selection

        // Search upwards for a node with data-id
        for (let depth = $from.depth; depth >= 0; depth--) {
            const node = $from.node(depth)
            if (node.attrs && node.attrs['data-id']) {
                console.log('[useStudioContentProcess] Detected Parent Node:', node.attrs.type, node.attrs['data-id'])
                return node.attrs['data-id']
            }
        }

        return null
    }

    const insertNode = async (type, title, time = null) => {
        if (!editorStore.currentEntity) return

        const parentId = findParentIdFromEditor()

        try {
            // 1. Optimistic UI: REMOVED (Step 29 Requirement)
            // Relying exclusively on server-side reload as Source of Truth.

            // 2. Media Store Update (Step 2 Requirement)
            if (time !== null && (editorStore.editorMode === 'audio' || editorStore.editorMode === 'video')) {
                // Feature Refinement: Do NOT activate segment automatically when adding structural nodes
                mediaStore.addSegment && mediaStore.addSegment({
                    title: title,
                    start: time
                }, { setActive: false })
            }

            // 3. Persistence (Step 5 Requirement)
            console.log('[useStudioContentProcess] Sending persistence request...')

            const response = await axios.post(`/studio/${editorStore.editorMode}/${editorStore.currentEntity.slug}/nodes`, {
                type: type,
                title: title,
                time: time,
                parent_id: parentId
            })

            console.log('[useStudioContentProcess] Persistence successful:', response.data.message)

            // Step 15 Refinement: Stay on Full Content!
            // Instead of redirecting to the new node, we just reload the data to refresh everything.
            router.reload({
                only: ['entity', 'editorContent', 'fullContent', 'hierarchy'],
                onSuccess: () => {
                    console.log('[useStudioContentProcess] Navigation Success (Reloaded Data)')
                }
            })

        } catch (error) {
            console.error('[useStudioContentProcess] Integration failed:', error)
            // Rollback/Notification logic would go here in a production app
        }
    }

    return {
        editorStore,
        mediaStore,
        insertNode
    }
}
