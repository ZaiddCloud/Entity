<script setup>
import ToolbarButton from '../Components/ToolbarButton.vue'
import { useTiptapStore } from '@/Technologies/Editor/Core/TiptapStore'

const store = useTiptapStore()
</script>

<template>
  <div class="flex items-center gap-0.5">
    <ToolbarButton 
      icon="ri-align-right" 
      title="محاذاة لليمين" 
      :active="store.isActive({ textAlign: 'right' })" 
      @click="store.executeCommand('textAlign', 'right')" 
    />
    <ToolbarButton 
      icon="ri-align-center" 
      title="توسيط" 
      :active="store.isActive({ textAlign: 'center' })" 
      @click="store.executeCommand('textAlign', 'center')" 
    />
    <ToolbarButton 
      icon="ri-align-left" 
      title="محاذاة لليسار" 
      :active="store.isActive({ textAlign: 'left' })" 
      @click="store.executeCommand('textAlign', 'left')" 
    />
  </div>
</template>
