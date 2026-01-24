<script setup>
import { BubbleMenu } from '@tiptap/vue-3/menus'
import ToolbarButton from '../Toolbar/Components/ToolbarButton.vue'

const props = defineProps({
    editor: {
        type: Object,
        required: true
    }
})

// Only show if selection is in a table
const shouldShow = ({ editor, view, state, oldState, from, to }) => {
    return editor.isActive('table')
}
</script>

<template>
  <bubble-menu 
    v-if="editor" 
    :editor="editor"
    :tippy-options="{ duration: 100, placement: 'bottom' }"
    :should-show="shouldShow"
    class="bg-white shadow-xl border border-blue-100 rounded-lg flex flex-col p-1 overflow-hidden min-w-[200px]"
  >
    <div class="flex items-center gap-1 p-1 bg-blue-50/50 border-b border-blue-50 mb-1">
      <span class="text-[10px] font-bold text-blue-600 px-2">أدوات الجدول</span>
    </div>
        
    <div class="flex items-center gap-0.5 justify-center">
      <ToolbarButton 
        icon="ri-table-line" 
        title="حذف الجدول" 
        :red="true"
        @click="editor.chain().focus().deleteTable().run()"
      />
      <div class="w-px h-4 bg-gray-200 mx-1" />
            
      <ToolbarButton 
        icon="ri-insert-column-left" 
        title="إضافة عمود قبل" 
        @click="editor.chain().focus().addColumnBefore().run()"
      />
      <ToolbarButton 
        icon="ri-insert-column-right" 
        title="إضافة عمود بعد" 
        @click="editor.chain().focus().addColumnAfter().run()"
      />
      <ToolbarButton 
        icon="ri-delete-column" 
        title="حذف عمود" 
        :red="true"
        @click="editor.chain().focus().deleteColumn().run()"
      />
            
      <div class="w-px h-4 bg-gray-200 mx-1" />

      <ToolbarButton 
        icon="ri-insert-row-top" 
        title="إضافة صف قبل" 
        @click="editor.chain().focus().addRowBefore().run()"
      />
      <ToolbarButton 
        icon="ri-insert-row-bottom" 
        title="إضافة صف بعد" 
        @click="editor.chain().focus().addRowAfter().run()"
      />
      <ToolbarButton 
        icon="ri-delete-row" 
        title="حذف صف" 
        :red="true"
        @click="editor.chain().focus().deleteRow().run()"
      />
    </div>
        
    <div class="flex items-center gap-0.5 justify-center mt-1 border-t border-gray-100 pt-1">
      <ToolbarButton 
        icon="ri-merge-cells-horizontal" 
        title="دمج الخلايا" 
        @click="editor.chain().focus().mergeCells().run()"
      />
      <ToolbarButton 
        icon="ri-split-cells-horizontal" 
        title="فصل الخلايا" 
        @click="editor.chain().focus().splitCell().run()"
      />
    </div>
  </bubble-menu>
</template>
