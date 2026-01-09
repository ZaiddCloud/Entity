import { Extension } from '@tiptap/core'
import Suggestion from '@tiptap/suggestion'

export const CommandExtension = Extension.create({
    name: 'commandExtension',

    addOptions() {
        return {
            suggestion: {
                char: '/',
                command: ({ editor, range, props }) => {
                    props.command({ editor, range })
                },
            },
        }
    },

    addProseMirrorPlugins() {
        return [
            Suggestion({
                editor: this.editor,
                ...this.options.suggestion,
            }),
        ]
    },

    addKeyboardShortcuts() {
        return {
            'Alt-/': () => {
                this.editor.commands.insertContent('/')
                return true
            }
        }
    }
})
