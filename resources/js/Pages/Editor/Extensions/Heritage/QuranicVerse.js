import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import QuranicVerseView from '../Views/QuranicVerseView.vue'

export const QuranicVerse = Node.create({
    name: 'quranicVerse',

    group: 'block',

    content: 'inline*',

    addAttributes() {
        return {
            text: {
                default: '',
            },
            surah: {
                default: '',
            },
            ayah: {
                default: '',
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-type="quranic-verse"]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(HTMLAttributes, { 'data-type': 'quranic-verse' }), 0]
    },

    addNodeView() {
        return VueNodeViewRenderer(QuranicVerseView)
    },

    addCommands() {
        return {
            setQuranicVerse: (attributes) => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                    attrs: attributes,
                })
            },
        }
    },
})

export default QuranicVerse
