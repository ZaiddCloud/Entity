import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import PoetryNodeView from '../../Components/Extensions/Views/PoetryNodeView.vue'

export const HeritagePoetry = Node.create({
    name: 'heritagePoetry',

    group: 'block',

    content: 'inline*',

    addAttributes() {
        return {
            sadr: {
                default: '',
            },
            ajuz: {
                default: '',
            },
            poet: {
                default: '',
            },
            source: {
                default: '',
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-type="heritage-poetry"]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(HTMLAttributes, { 'data-type': 'heritage-poetry' }), 0]
    },

    addNodeView() {
        return VueNodeViewRenderer(PoetryNodeView)
    },

    addCommands() {
        return {
            setPoetry: (attributes) => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                    attrs: attributes,
                })
            },
        }
    },
})

export default HeritagePoetry
