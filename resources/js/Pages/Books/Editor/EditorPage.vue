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
    },
    // New polymorphic props from Controller
    editor_mode: {
        type: String,
        default: 'book' // 'book', 'manuscript', 'audio', 'video'
    },
    resource_data: {
        type: Object,
        default: null
    }
})

const store = useEditorStore()
const editorRef = ref(null)

// Static imports for stability in tests and simple view
import ManuscriptViewer from './Components/Viewers/ManuscriptViewer.vue'
import MediaPlayer from './Components/Viewers/MediaPlayer.vue'

onMounted(() => {
    // Initialize polymorphic state
    store.setEditorMode(props.editor_mode)
    if (props.resource_data) {
        store.setResourceData(props.resource_data)
    }

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

        <!-- Dynamic Viewer Slot -->
        <template #viewer v-if="['manuscript', 'audio', 'video'].includes(store.editorMode)">
            <ManuscriptViewer 
                v-if="store.editorMode === 'manuscript'" 
                :resource="store.resourceData"
            />
            <MediaPlayer 
                v-else-if="['audio', 'video'].includes(store.editorMode)"
                :mode="store.editorMode"
                :resource="store.resourceData"
            />
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
