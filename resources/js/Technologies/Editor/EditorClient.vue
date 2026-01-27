<script setup>
import { onMounted, watch, ref } from 'vue'
import TiptapEditor from './Core/TiptapEditor.vue'
import EditorToolbar from './UI/Toolbar/EditorToolbar.vue'
import FootnoteEditor from './Extensions/Footnotes/FootnoteEditor.vue'
import ReferencePane from '../Studio/Panes/ReferencePane.vue' // Added ReferencePane
import { useEditorStore } from '@/Technologies/Store/EditorStore'

const props = defineProps({
    initialContent: { type: [String, Object, Array], default: '' },
    mediaEntity: { type: Object, default: null }, // NEW
    type: { type: String, default: 'manuscript' } // NEW
})

import { useEditorSave } from './Composables/useEditorSave'

const store = useEditorStore()
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
</script>

<template>
  <div class="flex-1 flex flex-col bg-white overflow-hidden relative h-full w-full">
    <!-- Toolbar -->
    <EditorToolbar @command="handleCommand" />
          
    <!-- Editor Core -->
    <div class="flex-1 overflow-y-auto bg-white custom-scrollbar">
      <div class="w-full min-h-full p-8 md:p-12">
        
        <!-- Wrapped/Floating Media Player -->
        <div 
          v-if="['audio', 'video'].includes(props.type) && props.mediaEntity" 
          :class="[
            isFloating ? 'fixed top-20 left-20 z-[9999]' : 'float-left ml-0 mr-8 mb-8 sticky top-4 z-10 w-fit h-fit'
          ]"
        >
           <ReferencePane
             :type="props.type"
             :entity="props.mediaEntity"
             :is-integrated="!isFloating"
             @toggle-dock="toggleDock"
             @timeupdate="(t) => store.updateCurrentTime(t)"
           />
        </div>

        <TiptapEditor
          :key="store.contentVersion"
          v-model="store.content"
          @set-editor="store.setEditor"
        />
      </div>
    </div>
    
    <!-- Extensions UI -->
    <FootnoteEditor />
  </div>
</template>
