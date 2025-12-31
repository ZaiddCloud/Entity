import { Mark, mergeAttributes } from '@tiptap/core'

export const ScholarlyFootnote = Mark.create({
  name: 'scholarlyFootnote',

  addAttributes() {
    return {
      content: {
        default: '',
        parseHTML: element => element.getAttribute('data-content'),
        renderHTML: attributes => ({
          'data-content': attributes.content,
        }),
      },
      marker: {
        default: '*',
        parseHTML: element => element.getAttribute('data-marker'),
        renderHTML: attributes => ({
          'data-marker': attributes.marker,
        }),
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
    return ['span', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, { 'data-footnote': '' }), 0]
  },

  addCommands() {
    return {
      setFootnote: attributes => ({ chain }) => {
        return chain()
          .setMark(this.name, attributes)
          .setMeta('addToHistory', true)
          .run()
      },
      unsetFootnote: () => ({ chain }) => {
        return chain()
          .unsetMark(this.name)
          .run()
      },
    }
  },
})
