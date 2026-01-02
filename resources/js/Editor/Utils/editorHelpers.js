/**
 * Helper functions for editor operations
 */

/**
 * Check if editor has selection
 */
export function hasSelection(editor) {
    if (!editor) return false
    const { from, to } = editor.state.selection
    return from !== to
}

/**
 * Get selected text
 */
export function getSelectedText(editor) {
    if (!editor || !hasSelection(editor)) return ''
    const { from, to } = editor.state.selection
    return editor.state.doc.textBetween(from, to, ' ')
}

/**
 * Insert text at cursor
 */
export function insertText(editor, text) {
    if (!editor) return
    editor.chain().focus().insertContent(text).run()
}

/**
 * Get current node type
 */
export function getCurrentNodeType(editor) {
    if (!editor) return null
    return editor.state.selection.$from.parent.type.name
}

/**
 * Check if current node is of type
 */
export function isNodeType(editor, typeName) {
    return getCurrentNodeType(editor) === typeName
}

/**
 * Get editor statistics
 */
export function getEditorStats(editor) {
    if (!editor) return { words: 0, characters: 0, paragraphs: 0 }

    const text = editor.getText()
    const words = text.trim().split(/\s+/).filter(Boolean).length
    const characters = text.length

    let paragraphs = 0
    editor.state.doc.descendants((node) => {
        if (node.type.name === 'paragraph') paragraphs++
    })

    return { words, characters, paragraphs }
}

/**
 * Clear editor content
 */
export function clearContent(editor) {
    if (!editor) return
    editor.chain().focus().clearContent().run()
}

/**
 * Set editor content
 */
export function setContent(editor, content) {
    if (!editor) return
    editor.commands.setContent(content)
}
