import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useResilientSync } from '@/Core/Sync/useResilientSync'
import { registerAccess } from '@/Core/Storage/quotaManager';
import { predictNextMoves } from '@/Core/Sync/cachingStrategy';
import { usePage } from '@inertiajs/vue3'; // For user ID
import { usePresence } from '@/Core/Sync/usePresence';
import { useSoftLock } from '@/Core/Sync/useSoftLock';

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

    // Presence & Soft Locking
    const presence = usePresence()
    const softLock = useSoftLock()

    // Getters
    const documentTitle = computed(() => currentContentNode.value?.title || 'مستند جديد')

    const hasUnsavedChanges = computed(() => {
        return content.value !== (currentContentNode.value?.content || '')
    })

    // Actions
    const setEditor = (editorInstance) => {
        editor.value = editorInstance
    }

    const loadDocument = async (entity, contentNode, hierarchyData = [], navigationData = {}) => {
        currentEntity.value = entity
        currentContentNode.value = contentNode
        hierarchy.value = hierarchyData
        navigation.value = navigationData
        contentVersion.value = 0

        // Join presence for this entity
        if (entity && entity.slug) {
            presence.join(editorMode.value, entity.slug)
        }

        // --- LOCAL-FIRST LOADING (Phase 13 Enhancement) ---
        // Check IndexedDB first for unsaved changes
        const { loadEntity } = useResilientSync()
        let localVersion = null

        try {
            const entityId = entity.id || entity.slug
            const childId = contentNode.id === 'full' ? 'full' : (contentNode._id || contentNode.id)

            localVersion = await loadEntity(entityId, editorMode.value, childId)

            if (localVersion && localVersion.content) {
                // Use local version if it exists (may have unsaved changes)
                content.value = localVersion.content
                console.log('[EditorStore] 📦 Loaded from IndexedDB (local-first)')
            } else {
                // Fallback to server data
                content.value = contentNode.content || ''
                console.log('[EditorStore] 🌐 Loaded from server')
            }
        } catch (e) {
            console.warn('[EditorStore] Local load failed, using server data:', e)
            content.value = contentNode.content || ''
        }

        // --- PRODUCTION INTEGRATION (TOUCH 8) ---
        // 1. Register Access for LRU
        try {
            const user = usePage().props.auth.user;
            if (user && entity.id) {
                registerAccess(entity.id, user.id);
            }
        } catch (e) { console.warn('LRU registration skipped', e); }

        // 2. Predictive Caching (Pre-fetch next chapter/page)
        if (entity) {
            predictNextMoves({
                ...entity,
                editorMode: editorMode.value,
                order: contentNode?.order,
                parent_id: entity.parent_id || entity.id,
                navigation: navigationData
            }).then(predictions => {
                if (predictions.length > 0) {
                    prefetchEntities(predictions);
                }
            });
        }
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

        const { saveEntity } = useResilientSync()
        isSaving.value = true

        try {
            const childId = currentContentNode.value.id === 'full' ? 'full' : (currentContentNode.value._id || currentContentNode.value.id)

            const payload = {
                id: currentEntity.value.id || currentEntity.value.slug,
                type: editorMode.value,
                child_id: childId,
                title: currentContentNode.value.title,
                content: content.value,
                html_content: content.value,
                plain_text: editor.value?.getText() || '',
                json_content: editor.value?.getJSON() || null,

                // --- Sync Metadata ---
                method: 'POST',
                url: route('studio.save', {
                    type: editorMode.value,
                    slug: currentEntity.value.slug,
                    childId: childId
                })
            }

            // --- SMART SPLITTING FOR FULL VIEW ---
            if (childId === 'full' && editor.value) {
                const doc = editor.value.state.doc
                const segments = []
                let currentSegment = null

                doc.forEach((node, offset, index) => {
                    // Robust Header Detection: Strict match for <p><strong>Title:</strong></p>
                    // Must be a paragraph with single text node, bold, ending with colon.
                    let isHeader = false;

                    if (node.type.name === 'paragraph') {
                        // Priority: Explicit SegmentLink Mark
                        if (node.firstChild?.marks?.some(m => m.type.name === 'segmentLink')) {
                            isHeader = true;
                        }
                        // Fallback: Structure Matching (Bold + Colon)
                        else if (node.content.size === 1 && node.firstChild?.type.name === 'text') {
                            const text = node.firstChild.text;
                            const hasBold = node.firstChild.marks?.some(m => m.type.name === 'bold');

                            if (hasBold && text.trim().endsWith(':')) {
                                isHeader = true;
                            }
                        }
                    }

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
                    json: seg.nodes.map(node => node.toJSON())
                }))
            }

            // Use the resilient save (Optimistic + Queue)
            await saveEntity(payload)

            lastSaved.value = new Date()
            console.log('[EditorStore] Content queued for sync successfully')

            if (window.notifySync) {
                window.notifySync(`✅ تم حفظ "${documentTitle.value}" محلياً: سيتم المزامنة تلقائياً`, 'success')
            }

            // Sync with server to refresh meta components (presence, header, etc)
            router.reload({ only: ['entity'] })

        } catch (error) {
            console.error('[EditorStore] Save failed:', error)
            window.notifySync?.('❌ فشل الحفظ المحلي: تأكد من مساحة التخزين', 'error')
            throw error
        } finally {
            isSaving.value = false
        }
    }

    const cleanup = () => {
        presence.leave()
        softLock.stopMonitoring()
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
        contentVersion,
        presence, softLock, cleanup
    }
})
