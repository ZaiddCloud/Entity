<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import { useEditorStore } from '@/Technologies/Store/EditorStore'
import { useTiptapStore } from './TiptapStore'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import TextAlign from '@tiptap/extension-text-align'
import Placeholder from '@tiptap/extension-placeholder'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'
import { Table } from '@tiptap/extension-table'
import { TableCell } from '@tiptap/extension-table-cell'
import { TableHeader } from '@tiptap/extension-table-header'
import { TableRow } from '@tiptap/extension-table-row'
import Subscript from '@tiptap/extension-subscript'
import Superscript from '@tiptap/extension-superscript'
import Highlight from '@tiptap/extension-highlight'
import { Color } from '@tiptap/extension-color'
import { TextStyle } from '@tiptap/extension-text-style'

import HeritagePoetry from '../Extensions/Poetry/PoetryExtension'
import QuranicVerse from '../Extensions/Quran/QuranExtension'
import ScientificFootnote from '../Extensions/Footnotes/FootnoteExtension'

// Commands (Slash Menu)
import { CommandExtension } from '../Extensions/Commands/CommandExtension'
import suggestionUtils from '../Extensions/Commands/SuggestionUtils'

// Drag & Drop
import FileNode from '../Nodes/File/FileNode'
import { DragAndDrop } from '../Extensions/DragAndDrop/DragAndDropExtension'

// Drag Handle
import { DragHandleExtension } from '../Extensions/DragHandle/DragHandleExtension'

// UI Components will be added in the next step
// import EditorBubbleMenu from '../UI/EditorBubbleMenu.vue'

const props = defineProps({
    modelValue: {
        type: [String, Array, Object],
        default: ''
    },
    editable: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits(['update:modelValue', 'setEditor'])
const tiptapStore = useTiptapStore()

const editor = useEditor({
    content: props.modelValue,
    editable: props.editable,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3, 4, 5, 6]
            }
        }),
        // Underline, // Potentially duplicated
        TextAlign.configure({
            types: ['heading', 'paragraph'],
            alignments: ['left', 'center', 'right', 'justify'],
            defaultAlignment: 'right'
        }),
        Placeholder.configure({
            placeholder: 'ابدأ الكتابة هنا...'
        }),
        // Link.configure({
        //     openOnClick: false
        // }),
        Image,
        Table.configure({
            resizable: true,
        }),
        TableRow,
        TableHeader,
        TableCell,
        Subscript,
        Superscript,
        Highlight,
        TextStyle,
        Color,
        HeritagePoetry,
        QuranicVerse,
        ScientificFootnote,
        FileNode,  // Register new node
        DragAndDrop, // Register extension
        CommandExtension.configure({
            suggestion: suggestionUtils
        }),
        DragHandleExtension,
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-lg max-w-none focus:outline-none min-h-[800px] p-10 lg:p-14',
            dir: 'rtl'
        },
        handleClick: (view, pos, event) => {
            if (event.target.closest('.scientific-footnote')) {
                const node = view.state.doc.nodeAt(pos)
                const mark = node?.marks.find(m => m.type.name === 'scientificFootnote') || 
                            view.state.selection.$from.marks().find(m => m.type.name === 'scientificFootnote')
                
                if (mark) {
                    const store = useEditorStore() // We might need FootnoteStore here, but let's dynamic import or use prop
                    // Actually, importing useFootnoteStore is cleaner
                    import('../Extensions/Footnotes/FootnoteStore').then(({ useFootnoteStore }) => {
                        const footnoteStore = useFootnoteStore()
                        footnoteStore.openEditor(
                            editor.value,
                            mark.attrs.id,
                            mark.attrs.type,
                            mark.attrs.content_json
                        )
                    })
                    return true
                }
            }
            return false
        }
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML())
    },
    onCreate: ({ editor }) => {
        emit('setEditor', editor)
        tiptapStore.setEditor(editor)
    }
})

watch(() => props.modelValue, (value) => {
    if (editor.value && value !== editor.value.getHTML()) {
        editor.value.commands.setContent(value, false)
    }
})

watch(() => props.editable, (value) => {
    if (editor.value) {
        editor.value.setEditable(value)
    }
})

onBeforeUnmount(() => {
    if (editor.value) {
        editor.value.destroy()
    }
})

