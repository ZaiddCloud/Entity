<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import EditorLayout from './EditorLayout.vue'
import EditorToolbar from '@/Technologies/Editor/UI/Toolbar/EditorToolbar.vue'
import TiptapEditor from '@/Technologies/Editor/Core/TiptapEditor.vue'

import { useEditorStore } from '@/Technologies/Store/EditorStore'
import { useEditorSave } from '@/Technologies/Editor/Composables/useEditorSave'
import { useEditorNavigation } from '@/Technologies/Editor/Composables/useEditorNavigation'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    entity: {
        type: Object,
        required: true
    },
    contentNode: {
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
    },
    hierarchy: {
        type: Array,
        default: () => []
    },
    navigation: {
        type: Object,
        default: () => ({ prev: null, next: null })
    }
})

const store = useEditorStore()
const { save, startAutoSave, stopAutoSave } = useEditorSave()
const { goToPrev, goToNext } = useEditorNavigation() // Not strictly used in template but ready
const editorRef = ref(null)

// Static imports for stability in tests and simple view
import DetailViewer from '@/Technologies/Manuscripter/UI/DetailViewer.vue'
import MediaPlayer from '@/Technologies/Player/MediaPlayer.vue'
import DraggableMediaPlayer from '@/Technologies/Player/DraggableMediaPlayer.vue'
import AudioSegmentEditor from '@/Technologies/Editor/Core/AudioSegmentEditor.vue'
import VideoSceneEditor from '@/Technologies/Editor/Core/VideoSceneEditor.vue'

onMounted(() => {
    // Initialize polymorphic state
    store.setEditorMode(props.editor_mode)
    if (props.resource_data) {
        store.setResourceData(props.resource_data)
    }

    store.loadDocument(props.entity, props.contentNode, props.hierarchy, props.navigation)
    startAutoSave()
})

onUnmounted(() => {
    stopAutoSave()
})

const handleToolbarCommand = ({ command, value }) => {
    if (command === 'save') {
        save()
    } else if (command === 'togglePin') {
        store.togglePin()
    } else if (command === 'goto') {
        // Go to specific node
        router.visit(route('studio.show', { type: store.editorMode, slug: value.slug }))
    } else if (command === 'prev') {
        // use composable or direct router visit if logic matches
        const target = store.navigation.prev
        if (target) router.visit(route('studio.show', { type: store.editorMode, slug: target.slug }))
    } else if (command === 'next') {
        const target = store.navigation.next
        if (target) router.visit(route('studio.show', { type: store.editorMode, slug: target.slug }))
    } else if (['minimize', 'maximize'].includes(command)) {
        // Handle window controls if needed
    } else if (command === 'addMediaNode') {
        store.addMediaNode()
    } else {
        store.executeCommand(command, value)
    }
}
</script>

<template>
  <EditorLayout :title="store.documentTitle">
    <template #toolbar>
      <EditorToolbar @command="handleToolbarCommand" />
    </template>

    <!-- Dynamic Viewer Slot -->
    <template
      v-if="['manuscript', 'video'].includes(store.editorMode)"
      #viewer
    >
      <DetailViewer 
        v-if="store.editorMode === 'manuscript'" 
        :resource="store.resourceData"
        :current-node="store.currentContentNode"
      />
      <!-- Keeping original MediaPlayer for video for now, unless Draggable is desired for Video too -->
      <MediaPlayer 
        v-else-if="store.editorMode === 'video'"
        :mode="store.editorMode"
        :resource="store.resourceData"
        :hierarchy="store.hierarchy"
      />
    </template>

    <!-- Draggable Player (Floating outside layout) -->
    <template #overlays>
         <DraggableMediaPlayer
            v-if="store.editorMode === 'audio' && store.resourceData"
            :src="store.resourceData.file_url"
            :title="store.resourceData.title"
            :segments="store.hierarchy" 
            poster="/images/audio-placeholder.jpg"
         />
    </template>


    <!-- Main Paper Sheet -->
    <div class="bg-white shadow-xl min-h-[1100px] border border-gray-200 rounded-sm overflow-hidden mb-20 relative">
      <TiptapEditor 
        v-if="store.editorMode === 'book' || store.editorMode === 'manuscript'"
        :key="store.contentVersion"
        ref="editorRef"
        v-model="store.content"
        @set-editor="store.setEditor"
      />
      <AudioSegmentEditor
        v-else-if="store.editorMode === 'audio'"
        v-model="store.content"
      />
      <VideoSceneEditor
        v-else-if="store.editorMode === 'video'"
        v-model="store.content"
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
