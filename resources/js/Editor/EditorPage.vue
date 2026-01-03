<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import EditorLayout from './EditorLayout.vue'
import EditorToolbar from './Components/Toolbar/EditorToolbar.vue'
import TiptapEditor from './Components/Content/TiptapEditor.vue'

import { useEditorStore } from './Store/editorStore'

const props = defineProps({
    book: {
        type: Object,
        required: true
    },
    child: {
        type: Object,
        required: true
    }
})

const store = useEditorStore()
const editorRef = ref(null)

onMounted(() => {
    store.loadDocument(props.book, props.child)
    store.startAutoSave()
})

onUnmounted(() => {
    store.stopAutoSave()
})

const handleToolbarCommand = ({ command, value }) => {
    store.executeCommand(command, value)
}
</script>

<template>
    <EditorLayout :title="store.documentTitle">
        <template #toolbar>
            <EditorToolbar @command="handleToolbarCommand" />
        </template>

        <template #sidebar>

        </template>

        <!-- Main Paper Sheet -->
        <div class="bg-white shadow-xl min-h-[1100px] border border-gray-200 rounded-sm overflow-hidden mb-20 relative">
            <TiptapEditor 
                ref="editorRef"
                v-model="store.content"
                @set-editor="store.setEditor"
            />
        </div>
    </EditorLayout>
</template>

<style scoped>
/* Paper sheet mimicry */
.bg-white {
    box-shadow: 0 0 50px rgba(0,0,0,0.05);
}
</style>
