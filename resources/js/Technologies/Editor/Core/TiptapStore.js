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
            code: () => chain.toggleCode().run(),
            codeBlock: () => chain.toggleCodeBlock().run(),
            blockquote: () => chain.toggleBlockquote().run(),
            horizontalRule: () => chain.setHorizontalRule().run(),
            undo: () => chain.undo().run(),
            redo: () => chain.redo().run(),
            textAlign: () => chain.setTextAlign(value).run(),

            // Extended Formatting
            subscript: () => chain.toggleSubscript().run(),
            superscript: () => chain.toggleSuperscript().run(),
            highlight: () => chain.toggleHighlight().run(),
            unsetAllMarks: () => chain.unsetAllMarks().run(),
            clearNodes: () => chain.clearNodes().run(),

            // Insertables
            setLink: () => chain.setLink({ href: value }).run(),
            unsetLink: () => chain.unsetLink().run(),
            setImage: () => chain.setImage({ src: value }).run(),

            // Tables
            insertTable: () => chain.insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run(),
            deleteTable: () => chain.deleteTable().run(),
            addColumnBefore: () => chain.addColumnBefore().run(),
            addColumnAfter: () => chain.addColumnAfter().run(),
            deleteColumn: () => chain.deleteColumn().run(),
            addRowBefore: () => chain.addRowBefore().run(),
            addRowAfter: () => chain.addRowAfter().run(),
            deleteRow: () => chain.deleteRow().run(),
            mergeCells: () => chain.mergeCells().run(),
            splitCell: () => chain.splitCell().run(),

            insertHeritagePoetry: () => chain.setHeritagePoetry().run(),
            insertQuranicVerse: () => chain.setQuranicVerse().run(),
            insertFootnote: () => chain.insertFootnote().run(),
            setFootnote: () => chain.setFootnote(value).run(),
        }

        if (commands[command]) {
            commands[command]()
        } else if (command === 'textAlign') {
            chain.setTextAlign(value).run()
        } else if (command === 'setLink') {
            chain.setLink({ href: value }).run()
        } else if (command === 'setImage') {
            chain.setImage({ src: value }).run()
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
