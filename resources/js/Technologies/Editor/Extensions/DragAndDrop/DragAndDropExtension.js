import { Extension } from '@tiptap/core'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { DOMParser } from '@tiptap/pm/model'
import mammoth from 'mammoth'

export const DragAndDrop = Extension.create({
    name: 'dragAndDrop',

    addProseMirrorPlugins() {
        return [
            new Plugin({
                key: new PluginKey('dragAndDrop'),
                props: {
                    handleDrop(view, event, slice, moved) {
                        if (!moved && event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files[0]) {
                            const file = event.dataTransfer.files[0]
                            const coordinates = view.posAtCoords({ left: event.clientX, top: event.clientY })

                            const reader = new FileReader()
                            reader.onload = (readerEvent) => {
                                const result = readerEvent.target.result

                                if (file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                                    mammoth.convertToHtml({ arrayBuffer: result })
                                        .then(docResult => {
                                            const html = docResult.value
                                            // Parse HTML string to DOM fragment
                                            const parser = new window.DOMParser()
                                            const doc = parser.parseFromString(html, 'text/html')

                                            const pmParser = DOMParser.fromSchema(view.state.schema)
                                            const slice = pmParser.parseSlice(doc.body, { preserveWhitespace: true })

                                            const transaction = view.state.tr.insert(coordinates.pos, slice.content)
                                            view.dispatch(transaction)
                                        })
                                        .catch(err => console.error(err))
                                    return
                                }

                                if (file.type.startsWith('image/')) {
                                    // Handle Image
                                    const node = view.state.schema.nodes.image.create({
                                        src: result
                                    })
                                    const transaction = view.state.tr.insert(coordinates.pos, node)
                                    view.dispatch(transaction)
                                } else {
                                    // Handle File Attachment
                                    if (view.state.schema.nodes.fileAttachment) {
                                        const node = view.state.schema.nodes.fileAttachment.create({
                                            src: result,
                                            filename: file.name
                                        })
                                        const transaction = view.state.tr.insert(coordinates.pos, node)
                                        view.dispatch(transaction)
                                    } else {
                                        console.warn('FileAttachment node not registered')
                                    }
                                }
                            }

                            if (file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                                reader.readAsArrayBuffer(file)
                            } else {
                                reader.readAsDataURL(file)
                            }

                            event.preventDefault()
                            return true
                        }
                        return false
                    }
                }
            })
        ]
    }
})
