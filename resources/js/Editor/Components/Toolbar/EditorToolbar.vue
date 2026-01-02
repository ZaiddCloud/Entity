<script setup>
import { computed } from 'vue'
import { useEditorStore } from '../../Store/editorStore'
import ToolbarSection from './ToolbarSection.vue'
import WindowControls from './WindowControls.vue'
import FilenameBadge from './FilenameBadge.vue'
import { TOOLBAR_COMMANDS } from '../../Constants/toolbarItems'

const emit = defineEmits(['command'])
const store = useEditorStore()

const executeCommand = (command, value = null) => {
    emit('command', { command, value })
}

// الأقسام الخمسة الرئيسية
const basicsSection = [
    { icon: '💾', label: 'حفظ', command: 'save', shortcut: 'Ctrl+S' },
    { icon: '↩️', label: 'تراجع', command: TOOLBAR_COMMANDS.UNDO, shortcut: 'Ctrl+Z' },
    { icon: '↪️', label: 'إعادة', command: TOOLBAR_COMMANDS.REDO, shortcut: 'Ctrl+Y' },
    { type: 'separator' },
    { icon: 'B', label: 'غامق', command: TOOLBAR_COMMANDS.BOLD, shortcut: 'Ctrl+B', active: 'bold' },
    { icon: 'I', label: 'مائل', command: TOOLBAR_COMMANDS.ITALIC, shortcut: 'Ctrl+I', active: 'italic' },
    { icon: 'U', label: 'تسطير', command: TOOLBAR_COMMANDS.UNDERLINE, shortcut: 'Ctrl+U', active: 'underline' },
]

const organizationSection = [
    { 
        type: 'dropdown', 
        label: 'هيكلية', 
        icon: '📋',
        default: 'فقرة',
        items: [
            { label: 'فقرة', value: 'paragraph', command: 'setParagraph' },
            { label: 'عنوان 1', value: 'h1', command: 'setHeading', args: { level: 1 } },
            { label: 'عنوان 2', value: 'h2', command: 'setHeading', args: { level: 2 } },
            { label: 'عنوان 3', value: 'h3', command: 'setHeading', args: { level: 3 } },
        ]
    },
    { icon: '📝', label: 'قائمة نقطية', command: TOOLBAR_COMMANDS.BULLET_LIST },
    { icon: '🔢', label: 'قائمة مرقمة', command: TOOLBAR_COMMANDS.ORDERED_LIST },
    { type: 'separator' },
    { icon: '➡️', label: 'محاذاة يمين', command: TOOLBAR_COMMANDS.ALIGN_RIGHT },
    { icon: '↔️', label: 'توسيط', command: TOOLBAR_COMMANDS.ALIGN_CENTER },
    { icon: '⬅️', label: 'محاذاة يسار', command: TOOLBAR_COMMANDS.ALIGN_LEFT },
    { icon: '↕️', label: 'ضبط', command: TOOLBAR_COMMANDS.ALIGN_JUSTIFY },
]

const attachmentsSection = [
    { icon: '🖼️', label: 'صورة', command: 'insertImage' },
    { icon: '🎵', label: 'صوت', command: 'insertAudio' },
    { icon: '🎬', label: 'فيديو', command: 'insertVideo' },
    { type: 'separator' },
    { icon: '📖', label: 'شعر', command: 'insertPoetry' },
    { icon: '📿', label: 'آية قرآنية', command: 'insertQuranic' },
    { icon: '📌', label: 'حاشية', command: 'insertFootnote' },
]

const editorModeSection = [
    { icon: '✏️', label: 'تحرير', command: 'setMode', args: 'edit', active: store.editorMode === 'edit' },
    { icon: '👁️', label: 'معاينة', command: 'setMode', args: 'preview', active: store.editorMode === 'preview' },
    { icon: '⚡', label: 'مزدوج', command: 'setMode', args: 'split', active: store.editorMode === 'split' },
]

const utilitiesSection = [
    { icon: '🔍', label: 'بحث', command: 'search', shortcut: 'Ctrl+F' },
    { icon: '📤', label: 'تصدير', command: 'export' },
    { icon: '⚙️', label: 'إعدادات', command: 'settings' },
]
</script>

<template>
    <div class="editor-toolbar bg-white border-b border-gray-200 shadow-sm" dir="rtl">
        <!-- الصف الأول: شارة اسم الملف + أزرار النافذة -->
        <div class="flex items-center justify-between h-8 px-4 bg-gray-50 border-b border-gray-100">
            <FilenameBadge :filename="store.documentTitle" />
            <WindowControls @pin="store.togglePin" :is-pinned="store.isToolbarPinned" />
        </div>

        <!-- الصف الثاني: الأقسام الخمسة -->
        <div class="flex items-center gap-6 px-4 py-2">
            <!-- القسم 1: الأساسيات -->
            <ToolbarSection 
                title="الأساسيات" 
                :items="basicsSection" 
                @command="executeCommand"
            />

            <div class="w-px h-8 bg-gray-300"></div>

            <!-- القسم 2: التنظيم -->
            <ToolbarSection 
                title="التنظيم" 
                :items="organizationSection" 
                @command="executeCommand"
            />

            <div class="w-px h-8 bg-gray-300"></div>

            <!-- القسم 3: المرفقات -->
            <ToolbarSection 
                title="المرفقات" 
                :items="attachmentsSection" 
                @command="executeCommand"
            />

            <div class="w-px h-8 bg-gray-300"></div>

            <!-- القسم 4: وضع المحرر -->
            <ToolbarSection 
                title="الوضع" 
                :items="editorModeSection" 
                @command="executeCommand"
            />

            <div class="w-px h-8 bg-gray-300"></div>

            <!-- القسم 5: الأدوات -->
            <ToolbarSection 
                title="الأدوات" 
                :items="utilitiesSection" 
                @command="executeCommand"
            />
        </div>
    </div>
</template>

<style scoped>
.editor-toolbar {
    user-select: none;
    -webkit-app-region: drag;
}

.editor-toolbar button,
.editor-toolbar select {
    -webkit-app-region: no-drag;
}
</style>
