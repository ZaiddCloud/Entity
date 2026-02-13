<script setup>
import { onMounted, onBeforeUnmount, watch, ref } from 'vue'
import TiptapEditor from './Core/TiptapEditor.vue'
import EditorToolbar from './UI/Toolbar/EditorToolbar.vue'
import FootnoteEditor from './Extensions/Footnotes/FootnoteEditor.vue'
import ReferencePane from '../Studio/Panes/ReferencePane.vue' // Added ReferencePane
import { useEditorStore } from '@/Technologies/Store/EditorStore'
import { useMediaStore } from '@/Technologies/Store/MediaStore'

const props = defineProps({
    initialContent: { type: [String, Object, Array], default: '' },
    mediaEntity: { type: Object, default: null }, // NEW
    type: { type: String, default: 'manuscript' } // NEW
})

const emit = defineEmits(['navigate', 'navigate-full', 'add-node']);

import { useEditorSave } from './Composables/useEditorSave'

const store = useEditorStore()
const mediaStore = useMediaStore()
const { save } = useEditorSave()

const isFloating = ref(false)

const toggleDock = () => {
    isFloating.value = !isFloating.value
}

// ... (Existing watchers and handlers) ...
watch(() => props.initialContent, (newVal) => {
    if (newVal !== store.content) {
        store.updateContent(newVal)
    }
})

const handleCommand = ({ command, value }) => {
    if (command === 'save') {
        save()
    } else {
        store.executeCommand(command, value)
    }
}

// --- STEP 4: ORCHESTRATOR LISTENER ---
const onInsertNode = (event) => {
    const { type, title, time } = event.detail
    const visualMap = store.resourceData?.visual_map || {}
    const config = visualMap[type] || { tag: 'h4', behavior: 'container' }
    
    if (config.behavior === 'container') {
        const level = parseInt(config.tag?.replace('h', '')) || 4
        store.editor?.commands.insertStructureNode(type, title, level)
    } else {
        const level = parseInt(config.tag?.replace('h', '')) || 4
        store.editor?.commands.insertMarkerNode(type, title, { time, level })
    }
}

onMounted(() => {
    window.addEventListener('studio:insert-node', onInsertNode)
})

onBeforeUnmount(() => {
    window.removeEventListener('studio:insert-node', onInsertNode)
})

// Seek player when clicking segment title
const handleTitleClick = () => {
    if (store.currentContentNode?.start_time !== undefined) {
        mediaStore.requestSeek(store.currentContentNode.start_time);
    }
}
</script>

<template>
  <div class="flex-1 flex flex-col bg-white overflow-hidden relative h-full w-full">
    <!-- Toolbar -->
    <EditorToolbar @command="handleCommand" />
          
    <!-- Editor Core -->
    <div class="flex-1 overflow-y-auto bg-white custom-scrollbar">
      <div class="w-full min-h-full p-8 md:p-12 relative">
        
        <!-- Segment Title Input (Only for specific segments) -->
         <div v-if="store.currentContentNode && store.currentContentNode.id !== 'full'" class="mb-6 border-b border-gray-100 pb-4">
            <input 
                v-model="store.currentContentNode.title" 
                class="text-2xl font-bold w-full border-none focus:ring-0 px-0 text-gray-800 placeholder-gray-300 bg-transparent text-right cursor-pointer hover:text-blue-600 transition-colors" 
                placeholder="عنوان المقطع"
                @click="handleTitleClick"
                @keydown.enter.prevent
                title="انقر للقفز إلى بداية المقطع في المشغل"
            />
        </div>

        <!-- Wrapped/Floating Media Player -->
        <div 
          v-if="['audio', 'video'].includes(props.type) && props.mediaEntity" 
          v-show="mediaStore.isOpen"
          :class="[
            isFloating ? 'fixed top-[150px] left-[343px] z-[90]' : 'float-left ml-0 mr-8 mb-8 sticky top-4 z-[90] w-fit h-fit'
          ]"
        >
           <ReferencePane
             :type="props.type"
             :entity="props.mediaEntity"
             :is-integrated="!isFloating"
             :is-studio-context="true"
             @toggle-dock="toggleDock"
             @navigate="(id) => $emit('navigate', id)"
             @navigate-full="$emit('navigate-full')"
             @add-node="(data) => $emit('add-node', data)"
           />
        </div>

        <TiptapEditor
          v-model="store.content"
          @set-editor="store.setEditor"
          @navigate="(id) => $emit('navigate', id)"
        />
      </div>
    </div>
    
    <!-- Extensions UI -->
    <FootnoteEditor />
  </div>
</template>
