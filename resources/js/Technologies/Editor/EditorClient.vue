<script setup>
import { onMounted, watch } from 'vue'
import TiptapEditor from './Core/TiptapEditor.vue'
import EditorToolbar from './Toolbar/EditorToolbar.vue'
import FootnoteEditor from './Extensions/Footnotes/FootnoteEditor.vue'
import { useEditorStore } from './Core/EditorStore'

const props = defineProps({
    initialContent: {
        type: [String, Object, Array],
        default: ''
    }
})

const store = useEditorStore()

// Update store if prop changes (e.g. loading new entity)
watch(() => props.initialContent, (newVal) => {
    if (newVal !== store.content) {
        store.updateContent(newVal)
    }
})

const handleCommand = ({ command, value }) => {
    store.executeCommand(command, value)
}
</script>

<template>
  <div class="flex-1 flex flex-col bg-white overflow-hidden relative h-full w-full">
    <!-- Toolbar -->
    <EditorToolbar @command="handleCommand" />
          
    <!-- Editor Core -->
    <div class="flex-1 overflow-y-auto bg-white">
      <div class="w-full min-h-full p-8 md:p-12">
        <TiptapEditor
          v-model="store.content"
          @set-editor="store.setEditor"
        />
      </div>
    </div>
    
    <!-- Extensions UI -->
    <FootnoteEditor />
  </div>
</template>
