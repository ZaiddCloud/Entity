<script setup>
import { ref } from 'vue'
import ToolbarButton from '../Components/ToolbarButton.vue'
import { useEditorStore } from '@/Technologies/Store/EditorStore'
import { useMediaStore } from '@/Technologies/Store/MediaStore'
import { exportEntity, exportToSRT } from '@/Core/Sync/dataPortability'

const store = useEditorStore()
const mediaStore = useMediaStore()
const isOpen = ref(false)

const handleExport = async (format) => {
    isOpen.value = false
    
    if (!store.currentEntity) {
        window.notifySync?.('لا يوجد كيان نشط للتصدير', 'error')
        return
    }

    try {
        if (format === 'srt') {
            // For audio/video, true segments with timing are in mediaStore
            const segments = (mediaStore.segments && mediaStore.segments.length > 0) 
                ? mediaStore.segments 
                : (Array.isArray(store.content) ? store.content : [])
            
            console.log(`[PortabilityGroup] Triggering SRT export for ${segments.length} segments`);
            await exportToSRT(store.currentEntity, segments)
        } else {
            // For books/manuscripts, we must ensure the live editor content is included
            let blocks = []
            
            // Single page: clone the metadata but inject LIVE content from store
            const activeBlock = { 
                ...store.currentContentNode,
                content: store.content, // Live HTML from store
                plain_text: store.editor?.getText() || '' // Live text from tiptap
            }

            if (store.currentContentNode.id === 'full') {
                // If in full view, use hierarchy but ensure we fallback if it's empty
                blocks = (store.hierarchy && store.hierarchy.length > 0) ? store.hierarchy : [activeBlock]
            } else {
                blocks = [activeBlock]
            }
            
            console.log(`[PortabilityGroup] Triggering export for ${blocks.length} blocks`);
            await exportEntity(store.currentEntity, blocks, format)
        }
        window.notifySync?.('✅ تم التصدير بنجاح', 'success')
    } catch (error) {
        console.error('Export failed:', error)
        window.notifySync?.('❌ فشل التصدير: ' + error.message, 'error')
    }
}
</script>

<template>
  <div class="relative">
    <ToolbarButton 
      label="تصدير" 
      icon="📤"
      :active="isOpen"
      title="تصدير المحتوى بصيغ متعددة" 
      @click="isOpen = !isOpen"
    />
        
    <!-- Dropdown Menu -->
    <div
      v-if="isOpen"
      class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl py-2 min-w-[200px] z-50 flex flex-col gap-1 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200"
    >
      <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center border-b border-gray-50 mb-1">
        صيغ التصدير المتاحة
      </div>
      
      <button
        class="group px-4 py-2.5 text-right hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 transition-colors flex items-center justify-between"
        @click="handleExport('markdown')"
      >
        <span class="text-sm font-medium">Export to Markdown</span>
        <span class="text-xs px-2 py-0.5 bg-gray-100 rounded text-gray-500 group-hover:bg-emerald-100 group-hover:text-emerald-600">.md</span>
      </button>

      <button
        class="group px-4 py-2.5 text-right hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition-colors flex items-center justify-between"
        @click="handleExport('json')"
      >
        <span class="text-sm font-medium">Export to JSON</span>
        <span class="text-xs px-2 py-0.5 bg-gray-100 rounded text-gray-500 group-hover:bg-blue-100 group-hover:text-blue-600">.json</span>
      </button>

      <button
        v-if="store.editorMode === 'audio' || store.editorMode === 'video'"
        class="group px-4 py-2.5 text-right hover:bg-orange-50 text-gray-700 hover:text-orange-700 transition-colors flex items-center justify-between"
        @click="handleExport('srt')"
      >
        <span class="text-sm font-medium">Export to SRT (Subtitles)</span>
        <span class="text-xs px-2 py-0.5 bg-gray-100 rounded text-gray-500 group-hover:bg-orange-100 group-hover:text-orange-600">.srt</span>
      </button>

      <button
        class="group px-4 py-2.5 text-right hover:bg-gray-50 text-gray-700 hover:text-gray-900 transition-colors flex items-center justify-between"
        @click="handleExport('text')"
      >
        <span class="text-sm font-medium">Plain Text (TXT)</span>
        <span class="text-xs px-2 py-0.5 bg-gray-100 rounded text-gray-500 group-hover:bg-gray-200">.txt</span>
      </button>
      
      <div class="mt-2 p-2 bg-gray-50/50 border-t border-gray-100 text-center">
        <p class="text-[9px] text-gray-400 uppercase">Sovereignty Protocol Active</p>
      </div>
    </div>
        
    <!-- Backdrop to close -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40"
      @click="isOpen = false"
    />
  </div>
</template>
