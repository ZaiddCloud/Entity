import { onMounted, onUnmounted } from 'vue'
import { KEYBOARD_SHORTCUTS } from '../../Constants/keyboardShortcuts'

export function useKeyboardShortcuts(editor, customHandlers = {}) {
    const defaultHandlers = {
        [KEYBOARD_SHORTCUTS.SAVE]: (e) => {
            e.preventDefault()
            customHandlers.save?.()
        },
        [KEYBOARD_SHORTCUTS.BOLD]: (e) => {
            e.preventDefault()
            editor.value?.chain().focus().toggleBold().run()
        },
        [KEYBOARD_SHORTCUTS.ITALIC]: (e) => {
            e.preventDefault()
            editor.value?.chain().focus().toggleItalic().run()
        },
        [KEYBOARD_SHORTCUTS.UNDERLINE]: (e) => {
            e.preventDefault()
            editor.value?.chain().focus().toggleUnderline().run()
        },
        [KEYBOARD_SHORTCUTS.UNDO]: (e) => {
            e.preventDefault()
            editor.value?.chain().focus().undo().run()
        },
        [KEYBOARD_SHORTCUTS.REDO]: (e) => {
            e.preventDefault()
            editor.value?.chain().focus().redo().run()
        },
    }

    const handleKeyDown = (e) => {
        const isMod = e.ctrlKey || e.metaKey
        const key = e.key.toLowerCase()

        // Build shortcut string (e.g., "Mod-s")
        let shortcut = ''
        if (isMod) shortcut += 'Mod-'
        if (e.shiftKey) shortcut += 'Shift-'
        if (e.altKey) shortcut += 'Alt-'
        shortcut += key

        const handler = defaultHandlers[shortcut] || customHandlers[shortcut]
        if (handler) {
            handler(e)
        }
    }

    onMounted(() => {
        document.addEventListener('keydown', handleKeyDown)
    })

    onUnmounted(() => {
        document.removeEventListener('keydown', handleKeyDown)
    })

    return {
        handleKeyDown
    }
}
