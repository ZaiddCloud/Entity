import { describe, it, expect, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useEditorStore } from '../../Store/editorStore'

describe('Editor Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    it('initializes with default values', () => {
        const store = useEditorStore()
        expect(store.editorMode).toBe('book')
        expect(store.resourceData).toBeNull()
        expect(store.content).toBe('')
    })

    it('sets editor mode correctly', () => {
        const store = useEditorStore()
        store.setEditorMode('manuscript')
        expect(store.editorMode).toBe('manuscript')
    })

    it('sets resource data correctly', () => {
        const store = useEditorStore()
        const data = { url: 'test.pdf' }
        store.setResourceData(data)
        expect(store.resourceData).toEqual(data)
    })

    it('loads document correctly', () => {
        const store = useEditorStore()
        const book = { title: 'Book' }
        const child = { title: 'Chapter', content: 'Hello' }
        store.loadDocument(book, child)
        expect(store.currentBook).toEqual(book)
        expect(store.currentChild).toEqual(child)
        expect(store.content).toBe('Hello')
    })

    it('generates correct document title', () => {
        const store = useEditorStore()
        store.loadDocument({}, { title: 'Test' })
        expect(store.documentTitle).toBe('Test')
    })
})
