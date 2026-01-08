import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useTiptapStore = defineStore('tiptap', () => {
    const editor = ref(null)
    const content = ref('')

    const setEditor = (instance) => {
        editor.value = instance
    }

    const updateContent = (newContent) => {
        content.value = newContent
    }

    const executeCommand = (command, value = null) => {
        if (!editor.value) return

        const chain = editor.value.chain().focus()

        const commands = {
            bold: () => chain.toggleBold().run(),
            italic: () => chain.toggleItalic().run(),
            underline: () => chain.toggleUnderline().run(),
            strike: () => chain.toggleStrike().run(),
            heading: () => chain.toggleHeading({ level: value }).run(),
            setParagraph: () => chain.setParagraph().run(),
            bulletList: () => chain.toggleBulletList().run(),
            orderedList: () => chain.toggleOrderedList().run(),
            undo: () => chain.undo().run(),
            redo: () => chain.redo().run(),
            textAlign: () => chain.setTextAlign(value).run(),
            insertHeritagePoetry: () => chain.setHeritagePoetry().run(),
            insertQuranicVerse: () => chain.setQuranicVerse().run(),
            insertFootnote: () => chain.insertFootnote().run(),
        }

        if (commands[command]) {
            commands[command]()
        } else if (command === 'textAlign') {
            chain.setTextAlign(value).run()
        }
    }

    const isActive = (name, attributes = {}) => {
        if (!editor.value) return false
        return editor.value.isActive(name, attributes)
    }

    return {
        editor,
        content,
        setEditor,
        updateContent,
        executeCommand,
        isActive
    }
})
