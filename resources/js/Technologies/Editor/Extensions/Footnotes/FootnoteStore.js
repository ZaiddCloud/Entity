import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useFootnoteStore = defineStore('footnote', () => {
    // State
    const isOpen = ref(false)
    const activeFootnoteId = ref(null)
    const activeFootnoteType = ref('comment')
    const activeFootnoteContent = ref(null) // Tiptap JSON
    const activeEditor = ref(null) // Reference to main editor

    const activeRange = ref(null)

    // Actions
    const openEditor = (editor, footnoteId, initialType, initialContent) => {
        activeEditor.value = editor
        activeFootnoteId.value = footnoteId
        activeFootnoteType.value = initialType || 'comment'
        activeFootnoteContent.value = initialContent || null

        // Capture current selection or find mark range if possible
        // Ideally we assume the user just clicked the mark, so selection is near/in it
        const { from, to } = editor.state.selection
        activeRange.value = { from, to }

        isOpen.value = true
    }

    const closeEditor = () => {
        isOpen.value = false
        activeFootnoteId.value = null
        activeEditor.value = null
        activeRange.value = null
    }

    const saveFootnote = (contentJson, type) => {
        if (!activeEditor.value || !activeFootnoteId.value) return

        let chain = activeEditor.value.chain().focus()

        // Restore selection if we have it
        if (activeRange.value) {
            chain = chain.setTextSelection(activeRange.value)
        }

        chain
            .extendMarkRange('scientificFootnote')
            .updateAttributes('scientificFootnote', {
                id: activeFootnoteId.value,
                type: type,
                content_json: contentJson,
                preview: extractPreview(contentJson)
            })
            .run()

        closeEditor()
    }

    // Helper to extract plain text preview from JSON
    const extractPreview = (json) => {
        if (!json) return ''
        try {
            // Simple DFS to get text
            let text = ''
            const traverse = (node) => {
                if (node.text) text += node.text + ' '
                if (node.content) node.content.forEach(traverse)
            }
            traverse(json)
            return text.substring(0, 50) + (text.length > 50 ? '...' : '')
        } catch (e) {
            return ''
        }
    }

    return {
        isOpen,
        activeFootnoteId,
        activeFootnoteType,
        activeFootnoteContent,
        openEditor,
        closeEditor,
        saveFootnote
    }
})
