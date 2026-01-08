<script setup>
import { ref } from 'vue'
import { useEditorStore } from '../Core/EditorStore'
import { TOOLBAR_COMMANDS } from '../Core/Constants/toolbarItems'

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
    <header id="main-toolbar" class="glass-editor-header flex flex-col border-b border-gray-200">
        <!-- Row 1: Menu Bar (TinyMCE Style) -->
        <div class="menu-bar flex items-center h-8 border-b border-gray-100 bg-gray-50/50">
            <div class="max-w-7xl mx-auto w-full flex items-center px-4">
                <!-- Pro Window Controls (Right side in RTL) -->
                <div class="flex items-center gap-1 ml-auto pl-2">
                    <!-- Pin Button -->
                    <button 
                        @click="togglePin" 
                        class="w-7 h-7 flex items-center justify-center hover:bg-gray-100 rounded-full transition-colors text-[12px]"
                        :class="{'bg-blue-50 text-blue-600 border border-blue-100': isPinned}"
                        title="تثبيت الشريط"
                    >
                        📌
                    </button>
                    
                    <div class="v-divider h-3 mx-1 opacity-50"></div>

                    <!-- Minimize -->
                    <button class="win-btn win-min" title="تصغير" @click="emit('command', { command: 'minimize' })"></button>
                    <!-- Maximize -->
                    <button class="win-btn win-max" title="تكبير" @click="emit('command', { command: 'maximize' })"></button>
                    <!-- Close -->
                    <button class="win-btn win-close" title="إغلاق" @click="closeWindow"></button>
                </div>

                <!-- Filename Badge (Centered) -->
                <div class="flex items-center mx-auto scale-95 opacity-80 hover:opacity-100 transition-all cursor-default">
                    <div class="filename-badge shadow-sm bg-blue-50/50 border-blue-100 text-blue-900">
                        {{ store.documentTitle }}
                    </div>
                </div>

                <!-- Main Menu Sections (Shifted Inward - Left side in RTL) -->
                <div class="flex items-center gap-1 mr-auto pr-2">
                    <!-- 1. ملف (File) -->
                    <div class="menu-item">
                        ملف
                        <div class="mega-menu">
                            <div class="menu-grid">
                                <button class="menu-btn">✨ جديد</button>
                                <button class="menu-btn" @click="handleSave">💾 حفظ</button>
                                <button class="menu-btn">🖨️ طباعة</button>
                                <div class="divider"></div>
                                <button class="menu-btn text-[10px]">🕒 تاريخ المراجعات</button>
                            </div>
                        </div>
                    </div>

                    <!-- 2. تعديل (Edit) -->
                    <div class="menu-item">
                        تعديل
                        <div class="mega-menu">
                            <div class="menu-grid">
                                <button class="menu-btn" @click="executeCommand('undo')">↩️ تراجع <span class="kb">Ctrl+Z</span></button>
                                <button class="menu-btn" @click="executeCommand('redo')">↪️ إعادة <span class="kb">Ctrl+Y</span></button>
                                <div class="divider"></div>
                                <button class="menu-btn">✂️ قص</button>
                                <button class="menu-btn">📋 نسخ</button>
                                <button class="menu-btn">📥 لصق</button>
                                <div class="divider"></div>
                                <button class="menu-btn">✔️ تحديد الكل</button>
                            </div>
                        </div>
                    </div>

                    <!-- 3. هيكلية (Structure) -->
                    <div class="menu-item">
                        هيكلية
                        <div class="mega-menu">
                            <div class="menu-grid">
                                <button class="menu-btn font-bold text-blue-600" @click="executeCommand('setParagraph')">فقرة عادية</button>
                                <div class="divider"></div>
                                <button class="menu-btn font-bold text-blue-800">📚 كتاب فرعي</button>
                                <button class="menu-btn font-bold text-blue-700">📑 جزء</button>
                                <button class="menu-btn font-bold text-blue-600">🚪 باب</button>
                                <button class="menu-btn text-blue-500">📂 فصل</button>
                                <button class="menu-btn italic">💡 مسألة</button>
                            </div>
                        </div>
                    </div>

                    <!-- 4. تراث (Heritage) -->
                    <div class="menu-item text-amber-800">
                        تراث
                        <div class="mega-menu w-56">
                            <div class="menu-grid">
                                <button class="menu-btn text-right" @click="executeCommand('insertPoetry')">✒️ بيت شعر (صدر/عجز)</button>
                                <button class="menu-btn text-right" @click="executeCommand('insertQuranic')">📖 آية (رسم عثماني)</button>
                            </div>
                        </div>
                    </div>

                    <!-- 5. تنسيق (Format) -->
                    <div class="menu-item">
                        تنسيق
                        <div class="mega-menu">
                            <div class="menu-grid">
                                <button class="menu-btn font-bold" @click="executeCommand('bold')">B عريض</button>
                                <button class="menu-btn italic" @click="executeCommand('italic')">I مائل</button>
                                <button class="menu-btn underline" @click="executeCommand('underline')">U تحت خط</button>
                                <div class="divider"></div>
                                <button class="menu-btn text-red-500" @click="executeCommand('unsetAllMarks')">✨ مسح التنسيق</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Icon Toolbar (Frequent Actions) -->
        <div class="icon-toolbar flex items-center h-10 bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto w-full flex items-center px-4 gap-0.5">
                <!-- Save & History -->
                <button class="icon-btn" @click="handleSave" :title="saveState === 'idle' ? 'حفظ' : 'جاري الحفظ'">
                    <span v-if="saveState === 'idle'">💾</span>
                    <span v-else-if="saveState === 'saving'" class="animate-pulse">⌛</span>
                    <span v-else>✅</span>
                </button>
                <div class="v-divider"></div>
                <button class="icon-btn" @click="executeCommand('undo')" title="تراجع">↩️</button>
                <button class="icon-btn" @click="executeCommand('redo')" title="إعادة">↪️</button>
                
                <div class="v-divider"></div>
                
                <!-- Basic Formatting -->
                <div class="flex items-center gap-0.5">
                    <button class="icon-btn font-bold" @click="executeCommand('bold')" :class="{'active': store.isActive('bold')}" title="عريض">B</button>
                    <button class="icon-btn italic" @click="executeCommand('italic')" :class="{'active': store.isActive('italic')}" title="مائل">I</button>
                    <button class="icon-btn underline" @click="executeCommand('underline')" :class="{'active': store.isActive('underline')}" title="تحته خط">U</button>
                </div>
                
                <div class="v-divider"></div>
                
                <!-- Alignment -->
                <div class="flex items-center gap-0.5">
                    <button class="icon-btn" @click="executeCommand('setTextAlign', 'right')" :class="{'active': store.isActive({textAlign: 'right'})}" title="محاذاة يمين">➡️</button>
                    <button class="icon-btn" @click="executeCommand('setTextAlign', 'center')" :class="{'active': store.isActive({textAlign: 'center'})}" title="توسيط">↔️</button>
                    <button class="icon-btn" @click="executeCommand('setTextAlign', 'left')" :class="{'active': store.isActive({textAlign: 'left'})}" title="محاذاة يسار">⬅️</button>
                </div>
    
                <div class="v-divider"></div>
    
                <!-- Navigation Cluster (Center - Deep Inward Shift) -->
                <div class="flex items-center bg-gray-50/80 rounded-full px-2 py-1 gap-1 border border-gray-100 flex-shrink-0 mx-auto transform translate-x-8 shadow-inner">
                    <!-- Previous Button -->
                    <button 
                        class="icon-btn !w-6 !h-6 !bg-white hover:!bg-blue-50 border border-gray-200 !rounded-full shadow-sm text-[10px]" 
                        @click="executeCommand('prev')"
                        title="السابق"
                        :disabled="!store.navigation.prev"
                        :class="{'opacity-30 cursor-not-allowed': !store.navigation.prev}"
                    >
                        🔼
                    </button>
    
                    <div class="relative group">
                        <button class="flex items-center gap-2 px-4 py-1 text-[11px] font-bold text-gray-700 hover:text-blue-700 hover:bg-white rounded-full transition-all shadow-sm bg-white border border-gray-200 min-w-[140px] justify-between">
                            <span class="truncate max-w-[100px]">{{ store.currentContentNode?.title || 'جاري التحميل...' }}</span>
                            <span class="text-[9px] opacity-40">▾</span>
                        </button>
                        
                        <!-- Dropdown Hierarchy (Glassmorphism) -->
                        <div class="mega-menu w-72 hidden group-hover:block list-none p-3 translate-y-1 shadow-2xl border-blue-50/30 overflow-hidden rounded-xl animate-in fade-in slide-in-from-top-2 duration-200">
                           <div class="menu-grid">
                                <div class="flex items-center justify-between px-2 mb-3">
                                    <p class="text-[10px] text-blue-600 font-bold tracking-tight">هيكلية الكيان</p>
                                    <span class="text-[8px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full uppercase">{{ store.editorMode }}</span>
                                </div>
                                <div class="overflow-y-auto max-h-80 pr-1 space-y-1 custom-scrollbar">
                                    <div v-for="item in store.hierarchy" :key="item._id" 
                                         class="flex items-center gap-3 p-2 hover:bg-blue-50/80 rounded-lg cursor-pointer transition-all group/item border border-transparent hover:border-blue-100"
                                         @click="emit('command', { command: 'goto', value: item })"
                                    >
                                        <div class="w-8 h-8 flex items-center justify-center bg-gray-50 rounded-md group-hover/item:bg-white shadow-sm transition-colors text-[14px]">
                                            {{ item.type === 'chapter' ? '📂' : '📑' }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[11px] text-gray-700 leading-tight" :class="{'font-bold text-blue-700': store.currentContentNode?.id === item?._id}">
                                                {{ item?.title }}
                                            </span>
                                            <span class="text-[8px] text-gray-400">الترتيب: {{ item.order }}</span>
                                        </div>
                                        <div v-if="store.currentContentNode?.id === item._id" class="mr-auto w-1.5 h-1.5 bg-blue-500 rounded-full shadow-[0_0_8px_rgba(59,130,246,0.5)]"></div>
                                    </div>
                                </div>
                           </div>
                        </div>
                    </div>
    
                    <!-- Next Button -->
                    <button 
                        class="icon-btn !w-6 !h-6 !bg-white hover:!bg-blue-50 border border-gray-200 !rounded-full shadow-sm text-[10px]" 
                        @click="executeCommand('next')"
                        title="التالي"
                        :disabled="!store.navigation.next"
                        :class="{'opacity-30 cursor-not-allowed': !store.navigation.next}"
                    >
                        🔽
                    </button>
                    
                    <div class="v-divider h-4 mr-1"></div>
                    <div class="flex items-center gap-1 pl-2">
                        <span class="text-[9px] text-gray-400 font-bold px-1 select-none">اذهب</span>
                        <input type="text" placeholder="5" class="w-8 h-6 text-[11px] text-center border-none bg-white/50 rounded-md outline-none focus:ring-1 focus:ring-blue-300 transition-all font-mono" />
                    </div>
                </div>
    
                <div class="v-divider"></div>
    
                <!-- Quick Heritage Tools -->
                <div class="flex items-center gap-0.5 ml-auto">
                    <button class="icon-btn" @click="executeCommand('insertPoetry')" title="إدراج بيت شعر">✒️</button>
                    <button class="icon-btn" @click="executeCommand('insertQuranic')" title="إدراج آية قرآنية">📖</button>
                    <button class="icon-btn" @click="executeCommand('insertFootnote')" title="إدراج حاشية">📌</button>
                </div>

                <!-- Media Specific Quick Controls -->
                <template v-if="['audio', 'video'].includes(store.editorMode)">
                    <div class="v-divider"></div>
                    <div class="flex items-center gap-0.5">
                        <button v-if="store.editorMode === 'audio'" class="icon-btn !w-auto !px-3 gap-2 bg-blue-50 text-blue-600 border border-blue-100 font-bold" @click="executeCommand('addMediaNode')" title="إضافة مقطع جديد">
                            <span>➕</span>
                            <span class="text-[10px]">إضافة مقطع</span>
                        </button>
                        <button v-if="store.editorMode === 'video'" class="icon-btn !w-auto !px-3 gap-2 bg-indigo-50 text-indigo-600 border border-indigo-100 font-bold" @click="executeCommand('addMediaNode')" title="إضافة مشهد جديد">
                            <span>➕</span>
                            <span class="text-[10px]">إضافة مشهد</span>
                        </button>
                        <div class="v-divider"></div>
                        <button class="icon-btn text-red-600" title="تشغيل/إيقاف">⏯️</button>
                        <button class="icon-btn text-red-600" title="ختم زمني">⏳</button>
                        <button class="icon-btn text-red-600" title="تمييز متحدث">🗣️</button>
                    </div>
                </template>
            </div>
        </div>
    </header>
</template>

<style scoped>
.glass-editor-header {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(12px);
    z-index: 50;
    width: 100%;
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
