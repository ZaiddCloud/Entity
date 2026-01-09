import { VueRenderer } from '@tiptap/vue-3'
import tippy from 'tippy.js'
import CommandList from './CommandList.vue'

export default {
    items: ({ query }) => {
        return [
            {
                title: 'نـص عـادي',
                icon: 'ri-text',
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .setParagraph()
                        .run()
                },
            },
            {
                title: 'عـنوان 1',
                icon: 'ri-h-1',
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .setNode('heading', { level: 1 })
                        .run()
                },
            },
            {
                title: 'عـنوان 2',
                icon: 'ri-h-2',
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .setNode('heading', { level: 2 })
                        .run()
                },
            },
            {
                title: 'قائمة نقطية',
                icon: 'ri-list-unordered',
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .toggleBulletList()
                        .run()
                },
            },
            {
                title: 'قائمة رقمية',
                icon: 'ri-list-ordered',
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .toggleOrderedList()
                        .run()
                },
            },
            {
                title: 'اقـتباس',
                icon: 'ri-double-quotes-l',
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .toggleBlockquote()
                        .run()
                },
            },
            {
                title: 'فاصل أفقي',
                icon: 'ri-separator',
                command: ({ editor, range }) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .setHorizontalRule()
                        .run()
                },
            },
        ].filter(item => item.title.toLowerCase().startsWith(query.toLowerCase()))
    },

    render: () => {
        let component
        let popup

        return {
            onStart: props => {
                component = new VueRenderer(CommandList, {
                    props,
                    editor: props.editor,
                })

                if (!props.clientRect) {
                    return
                }

                popup = tippy('body', {
                    getReferenceClientRect: props.clientRect,
                    appendTo: () => document.body,
                    content: component.element,
                    showOnCreate: true,
                    interactive: true,
                    trigger: 'manual',
                    placement: 'bottom-start',
                })
            },

            onUpdate(props) {
                component.updateProps(props)

                if (!props.clientRect) {
                    return
                }

                popup[0].setProps({
                    getReferenceClientRect: props.clientRect,
                })
            },

            onKeyDown(props) {
                if (props.event.key === 'Escape') {
                    popup[0].hide()

                    return true
                }

                return component.ref?.onKeyDown(props)
            },

            onExit() {
                if (popup && popup[0]) {
                    popup[0].destroy()
                }
                if (component) {
                    component.destroy()
                }
            },
        }
    },
}
