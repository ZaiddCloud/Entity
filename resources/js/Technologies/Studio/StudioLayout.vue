<script setup>
import { Head } from '@inertiajs/vue3'
import SplitPane from './Layouts/SplitPane.vue'
import ReferencePane from './Panes/ReferencePane.vue'
import EditorPane from './Panes/EditorPane.vue'

const props = defineProps({
    type: { type: String, required: true }, // 'manuscript' | 'audio' | 'video'
    entity: { type: Object, required: true },
    editorContent: { type: String, default: '' },
    title: { type: String, default: 'Entity Studio' }
})
</script>

<template>
  <Head :title="title" />
  
  <div class="h-screen w-screen overflow-hidden bg-gray-950 flex flex-col font-ui text-right" dir="rtl">
    <!-- 
        Global Studio Header 
        (Top Bar: Navigation, Save, User, etc.)
     -->
    <header class="h-12 bg-[#1e1e1e] border-b border-gray-800 flex items-center justify-between px-4 shrink-0 z-50">
      <div class="flex items-center gap-4">
        <!-- Logo / Home -->
        <a href="/dashboard" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
          <div class="w-6 h-6 bg-lime-500 rounded-md flex items-center justify-center text-black font-bold text-xs">
            ES
          </div>
          <span class="font-bold text-sm tracking-wide hidden sm:inline">Entity Studio</span>
        </a>

        <!-- Divider -->
        <div class="h-4 w-px bg-gray-700"></div>

        <!-- Entity Title -->
        <div class="flex items-center gap-2">
            <span class="text-xs px-1.5 py-0.5 rounded bg-gray-800 text-gray-300 border border-gray-700">
                {{ props.type === 'manuscript' ? 'مخطوط' : (props.type === 'audio' ? 'صوت' : 'فيديو') }}
            </span>
            <h1 class="text-sm font-medium text-white truncate max-w-[200px] md:max-w-md">
                {{ props.entity.title || props.entity.original_title || 'بدون عنوان' }}
            </h1>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-3">
        <!-- Placeholder for Save Status -->
        <span class="text-xs text-green-500 flex items-center gap-1">
            <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
            محفوظ
        </span>
        
        <button class="bg-blue-600 hover:bg-blue-500 text-white text-xs px-3 py-1.5 rounded transition-colors">
            نشر
        </button>
      </div>
    </header>

    <!-- 
        Main Workspace (Split Pane) 
        Pane 1 (Right in RTL): Editor (Constant) - Wait, PLAN said Editor is Right Pane.
        Let's check the Plan: "Right Pane (Constant): The Smart Editor... Left Pane (Variable): Chameleon Window".
        In RTL, Right looks like Start.
        So Pane 1 should be Editor.
        Pane 2 should be Reference.
        
        Let's configure SplitPane.vue accordingly.
        SplitPane slots: 'pane-1', 'pane-2'.
        Pane 1 is Start. Pane 2 is End.
    -->
    <div class="flex-1 overflow-hidden relative">
      <SplitPane :initial-split="40" :min-size="20">
        <!-- 
            PANE 1: Right Pane (Editor) 
            This is the constant component.
        -->
        <template #pane-1>
          <EditorPane :initial-content="props.editorContent" />
        </template>

        <!-- 
            PANE 2: Left Pane (Reference)
            This is the variable component (Chameleon).
        -->
        <template #pane-2>
          <ReferencePane
            :type="props.type"
            :entity="props.entity" 
          />
        </template>
      </SplitPane>
    </div>
  </div>
</template>
