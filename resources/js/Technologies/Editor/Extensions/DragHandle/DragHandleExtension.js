import { Extension } from '@tiptap/core'

export const DragHandleExtension = Extension.create({
    name: 'dragHandle',

    addGlobalAttributes() {
        return [
            {
                types: [
                    'paragraph',
                    'heading',
                    'bulletList',
                    'orderedList',
                    'listItem',
                    'blockquote',
                    'codeBlock',
                ],
                attributes: {
                    'data-drag-handle': {
                        default: null,
                        parseHTML: element => element.getAttribute('data-drag-handle'),
                        renderHTML: attributes => {
                            return {
                                'data-drag-handle': 'true',
                            }
                        },
                    },
                },
            },
        ]
    },
})
