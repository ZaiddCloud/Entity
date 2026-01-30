<script setup>
import ToolbarButton from '../Components/ToolbarButton.vue'
import { useTiptapStore } from '@/Technologies/Editor/Core/TiptapStore'

const store = useTiptapStore()
</script>

<template>
  <div class="flex items-center gap-1">
    <!-- Heritage Poetry -->
    <ToolbarButton 
      title="إدراج بيت شعر هوياتي" 
      @click="store.executeCommand('insertHeritagePoetry')" 
    >
      <div class="flex flex-col items-center leading-none">
        <span class="text-[10px] font-bold">═</span>
        <span class="text-[10px] font-bold">═</span>
      </div>
    </ToolbarButton>

    <!-- Quranic Verse -->
    <ToolbarButton 
      title="إدراج آية قرآنية" 
      @click="store.executeCommand('insertQuranicVerse')" 
    >
      <span class="text-sm font-bold opacity-80">﴿ ﴾</span>
    </ToolbarButton>

    <!-- Heritage Footnote (Scientific) -->
    <ToolbarButton 
      title="إدراج حاشية تراثية" 
      @click="store.executeCommand('insertFootnote')" 
    >
      <span class="text-xs font-bold">[*]</span>
    </ToolbarButton>
  </div>
</template>
