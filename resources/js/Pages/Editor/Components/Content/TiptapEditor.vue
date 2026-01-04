<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import TextAlign from '@tiptap/extension-text-align'
import Placeholder from '@tiptap/extension-placeholder'
import HeritagePoetry from '../../Extensions/Heritage/HeritagePoetry'
import QuranicVerse from '../../Extensions/Heritage/QuranicVerse'
import ScientificFootnote from '../../Extensions/Scientific/ScientificFootnote'

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['update:modelValue', 'setEditor'])

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3, 4, 5, 6]
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
            class: 'prose prose-lg max-w-none focus:outline-none min-h-[800px] p-10 lg:p-14',
            dir: 'rtl'
        }
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML())
    },
    onCreate: ({ editor }) => {
        emit('setEditor', editor)
    }
})

watch(() => props.modelValue, (value) => {
    if (editor.value && value !== editor.value.getHTML()) {
        editor.value.commands.setContent(value, false)
    }
})

onBeforeUnmount(() => {
    if (editor.value) {
        editor.value.destroy()
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
</style>
