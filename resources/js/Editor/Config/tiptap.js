/**
 * Tiptap Editor Configuration
 */

import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import TextAlign from '@tiptap/extension-text-align'
import Placeholder from '@tiptap/extension-placeholder'
import { HeritagePoetry } from '../Extensions/Heritage/HeritagePoetry'
import { QuranicVerse } from '../Extensions/Heritage/QuranicVerse'
import { ScientificFootnote } from '../Extensions/Scientific/ScientificFootnote'

export const tiptapConfig = {
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3, 4, 5, 6]
            },
            history: {
                depth: 100
            }
        }),
        Underline,
        TextAlign.configure({
            types: ['heading', 'paragraph'],
            alignments: ['left', 'center', 'right', 'justify'],
            defaultAlignment: 'right'
        }),
        Placeholder.configure({
            placeholder: 'ابدأ الكتابة هنا...'
        }),
        HeritagePoetry,
        QuranicVerse,
        ScientificFootnote
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-lg max-w-none focus:outline-none',
            dir: 'rtl',
            spellcheck: 'false'
        }
    }
}

export default tiptapConfig
