<script setup>
import { useTiptapStore } from '@/Technologies/Editor/Core/TiptapStore'
import ToolbarButton from '../Components/ToolbarButton.vue'

const store = useTiptapStore()

const insertFootnote = () => {
    store.executeCommand('setFootnote', { type: 'comment' })
}
</script>

<template>
  <div class="flex items-center gap-1">
    <ToolbarButton
        icon="footnote"
        title="إضافة حاشية علمية"
        :is-active="store.isActive('scientificFootnote')"
        @click="insertFootnote"
    >
        <span class="text-xs font-bold font-serif opacity-70">1</span>
        <sup class="text-[8px] -mt-2">ح</sup>
    </ToolbarButton>
  </div>
</template>
