import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import FileComponent from './FileComponent.vue'

export default Node.create({
    name: 'fileAttachment',

    group: 'block',

    atom: true,

    addAttributes() {
        return {
            src: {
                default: null,
            },
            filename: {
                default: 'ملف غير معروف',
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'file-attachment',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['file-attachment', mergeAttributes(HTMLAttributes)]
    },

    addNodeView() {
        return VueNodeViewRenderer(FileComponent)
    },
})
