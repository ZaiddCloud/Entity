<script setup>
import EditorClient from './EditorClient.vue'
import { useEditorStore } from '@/Technologies/Store/EditorStore'
import { onMounted } from 'vue'

const store = useEditorStore()

const sampleContent = '<p>مرحباً بك في بيئة المحرر المعزولة (Sandbox)! 🧪</p><p>يمكنك تجربة كل خصائص المحرر هنا بحرية.</p>'

// Mock Data for Store to enable Toolbar interactions
onMounted(() => {
    store.setEditorMode('book')
    store.loadDocument(
        { id: 999, title: 'Sandbox Entity' },
        { id: 1, slug: 'sandbox-slug', title: 'Sandbox Page', content: sampleContent }
    )
})

const handleMockSave = () => {
    if (store.editor) {
        console.log('--- Editor JSON Output ---')
        console.log(JSON.stringify(store.editor.getJSON(), null, 2))
        alert('تم طباعة الـ JSON في الكونسول للفحص')
    }
}
</script>

<template>
  <div
    class="h-screen bg-gray-50 font-ui flex flex-col overflow-hidden"
    dir="rtl"
  >
    <!-- Sandbox Header (Minimal) -->
    <div class="flex items-center justify-between px-4 py-2 bg-white border-b border-gray-200 shrink-0 z-40">
      <div class="flex items-center gap-3">
        <span class="text-xl">🏗️</span>
        <div>
          <h1 class="text-sm font-bold text-gray-800 leading-none">
            مختبر المحرر
          </h1>
          <p class="text-[10px] text-gray-400 leading-none mt-0.5">
            Sandbox Environment
          </p>
        </div>
      </div>
      <div class="flex gap-2">
        <button class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-50 text-xs">
          تصفير
        </button>
        <button 
            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs shadow-sm"
            @click="handleMockSave"
        >
          حفظ وهمي (Log JSON)
        </button>
      </div>
    </div>

    <!-- The Editor Assembly (Full Screen) -->
    <div class="flex-1 overflow-hidden">
      <EditorClient :initial-content="sampleContent" />
    </div>
  </div>
</template>

<style>
.font-ui {
    font-family: 'Inter', 'Noto Sans Arabic', sans-serif;
}
</style>
