<script setup>
import { useFootnoteStore } from './FootnoteStore'
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'

const store = useFootnoteStore()
const editor = ref(null)

// Initialize Secondary Editor
onMounted(() => {
    editor.value = new Editor({
        extensions: [
            StarterKit,
        ],
        content: store.activeFootnoteContent,
        editorProps: {
            attributes: {
                class: 'prose prose-sm focus:outline-none min-h-[150px] w-full text-black',
                dir: 'rtl'
            }
        }
    })
})

// Sync content from store when opening
watch(() => store.isOpen, (val) => {
    if (val && editor.value && store.activeFootnoteContent) {
        editor.value.commands.setContent(store.activeFootnoteContent)
    } else if (val && editor.value) {
        editor.value.commands.clearContent()
    }
})

onBeforeUnmount(() => {
    editor.value?.destroy()
})

const handleSave = () => {
    if (!editor.value) return
    store.saveFootnote(editor.value.getJSON(), store.activeFootnoteType)
}
</script>

<template>
  <div v-if="store.isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" dir="rtl">
    <div class="bg-white border border-gray-300 rounded-lg shadow-2xl w-[90%] max-w-lg overflow-hidden flex flex-col max-h-[80vh]">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-gray-900 font-bold text-sm">تحرير الحاشية</h3>
            <button @click="store.closeEditor" class="text-gray-400 hover:text-gray-600 transition-colors">
                ✕
            </button>
        </div>

        <!-- Type Selector -->
        <div class="px-4 py-3 border-b border-gray-200 flex gap-2 overflow-x-auto bg-gray-50">
            <button 
                v-for="type in ['tahqiq', 'sharh', 'takhrij', 'comment']"
                :key="type"
                class="px-3 py-1 rounded-full text-xs font-medium border transition-all"
                :class="store.activeFootnoteType === type 
                    ? 'bg-blue-600 text-white border-blue-500' 
                    : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100'"
                @click="store.activeFootnoteType = type"
            >
                {{ type === 'tahqiq' ? 'تحقيق' : (type === 'sharh' ? 'شرح' : (type === 'takhrij' ? 'تخريج' : 'تعليق')) }}
            </button>
        </div>

        <!-- Editor Area -->
        <div class="flex-1 overflow-y-auto p-4 bg-white">
            <editor-content :editor="editor" />
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex justify-end gap-2">
            <button @click="store.closeEditor" class="px-4 py-1.5 rounded text-xs text-gray-600 hover:bg-gray-200">
                إلغاء
            </button>
            <button @click="handleSave" class="px-4 py-1.5 rounded text-xs bg-blue-600 text-white hover:bg-blue-500 font-bold">
                حفظ الحاشية
            </button>
        </div>
    </div>
  </div>
</template>
