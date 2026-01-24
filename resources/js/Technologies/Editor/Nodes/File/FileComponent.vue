<script setup>
import { NodeViewWrapper } from '@tiptap/vue-3'

const props = defineProps(['node', 'updateAttributes'])

const downloadFile = () => {
    // In a real app, this would be a proper download link
    const link = document.createElement('a')
    link.href = props.node.attrs.src
    link.download = props.node.attrs.filename
    link.click()
}
</script>

<template>
  <NodeViewWrapper class="file-attachment my-4">
    <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg max-w-sm hover:bg-gray-100 transition-colors">
      <!-- Icon -->
      <div class="w-10 h-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-lg shrink-0">
        <i class="ri-file-text-line text-xl" />
      </div>
            
      <!-- Info -->
      <div class="flex-1 min-w-0">
        <p
          class="text-sm font-medium text-gray-900 truncate"
          :title="node.attrs.filename"
        >
          {{ node.attrs.filename }}
        </p>
        <button
          class="text-xs text-blue-600 hover:underline mt-0.5"
          @click="downloadFile"
        >
          تحميل الملف ⬇️
        </button>
      </div>
    </div>
  </NodeViewWrapper>
</template>
