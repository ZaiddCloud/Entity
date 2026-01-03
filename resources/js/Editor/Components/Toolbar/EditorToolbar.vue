<script setup>
import { ref } from 'vue'
import { useEditorStore } from '../../Store/editorStore'
import WindowControls from './WindowControls.vue'
import FilenameBadge from './FilenameBadge.vue'
import MegaMenu from './MegaMenu.vue'
import ToolbarItem from './ToolbarItem.vue'
import { TOOLBAR_COMMANDS } from '../../Constants/toolbarItems'

const emit = defineEmits(['command'])
const store = useEditorStore()

const executeCommand = (command, value = null) => {
    emit('command', { command, value })
}

// Special command for links
const createLink = () => {
    const url = prompt('أدخل الرابط:', 'https://')
    if (url) executeCommand('setLink', url)
}
</script>

<template>
    <header class="glass-toolbar z-50 fixed top-0 left-0 right-0 px-4 flex items-center justify-between" dir="rtl">
        <!-- يمين: اسم الملف -->
        <FilenameBadge :filename="store.documentTitle" />

        <!-- وسط: الأقسام الخمسة -->
        <div class="flex-1 flex items-center justify-center px-4 gap-2">
            
            <!-- 1. التنظيم -->
            <div class="relative group px-3 py-1 cursor-pointer rounded hover:bg-gray-100 transition-colors">
                <span class="text-sm font-medium text-gray-700">هيكلية</span>
                <MegaMenu width="w-48">
                    <ToolbarItem label="فقرة" command="setParagraph" @click="executeCommand('setParagraph')" />
                    <div class="h-px bg-gray-100 my-1"></div>
                    <ToolbarItem label="📚 كتاب فرعي" highlight />
                    <ToolbarItem label="📑 جزء" highlight />
                    <ToolbarItem label="🚪 باب" highlight />
                    <ToolbarItem label="📂 فصل" />
                    <ToolbarItem label="💡 مسألة" class="italic" />
                    <div class="h-px bg-gray-100 my-1"></div>
                    <ToolbarItem label="🗺️ عرض الشجرة" />
                    <ToolbarItem label="📍 القسم الحالي" active />
                </MegaMenu>
            </div>

            <div class="w-px h-4 bg-gray-300 mx-1"></div>

            <!-- 2. تراث -->
            <div class="relative group px-3 py-1 cursor-pointer rounded hover:bg-gray-100 transition-colors">
                <span class="text-sm font-medium text-amber-700">تراث</span>
                <MegaMenu width="w-56">
                    <p class="text-[10px] text-amber-600 px-2 py-1 font-bold">أدوات تراثية</p>
                    <ToolbarItem label="✒️ بيت شعر" @click="executeCommand('insertPoetry')" />
                    <ToolbarItem label="📖 آية (رسم عثماني)" @click="executeCommand('insertQuranic')" />
                    <ToolbarItem label="🏛️ سند/متن" />
                    <div class="h-px bg-gray-100 my-1"></div>
                    <p class="text-[10px] text-blue-600 px-2 py-1 font-bold">تحقيق علمي</p>
                    <ToolbarItem label="📑 إدارة الحواشي" />
                    <ToolbarItem label="📌 إدراج حاشية" @click="executeCommand('insertFootnote')" />
                    <ToolbarItem label="⏳ ختم زمن" />
                </MegaMenu>
            </div>

            <!-- 3. أدوات -->
            <div class="relative group px-3 py-1 cursor-pointer rounded hover:bg-gray-100 transition-colors">
                <span class="text-sm font-medium text-gray-700">أدوات</span>
                <MegaMenu width="w-64">
                    <div class="p-2">
                        <input type="text" placeholder="بحث..." class="w-full text-xs p-1.5 border rounded mb-1 text-right bg-gray-50 focus:bg-white transition-colors outline-none focus:border-blue-400">
                        <input type="text" placeholder="استبدال..." class="w-full text-xs p-1.5 border rounded mb-2 text-right bg-gray-50 focus:bg-white transition-colors outline-none focus:border-blue-400">
                        <button class="w-full bg-blue-600 text-white text-xs font-bold py-1 px-2 rounded hover:bg-blue-700 transition-colors">تنفيذ</button>
                    </div>
                    <div class="h-px bg-gray-100 my-1"></div>
                    <ToolbarItem label="💾 حفظ" icon="" shortcut="Ctrl+S" active @click="executeCommand('save')" />
                    <div class="relative group/sub">
                        <ToolbarItem label="📤 تصدير" />
                        <!-- Sub menu could be added here -->
                    </div>
                     <ToolbarItem label="⚙️ إعدادات" />
                </MegaMenu>
            </div>

            <!-- 4. الأساسيات -->
             <div class="relative group px-3 py-1 cursor-pointer rounded hover:bg-gray-100 transition-colors">
                <span class="text-sm font-medium text-gray-700">الأساسيات</span>
                <MegaMenu width="w-56">
                    <div class="grid grid-cols-2 gap-1">
                        <ToolbarItem label="تراجع" icon="↩️" shortcut="Ctrl+Z" @click="executeCommand('undo')" />
                        <ToolbarItem label="إعادة" icon="↪️" shortcut="Ctrl+Y" @click="executeCommand('redo')" />
                    </div>
                    <div class="h-px bg-gray-100 my-1"></div>
                    <ToolbarItem label="قص" icon="✂️" />
                    <ToolbarItem label="نسخ" icon="📋" />
                    <ToolbarItem label="لصق" icon="📥" />
                    <div class="h-px bg-gray-100 my-1"></div>
                    <div class="grid grid-cols-2 gap-1">
                        <ToolbarItem label="عريض" icon="B" shortcut="Ctrl+B" font-bold @click="executeCommand('bold')" :active="store.isActive('bold')" />
                        <ToolbarItem label="مائل" icon="I" shortcut="Ctrl+I" italic @click="executeCommand('italic')" :active="store.isActive('italic')" />
                        <ToolbarItem label="تسطير" icon="U" shortcut="Ctrl+U" underline @click="executeCommand('underline')" :active="store.isActive('underline')" />
                         <ToolbarItem label="مسح" icon="✨" danger @click="executeCommand('unsetAllMarks')" />
                    </div>
                     <div class="h-px bg-gray-100 my-1"></div>
                     <div class="grid grid-cols-4 gap-1">
                         <button class="text-lg hover:bg-gray-100 rounded" title="يمين" @click="executeCommand('setTextAlign', 'right')">➡️</button>
                         <button class="text-lg hover:bg-gray-100 rounded" title="توسيط" @click="executeCommand('setTextAlign', 'center')">↔️</button>
                         <button class="text-lg hover:bg-gray-100 rounded" title="يسار" @click="executeCommand('setTextAlign', 'left')">⬅️</button>
                         <button class="text-lg hover:bg-gray-100 rounded" title="ضبط" @click="executeCommand('setTextAlign', 'justify')">↕️</button>
                     </div>
                </MegaMenu>
            </div>

            <div class="w-px h-4 bg-gray-300 mx-1"></div>

            <!-- 5. المرفقات -->
            <div class="relative group px-3 py-1 cursor-pointer rounded hover:bg-gray-100 transition-colors">
                <span class="text-sm font-bold text-blue-600">المرفقات</span>
                <MegaMenu width="w-48">
                    <ToolbarItem label="🖼️ صورة" @click="executeCommand('insertImage')" />
                    <ToolbarItem label="🎧 صوت" @click="executeCommand('insertAudio')" />
                    <ToolbarItem label="🎬 فيديو" @click="executeCommand('insertVideo')" />
                    <div class="h-px bg-gray-100 my-1"></div>
                    <ToolbarItem label="🔗 رابط تشعبي" @click="createLink" />
                    <ToolbarItem label="📊 جدول" />
                </MegaMenu>
            </div>

             <!-- 6. نوع المحرر -->
            <div class="relative group px-3 py-1 cursor-pointer rounded hover:bg-gray-100 transition-colors ml-auto mr-4">
                <span class="text-sm font-bold text-purple-700">نوع المحرر</span>
                 <MegaMenu width="w-40">
                    <ToolbarItem label="📖 كتاب" :active="store.editorMode === 'book'" @click="executeCommand('setMode', 'book')" />
                    <ToolbarItem label="🎤 صوت" :active="store.editorMode === 'audio'" @click="executeCommand('setMode', 'audio')" />
                    <ToolbarItem label="📜 مخطوط" :active="store.editorMode === 'manuscript'" @click="executeCommand('setMode', 'manuscript')" />
                </MegaMenu>
            </div>

        </div>

        <!-- يسار: أزرار التحكم -->
        <div class="flex items-center gap-2">
            <div class="w-px h-4 bg-gray-300 mx-2"></div>
            <WindowControls @pin="store.togglePin" :is-pinned="store.isToolbarPinned" />
        </div>
    </header>
</template>

<style scoped>
.glass-toolbar {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(229, 231, 235, 0.5);
    height: 42px;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s;
}

/* Prevent text selection on toolbar */
.glass-toolbar {
    user-select: none;
    -webkit-app-region: drag;
}

/* Allow clicking on buttons */
.glass-toolbar button, 
.glass-toolbar .cursor-pointer,
.glass-toolbar input {
    -webkit-app-region: no-drag;
}
</style>