// Auto-Scroll & Highlight Logic
watch(() => useEditorStore().currentTime, (time) => {
    if (!editor.value || !time) return

    // Find the node that matches the current time
    // Assuming nodes have data-start attributes or we map by index/proportion
    // For now, simpler implementation: Highlight paragraph if it contains a timestamp? 
    // OR if we have specific node mapping. 
    // Let's iterate doc nodes to find one with data-start <= time
    
    let targetPos = null
    
    editor.value.state.doc.descendants((node, pos) => {
        if (targetPos) return false // Found
        
        // Check for timestamp attributes (if they exist)
        const start = parseFloat(node.attrs.startTime || node.attrs['data-start'] || -1)
        const end = parseFloat(node.attrs.endTime || node.attrs['data-end'] || -1)
        
        if (start >= 0 && time >= start && (end === -1 || time <= end)) {
            targetPos = pos
            return false
        }
    })

    if (targetPos !== null) {
        // Highlight logic (e.g. valid decoration or selection)
        // For visual simplicity, let's just scroll to it first
        const dom = editor.value.view.nodeDOM(targetPos)
        if (dom instanceof HTMLElement) {
            // Add temp highlight class
            document.querySelectorAll('.active-transcript-line').forEach(el => el.classList.remove('active-transcript-line'))
            dom.classList.add('active-transcript-line')
            
            dom.scrollIntoView({ behavior: 'smooth', block: 'center' })
        }
    }
})
</script>

<template>
  <div class="tiptap-editor">
    <EditorContent :editor="editor" />
  </div>
</template>

<style>
/* Tiptap Editor Styles */
.tiptap-editor {
    font-family: 'Amiri', 'Traditional Arabic', serif;
    line-height: 2;
    direction: rtl;
}

.ProseMirror {
    min-height: 800px;
}

.ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: right;
    color: #adb5bd;
    pointer-events: none;
    height: 0;
}

.ProseMirror:focus {
    outline: none;
}

/* Arabic Typography */
.ProseMirror p {
    margin-bottom: 1em;
    text-align: right;
}

.ProseMirror h1,
.ProseMirror h2,
.ProseMirror h3 {
    font-weight: 700;
    margin-top: 1.5em;
    margin-bottom: 0.5em;
    text-align: right;
}

.ProseMirror h1 {
    font-size: 2em;
}

.ProseMirror h2 {
    font-size: 1.5em;
}

.ProseMirror h3 {
    font-size: 1.25em;
}

.ProseMirror ul,
.ProseMirror ol {
    padding-right: 2em;
    margin-bottom: 1em;
}

.ProseMirror strong {
    font-weight: 700;
}

.ProseMirror em {
    font-style: italic;
}

.ProseMirror u {
    text-decoration: underline;
}

/* Drag Handle Styles */
.ProseMirror p,
.ProseMirror h1,
.ProseMirror h2,
.ProseMirror h3,
.ProseMirror h4,
.ProseMirror h5,
.ProseMirror h6 {
    position: relative;
}

.ProseMirror p:hover::before,
.ProseMirror h1:hover::before,
.ProseMirror h2:hover::before,
.ProseMirror h3:hover::before,
.ProseMirror h4:hover::before,
.ProseMirror h5:hover::before,
.ProseMirror h6:hover::before {
    content: '⋮⋮';
    position: absolute;
    right: calc(100% + 0.5rem);
    top: 0.25rem;
    color: #9CA3AF;
    font-size: 1.2rem;
    line-height: 1;
    cursor: grab;
    padding: 0.25rem;
    border-radius: 0.25rem;
    transition: all 0.2s;
    user-select: none;
}

.ProseMirror p:hover::before:hover,
.ProseMirror h1:hover::before:hover,
.ProseMirror h2:hover::before:hover,
.ProseMirror h3:hover::before:hover,
.ProseMirror h4:hover::before:hover,
.ProseMirror h5:hover::before:hover,
.ProseMirror h6:hover::before:hover {
    background-color: rgba(0, 0, 0, 0.05);
    color: #4B5563;
}



</style>

<style scoped>
.active-transcript-line {
    background-color: rgba(59, 130, 246, 0.1); /* blue-500/10 */
    border-right: 3px solid #3b82f6;
    padding-right: 0.5em; /* Compensate for border */
    transition: all 0.3s ease;
    border-radius: 4px;
}
</style>
