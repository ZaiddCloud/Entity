<script setup>
import { BubbleMenu } from '@tiptap/vue-3/menus'
import ToolbarButton from './Toolbar/Components/ToolbarButton.vue'
import { useTiptapStore } from '../Core/TiptapStore'

const props = defineProps({
    editor: {
        type: Object,
        required: true
    }
})

const store = useTiptapStore()
</script>

<template>
  <bubble-menu 
    v-if="editor" 
    :editor="editor"
    :tippy-options="{ duration: 100 }"
    class="bg-white shadow-lg border border-gray-200 rounded-lg flex items-center p-1 gap-0.5 overflow-hidden"
  >
    <ToolbarButton 
      icon="ri-bold" 
      :active="editor.isActive('bold')" 
      @click="editor.chain().focus().toggleBold().run()"
    />
    <ToolbarButton 
      icon="ri-italic" 
      :active="editor.isActive('italic')" 
      @click="editor.chain().focus().toggleItalic().run()"
    />
    <ToolbarButton 
      icon="ri-underline" 
      :active="editor.isActive('underline')" 
      @click="editor.chain().focus().toggleUnderline().run()"
    />
    <ToolbarButton 
      icon="ri-strikethrough" 
      :active="editor.isActive('strike')" 
      @click="editor.chain().focus().toggleStrike().run()"
    />
    <div class="w-px h-4 bg-gray-200 mx-1" />
    <ToolbarButton 
      icon="ri-mark-pen-line" 
      :active="editor.isActive('highlight')" 
      @click="editor.chain().focus().toggleHighlight().run()"
    />
    <ToolbarButton 
      icon="ri-code-line" 
      :active="editor.isActive('code')" 
      @click="editor.chain().focus().toggleCode().run()"
    />
  </bubble-menu>
</template>
