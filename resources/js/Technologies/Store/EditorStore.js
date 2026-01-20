import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useEditorStore = defineStore('editor', () => {
    // State
    const currentEntity = ref(null)
    const currentContentNode = ref(null)
    const content = ref('')
    const isToolbarPinned = ref(false)
    const editorMode = ref('book')
    const isSaving = ref(false)
    const lastSaved = ref(null)
    const editor = ref(null)
    const resourceData = ref(null)
    const hierarchy = ref([])
    const navigation = ref({ prev: null, next: null })

    // Getters
    const documentTitle = computed(() => currentContentNode.value?.title || 'مستند جديد')

    const hasUnsavedChanges = computed(() => {
        return content.value !== (currentContentNode.value?.content || '')
    })

    // Actions
    const setEditor = (editorInstance) => {
        editor.value = editorInstance
    }

    const loadDocument = (entity, contentNode, hierarchyData = [], navigationData = {}) => {
        currentEntity.value = entity
        currentContentNode.value = contentNode
        content.value = contentNode.content || ''
        hierarchy.value = hierarchyData
        navigation.value = navigationData
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
        if (currentContentNode.value) currentContentNode.value.title = title
    }

    const addMediaNode = () => {
        if (!Array.isArray(content.value)) {
            content.value = []
        }

        if (editorMode.value === 'audio') {
            content.value = [...content.value, {
                id: Date.now(),
                startTime: '00:00',
                endTime: '00:00',
                label: 'مقطع جديد',
                text: ''
            }]
        } else if (editorMode.value === 'video') {
            content.value = [...content.value, {
                id: Date.now(),
                timestamp: '00:00',
                title: 'مشهد جديد',
                description: ''
            }]
        }
    }

    const removeMediaNode = (index) => {
        if (!Array.isArray(content.value)) return
        const newContent = [...content.value]
        newContent.splice(index, 1)
        content.value = newContent
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

    return {
        currentEntity, currentContentNode, content, isToolbarPinned,
        editorMode, resourceData, isSaving, lastSaved, editor,
        hierarchy, navigation,
        documentTitle, hasUnsavedChanges,
        setEditor, loadDocument, updateContent, togglePin,
        executeCommand, isActive,
        setEditorMode, setResourceData, setTitle,
        addMediaNode, removeMediaNode
    }
})
