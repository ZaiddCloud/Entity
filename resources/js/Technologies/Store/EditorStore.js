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
    const contentVersion = ref(0)

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
        contentVersion.value = 0
    }

    const updateContent = (newContent) => {
        content.value = newContent
        contentVersion.value++
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

    const save = async () => {
        if (!currentContentNode.value || !currentEntity.value) {
            console.error('[EditorStore] Cannot save: missing entity or content node')
            return
        }

        isSaving.value = true

        try {
            const childId = currentContentNode.value.id === 'full' ? 'full' : (currentContentNode.value._id || currentContentNode.value.id)

            const payload = {
                child_id: childId,
                title: currentContentNode.value.title,
                content: content.value,
                html_content: content.value,
                plain_text: editor.value?.getText() || '',
                json_content: editor.value?.getJSON() || null
            }

            // --- SMART SPLITTING FOR FULL VIEW ---
            if (childId === 'full' && editor.value) {
                const doc = editor.value.state.doc
                const segments = []
                let currentSegment = null

                doc.forEach((node, offset, index) => {
                    // Precision Detection: Top-level paragraph containing a segmentLink mark
                    const isHeader = node.type.name === 'paragraph' &&
                        (node.firstChild?.marks?.some(m => m.type.name === 'segmentLink') || false)

                    if (isHeader) {
                        currentSegment = {
                            title: node.textContent.trim().replace(/:$/, ''),
                            nodes: []
                        }
                        segments.push(currentSegment)
                    } else if (currentSegment) {
                        currentSegment.nodes.push(node)
                    }
                })

                payload.segments = segments.map(seg => ({
                    title: seg.title,
                    // Send as a proper Tiptap/ProseMirror content array
                    json: seg.nodes.map(node => node.toJSON())
                }))
            }

            const response = await axios.post(
                route('studio.save', {
                    type: editorMode.value,
                    slug: currentEntity.value.slug,
                    childId: childId
                }),
                payload
            )

            lastSaved.value = new Date()
            console.log('[EditorStore] Content saved successfully')
        } catch (error) {
            console.error('[EditorStore] Save failed:', error)
            throw error
        } finally {
            isSaving.value = false
        }
    }

    return {
        currentEntity, currentContentNode, content, isToolbarPinned,
        editorMode, resourceData, isSaving, lastSaved, editor,
        hierarchy, navigation,
        documentTitle, hasUnsavedChanges,
        setEditor, loadDocument, updateContent, togglePin,
        executeCommand, isActive,
        setEditorMode, setResourceData, setTitle,
        addMediaNode, removeMediaNode,
        save,
        contentVersion
    }
})
