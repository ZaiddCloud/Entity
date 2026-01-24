<script setup>
import ToolbarButton from '../Components/ToolbarButton.vue'
import { useTiptapStore } from '@/Technologies/Editor/Core/TiptapStore'

const store = useTiptapStore()
</script>

<template>
  <div class="flex items-center gap-0.5">
    <ToolbarButton 
      icon="ri-arrow-go-back-line" 
      title="تراجع (Ctrl+Z)" 
      :disabled="!store.editor?.can().undo()" 
      @click="store.executeCommand('undo')"
    />
    <ToolbarButton 
      icon="ri-arrow-go-forward-line" 
      title="إعادة (Ctrl+Y)" 
      :disabled="!store.editor?.can().redo()" 
      @click="store.executeCommand('redo')"
    />
  </div>
</template>
