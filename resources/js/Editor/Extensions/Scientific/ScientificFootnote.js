import { Mark, mergeAttributes } from '@tiptap/core'

export const ScientificFootnote = Mark.create({
    name: 'scientificFootnote',

    addAttributes() {
        return {
            id: {
                default: null,
            },
            type: {
                default: 'comment', // tahqiq, takhrij, sharh, comment
            },
            content: {
                default: '',
            },
            reference: {
                default: '',
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-footnote]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'span',
            mergeAttributes(HTMLAttributes, {
                'data-footnote': '',
                class: 'scientific-footnote',
            }),
            0,
        ]
    },

    addCommands() {
        return {
            setFootnote: (attributes) => ({ commands }) => {
                return commands.setMark(this.name, attributes)
            },
            toggleFootnote: (attributes) => ({ commands }) => {
                return commands.toggleMark(this.name, attributes)
            },
            unsetFootnote: () => ({ commands }) => {
                return commands.unsetMark(this.name)
            },
        }
    },
})

export default ScientificFootnote
