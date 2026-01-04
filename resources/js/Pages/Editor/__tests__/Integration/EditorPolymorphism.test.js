import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import EditorPage from '../../EditorPage.vue'
import { useEditorStore } from '../../Store/editorStore'

// Mock child components to simplify testing
vi.mock('../../Components/Toolbar/EditorToolbar.vue', () => ({
    default: { template: '<div data-testid="editor-toolbar"></div>' }
}))
vi.mock('../../Components/Content/TiptapEditor.vue', () => ({
    default: { template: '<div data-testid="tiptap-editor"></div>' }
}))

// Note: We removed the mocks for ManuscriptViewer and MediaPlayer to allow the real components (which are now statically imported) to render.
// This ensures findComponent works as expected against the real simple components.

describe('EditorPage Polymorphism', () => {
    let pinia

    beforeEach(() => {
        pinia = createPinia()
        setActivePinia(pinia)
    })

    const mockProps = {
        book: { id: 1, title: 'Test Book', slug: 'test-book' },
        child: { id: 1, title: 'Test Chapter', slug: 'test-chapter', content: '' }
    }

    it('defaults to book mode (only editor visible)', async () => {
        const wrapper = mount(EditorPage, {
            props: mockProps,
            global: {
                plugins: [pinia],
                stubs: {
                    EditorLayout: false
                }
            }
        })

        await wrapper.vm.$nextTick()

        // Should show editor
        expect(wrapper.find('[data-testid="tiptap-editor"]').exists()).toBe(true)
        // Should NOT show viewers
        expect(wrapper.findComponent({ name: 'ManuscriptViewer' }).exists()).toBe(false)
        expect(wrapper.findComponent({ name: 'MediaPlayer' }).exists()).toBe(false)
    })

    it('renders manuscript viewer in manuscript mode', async () => {
        const wrapper = mount(EditorPage, {
            props: {
                ...mockProps,
                editor_mode: 'manuscript'
            },
            global: {
                plugins: [pinia],
                stubs: {
                    EditorLayout: false
                }
            }
        })

        await wrapper.vm.$nextTick()
        // Wait specifically for the v-if to update
        await wrapper.vm.$nextTick()

        // Should show BOTH editor and viewer
        expect(wrapper.find('[data-testid="tiptap-editor"]').exists()).toBe(true)
        expect(wrapper.findComponent({ name: 'ManuscriptViewer' }).exists()).toBe(true)
    })

    it('renders media player in audio mode', async () => {
        const wrapper = mount(EditorPage, {
            props: {
                ...mockProps,
                editor_mode: 'audio'
            },
            global: {
                plugins: [pinia],
                stubs: {
                    EditorLayout: false
                }
            }
        })

        await wrapper.vm.$nextTick()
        await wrapper.vm.$nextTick()

        // Should show BOTH editor and player
        expect(wrapper.find('[data-testid="tiptap-editor"]').exists()).toBe(true)
        expect(wrapper.findComponent({ name: 'MediaPlayer' }).exists()).toBe(true)
    })
})
