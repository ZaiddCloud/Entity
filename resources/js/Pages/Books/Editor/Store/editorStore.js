import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useEditorStore = defineStore('editor', () => {
    // State
    const currentBook = ref(null)
    const currentChild = ref(null)
    const content = ref('')
    const isToolbarPinned = ref(false)
    const editorMode = ref('book')
    const isSaving = ref(false)
    const lastSaved = ref(null)
    const editor = ref(null)
    const resourceData = ref(null)

    // Getters
    const documentTitle = computed(() => currentChild.value?.title || 'مستند جديد')

    const hasUnsavedChanges = computed(() => {
        return content.value !== (currentChild.value?.content || '')
    })

    // Actions
    const setEditor = (editorInstance) => {
        editor.value = editorInstance
    }

    const loadDocument = (book, child) => {
        currentBook.value = book
        currentChild.value = child
        content.value = child.content || ''
    }

    const updateContent = (newContent) => {
        content.value = newContent
    }

    const togglePin = () => {
        isToolbarPinned.value = !isToolbarPinned.value
    }

    const setEditorMode = (mode) => {
        editorMode.value = mode
        resourceData.value = null
    }

    const setResourceData = (data) => {
        resourceData.value = data
    }

    const setTitle = (title) => {
        if (currentChild.value) currentChild.value.title = title
    }

    const executeCommand = (command, value = null) => {
        if (!editor.value) return

        const chain = editor.value.chain().focus()

        const commands = {
            bold: () => chain.toggleBold().run(),
            italic: () => chain.toggleItalic().run(),
            underline: () => chain.toggleUnderline().run(),
            heading: () => chain.toggleHeading({ level: value }).run(),
            bulletList: () => chain.toggleBulletList().run(),
            orderedList: () => chain.toggleOrderedList().run(),
            strike: () => chain.toggleStrike().run(),
            undo: () => chain.undo().run(),
            redo: () => chain.redo().run(),
            alignRight: () => chain.setTextAlign('right').run(),
            alignCenter: () => chain.setTextAlign('center').run(),
            alignLeft: () => chain.setTextAlign('left').run(),
            alignJustify: () => chain.setTextAlign('justify').run(),
            insertHeritagePoetry: () => chain.setHeritagePoetry().run(),
            insertQuranicVerse: () => chain.setQuranicVerse().run(),
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

    const getSavePayload = () => {
        if (!editor.value) return null

        let resourceId = null
        if (editorMode.value === 'book') {
            resourceId = currentBook.value?.id
        } else {
            resourceId = resourceData.value?.id
        }

        return {
            mode: editorMode.value,
            resource_id: resourceId,
            content: editor.value.getJSON(),
            title: documentTitle.value
        }
    }

    const save = async () => {
        if (!currentBook.value || isSaving.value) return

        isSaving.value = true
        try {
            console.log('Saving to server...', content.value)
            lastSaved.value = new Date()
            if (currentChild.value) {
                currentChild.value.content = content.value
            }
            return true
        } catch (error) {
            console.error('Save failed', error)
            return false
        } finally {
            isSaving.value = false
        }
    }

    let autoSaveInterval = null
    const startAutoSave = () => {
        autoSaveInterval = setInterval(() => {
            if (hasUnsavedChanges.value) save()
        }, 30000)
    }

    const stopAutoSave = () => {
        if (autoSaveInterval) clearInterval(autoSaveInterval)
    }

    return {
        currentBook, currentChild, content, isToolbarPinned,
        editorMode, resourceData, isSaving, lastSaved, editor,
        documentTitle, hasUnsavedChanges,
        setEditor, loadDocument, updateContent, togglePin,
        executeCommand, isActive, save, startAutoSave, stopAutoSave,
        setEditorMode, setResourceData, getSavePayload, setTitle
    }
})
