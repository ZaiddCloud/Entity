import { Mark } from '@tiptap/core'

export const SegmentLink = Mark.create({
    name: 'segmentLink',

    addOptions() {
        return {
            HTMLAttributes: {},
            onSeek: null, // Callback function to handle seeking
        }
    },

    addAttributes() {
        return {
            segmentId: {
                default: null,
                parseHTML: element => element.getAttribute('data-id') || element.getAttribute('data-segment-id'),
                renderHTML: attributes => ({ 'data-id': attributes.segmentId }),
            },
            startTime: {
                default: null,
                parseHTML: element => element.getAttribute('data-start-time'),
                renderHTML: attributes => ({ 'data-start-time': attributes.startTime }),
            },
            title: {
                default: null,
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-segment-link]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['span', {
            ...HTMLAttributes,
            'data-segment-link': '',
            class: 'segment-link cursor-pointer text-blue-600 hover:text-blue-800 hover:underline font-semibold transition-colors',
            title: 'انقر للقفز إلى هذا المقطع في المشغل',
        }, 0]
    },

    addCommands() {
        return {
            setSegmentLink: (attributes) => ({ commands }) => {
                return commands.setMark(this.name, attributes)
            },
            unsetSegmentLink: () => ({ commands }) => {
                return commands.unsetMark(this.name)
            },
        }
    },
})
