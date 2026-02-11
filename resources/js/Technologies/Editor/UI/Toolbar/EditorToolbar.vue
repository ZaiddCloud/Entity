<script setup>
import { ref } from 'vue'
import { useTiptapStore } from '@/Technologies/Editor/Core/TiptapStore'
import { TOOLBAR_COMMANDS } from '../../Core/Constants/toolbarItems'

// Modular Groups
import FormattingGroup from './Groups/FormattingGroup.vue'
import HistoryGroup from './Groups/HistoryGroup.vue'
import StructureGroup from './Groups/StructureGroup.vue'
import ListGroup from './Groups/ListGroup.vue'
import TextAlignGroup from './Groups/TextAlignGroup.vue'
import BlockGroup from './Groups/BlockGroup.vue'
import InsertGroup from './Groups/InsertGroup.vue'
import HeritageGroup from './Groups/HeritageGroup.vue'
import ScientificGroup from './Groups/ScientificGroup.vue'
import PortabilityGroup from './Groups/PortabilityGroup.vue'

import ToolbarDivider from './Components/ToolbarDivider.vue'

const emit = defineEmits(['command'])
const store = useTiptapStore()
// ... (rest of logic) ...
// In template:
// Cleaned up script block

const executeCommand = (command, value = null) => {
    store.executeCommand(command, value)
}

// Special command for links
const createLink = () => {
    const url = prompt('أدخل الرابط:', 'https://')
    if (url) executeCommand('setLink', url)
}

// Save button feedback logic
const saveState = ref('idle') // idle, saving, saved
const handleSave = () => {
    saveState.value = 'saving'
    executeCommand('save')
    setTimeout(() => {
        saveState.value = 'saved'
        setTimeout(() => {
            saveState.value = 'idle'
        }, 2000)
    }, 800)
}
const isPinned = ref(false)
const togglePin = () => {
    isPinned.value = !isPinned.value
    emit('command', { command: 'togglePin', value: isPinned.value })
}

// Window actions
const closeWindow = () => {
    if (confirm('هل تريد إغلاق المحرر؟')) {
        window.history.back()
    }
}
</script>

<template>
  <header
    id="main-toolbar"
    class="glass-editor-header flex flex-col border-b border-gray-200"
  >
    <!-- New Modular Toolbar Row (Sandbox Mode) -->
    <div class="flex items-center h-12 px-4 gap-2 border-b border-b-gray-100 bg-white overflow-visible z-50">
      <HistoryGroup dusk="history-group" />
      <ToolbarDivider />
      <StructureGroup dusk="structure-group" />
      <ToolbarDivider />
      <FormattingGroup dusk="formatting-group" />
      <ToolbarDivider />
      <ListGroup dusk="list-group" />
      <ToolbarDivider />
      <BlockGroup dusk="block-group" />
      <ToolbarDivider />
      <InsertGroup dusk="insert-group" />
      <ToolbarDivider />
      <TextAlignGroup dusk="text-align-group" />
      <ToolbarDivider />
      
      <ScientificGroup dusk="scientific-group" />
            
      <div class="w-px h-6 bg-gray-200 mx-2" />
            
      <HeritageGroup dusk="heritage-group" />
            
      <div class="h-6 w-px bg-gray-200 mx-1" />
      
      <PortabilityGroup dusk="portability-group" />

      <div class="flex-1" />
      
      <ToolbarButton
        icon="ri-save-line"
        title="حفظ التغييرات (Ctrl+S)"
        :class="{'text-emerald-600': saveState === 'saved'}"
        @click="handleSave"
        dusk="save-button"
      >
        <span v-if="saveState === 'saved'" class="text-[10px] ml-1">تم الحفظ</span>
        <span v-else-if="saveState === 'saving'" class="text-[10px] ml-1 italic opacity-50">جاري الحفظ...</span>
      </ToolbarButton>
    </div>
        
    <!-- Legacy Toolbar (disabled to prevent crashes during refactoring) -->
    <!-- 
        <div class="menu-bar flex items-center h-8 border-b border-gray-100 bg-gray-50/50">
             ... legacy content ...
        </div> 
        -->
  </header>
</template>

<style scoped>
.glass-editor-header {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(8px);
    border-bottom: 1px solid rgba(229, 231, 235, 0.5);
    z-index: 50;
    width: 100%;
    position: sticky;
    top: 0;
}

.menu-item {
    position: relative;
    cursor: pointer;
    padding: 0 0.6rem;
    height: 100%;
    display: flex;
    align-items: center;
    border-radius: 3px;
    font-size: 12px;
    color: #374151;
    transition: background 0.2s;
}

.menu-item:hover {
    background: #f3f4f6;
}

.mega-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
    z-index: 100;
    min-width: 180px;
    padding: 0.4rem;
    margin-top: 1px;
}

.menu-item:hover .mega-menu {
    display: block;
}

.icon-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    color: #4b5563;
    transition: all 0.2s;
    font-size: 14px;
}

.icon-btn:hover {
    background: #f3f4f6;
    color: #111827;
}

.icon-btn.active {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
}

.v-divider {
    width: 1px;
    height: 18px;
    background: #e5e7eb;
    margin: 0 4px;
}

.menu-grid {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.menu-btn {
    display: flex;
    align-items: center;
    width: 100%;
    text-align: right;
    padding: 6px 10px;
    font-size: 12px;
    border-radius: 4px;
    transition: background 0.2s;
}

.menu-btn:hover {
    background-color: #f3f4f6;
}

.search-input {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    font-size: 11px;
    text-align: right;
    outline: none;
    transition: border-color 0.2s;
}

.search-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
}

.execute-btn {
    width: 100%;
    background: #2563eb;
    color: white;
    padding: 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    transition: background 0.2s;
}

.execute-btn:hover {
    background: #1d4ed8;
}

.filename-badge {
    background: #f9fafb;
    border: 1px solid #f3f4f6;
    padding: 2px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    margin-right: 12px;
}

/* Window Buttons */
.win-btn {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: none;
    transition: all 0.2s;
}
.win-close { background: #ff5f56; }
.win-close:hover { background: #ff3b30; }
.win-max { background: #ffbd2e; }
.win-max:hover { background: #ffcc00; }
.win-min { background: #27c93f; }
.win-min:hover { background: #28cd41; }

.v-divider {
    width: 1px;
    height: 18px;
    background: #e5e7eb;
    margin: 0 4px;
}
</style>
