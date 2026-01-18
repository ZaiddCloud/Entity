import { Mark, mergeAttributes } from '@tiptap/core'
import { v4 as uuidv4 } from 'uuid'
import { Plugin, PluginKey } from '@tiptap/pm/state'

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
                const footnoteId = attributes.id || uuidv4()

                return commands.insertContent({
                    type: 'text',
                    text: '[?]', // Placeholder, plugin will fix it
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

    addProseMirrorPlugins() {
        return [
            new Plugin({
                key: new PluginKey('scientificFootnoteRenumbering'),
                appendTransaction: (transactions, oldState, newState) => {
                    // Only run if the document actually changed
                    if (!transactions.some(tr => tr.docChanged)) return

                    const { doc } = newState
                    const markType = newState.schema.marks[this.name]
                    if (!markType) return

                    const changes = []
                    let currentIndex = 1
                    let lastSeenId = null

                    doc.descendants((node, pos) => {
                        const mark = node.marks.find(m => m.type === markType)
                        if (mark && node.isText) {
                            const footnoteId = mark.attrs.id

                            // If we haven't seen this ID yet (or it's a new occurrence), increment
                            if (footnoteId !== lastSeenId) {
                                const expectedText = `[${currentIndex}]`
                                if (node.text !== expectedText) {
                                    changes.push({
                                        from: pos,
                                        to: pos + node.text.length,
                                        text: expectedText
                                    })
                                }
                                currentIndex++
                                lastSeenId = footnoteId
                            }
                        } else if (!mark) {
                            lastSeenId = null
                        }
                        return true
                    })

                    if (changes.length === 0) return

                    const tr = newState.tr
                    // Important: Apply changes in reverse to keep positions valid
                    changes.reverse().forEach(change => {
                        tr.insertText(change.text, change.from, change.to)
                    })

                    // Avoid infinite loops by ensuring doc actually changed
                    return tr.docChanged ? tr : null
                }
            })
        ]
    },
})

export default ScientificFootnote
