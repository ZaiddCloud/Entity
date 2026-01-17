import { Mark, mergeAttributes } from '@tiptap/core'
import { v4 as uuidv4 } from 'uuid'

export const ScientificFootnote = Mark.create({
    name: 'scientificFootnote',

    addAttributes() {
        return {
            id: {
                default: null,
                parseHTML: element => element.getAttribute('data-id'),
                renderHTML: attributes => {
                    if (!attributes.id) {
                        return {}
                    }
                    return {
                        'data-id': attributes.id,
                    }
                },
            },
            type: {
                default: 'comment', // tahqiq, takhrij, sharh, comment
                parseHTML: element => element.getAttribute('data-type'),
                renderHTML: attributes => {
                    return {
                        'data-type': attributes.type,
                    }
                },
            },
            // Metadata for quick preview without parsing full JSON
            preview: {
                default: '',
            },
            // The rich text content of the footnote (stored as JSON string or object)
            content_json: {
                default: null,
                renderHTML: (attributes) => {
                    return {
                        'data-content-json': JSON.stringify(attributes.content_json)
                    }
                },
                parseHTML: (element) => {
                    const content = element.getAttribute('data-content-json')
                    return content ? JSON.parse(content) : null
                }
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-footnote]',
            },
            {
                tag: 'sup.scientific-footnote',
            }
        ]
    },

    renderHTML({ HTMLAttributes }) {
        // Semantic HTML: Use <sup> for footnotes
        return [
            'sup',
            mergeAttributes(HTMLAttributes, {
                'data-footnote': '',
                class: 'scientific-footnote cursor-pointer text-blue-600 font-bold hover:underline select-none',
            }),
            0, // Render the text content inside the sup (e.g., "[1]")
        ]
    },

    addCommands() {
        return {
            insertFootnote: (attributes = {}) => ({ commands, state }) => {
                const ids = new Set()
                state.doc.descendants(node => {
                    node.marks.forEach(mark => {
                        if (mark.type.name === this.name && mark.attrs.id) {
                            ids.add(mark.attrs.id)
                        }
                    })
                })

                const nextNumber = `[${ids.size + 1}]`
                const footnoteId = attributes.id || uuidv4()

                return commands.insertContent({
                    type: 'text',
                    text: nextNumber,
                    marks: [
                        {
                            type: this.name,
                            attrs: {
                                ...attributes,
                                id: footnoteId,
                            },
                        },
                    ],
                })
            },
            setFootnote: (attributes = {}) => ({ commands }) => {
                return commands.setMark(this.name, {
                    ...attributes,
                    id: attributes.id || uuidv4()
                })
            },
            toggleFootnote: (attributes = {}) => ({ commands }) => {
                return commands.toggleMark(this.name, {
                    ...attributes,
                    id: attributes.id || uuidv4()
                })
            },
            unsetFootnote: () => ({ commands }) => {
                return commands.unsetMark(this.name)
            },
        }
    },
})

export default ScientificFootnote
