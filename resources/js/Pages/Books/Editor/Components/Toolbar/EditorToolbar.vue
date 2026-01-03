<script setup>
import { ref } from 'vue'
import { useEditorStore } from '../../Store/editorStore'
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
</script>

<template>
    <header id="main-toolbar" class="glass-editor-header flex flex-col border-b border-gray-200">
        <!-- Row 1: Menu Bar (TinyMCE Style) -->
        <div class="menu-bar flex items-center px-3 h-8 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-1">
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

                <!-- 3. هيكلية (Structure) - PERMANENT -->
                <div class="menu-item">
                    هيكلية
                    <div class="mega-menu">
                        <div class="menu-grid">
                            <button class="menu-btn" @click="executeCommand('setParagraph')">فقرة عادية</button>
                            <div class="divider"></div>
                            <button class="menu-btn font-bold text-blue-800">📚 كتاب فرعي</button>
                            <button class="menu-btn font-bold text-blue-700">📑 جزء</button>
                            <button class="menu-btn font-bold text-blue-600">🚪 باب</button>
                            <button class="menu-btn text-blue-500">📂 فصل</button>
                            <button class="menu-btn italic">💡 مسألة</button>
                            <div class="divider"></div>
                            <button class="menu-btn text-[11px]">🗺️ عرض الشجرة</button>
                            <button class="menu-btn text-blue-600 font-bold">📍 القسم الحالي</button>
                        </div>
                    </div>
                </div>

                <!-- 4. تراث (Heritage) - PERMANENT -->
                <div class="menu-item text-amber-800">
                    تراث
                    <div class="mega-menu w-56">
                        <div class="menu-grid">
                            <p class="text-[9px] text-amber-600 px-2 font-bold mb-1">أدوات تراثية</p>
                            <button class="menu-btn" @click="executeCommand('insertPoetry')">✒️ بيت شعر (صدر/عجز)</button>
                            <button class="menu-btn" @click="executeCommand('insertQuranic')">📖 آية (رسم عثماني)</button>
                            <button class="menu-btn">🏛️ سند/متن</button>
                        </div>
                    </div>
                </div>

                <!-- 4. الوسائط (Media Settings) - Contextual -->
                <div class="menu-item text-red-600" v-if="['audio', 'video'].includes(store.editorMode)">
                    أدوات الوسائط
                    <div class="mega-menu">
                        <div class="menu-grid">
                            <button class="menu-btn">⏯️ تشغيل/إيقاف</button>
                            <button class="menu-btn">⏪ تراجع 5ث</button>
                            <button class="menu-btn">⏩ تقدم 5ث</button>
                            <div class="divider"></div>
                            <button class="menu-btn font-bold">⏳ إدراج ختم زمني</button>
                            <button class="menu-btn">🗣️ تمييز متحدث</button>
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
                            <button class="menu-btn line-through" @click="executeCommand('strike')">S يتوسطه خط</button>
                            <div class="divider"></div>
                            <button class="menu-btn text-red-500" @click="executeCommand('unsetAllMarks')">✨ مسح التنسيق</button>
                        </div>
                    </div>
                </div>

                <!-- 6. إدراج (Insert) - REFACTORED -->
                <div class="menu-item">
                    إدراج
                    <div class="mega-menu w-52">
                        <div class="menu-grid">
                            <button class="menu-btn" @click="executeCommand('insertImage')">🖼️ صورة</button>
                            <button class="menu-btn" @click="executeCommand('insertAudio')">🎧 صوت</button>
                            <button class="menu-btn" @click="executeCommand('insertVideo')">🎬 فيديو</button>
                            <div class="divider"></div>
                            <button class="menu-btn">📊 جدول</button>
                            <button class="menu-btn" @click="createLink">🔗 رابط تشعبي</button>
                            <button class="menu-btn font-bold">🏷️ تاق (Tag)</button>
                            <div class="divider"></div>
                            <p class="text-[9px] text-blue-600 px-2 font-bold mb-1">بحث وتحقيق</p>
                            <button class="menu-btn" @click="executeCommand('insertFootnote')">📌 إدراج حاشية</button>
                            <button class="menu-btn">💬 تعليق (Comment)</button>
                            <button class="menu-btn">⏳ ختم زمن (Timestamp)</button>
                            <button class="menu-btn text-[10px]">📑 إدارة الحواشي</button>
                        </div>
                    </div>
                </div>

                <!-- 7. أدوات (Tools) -->
                <div class="menu-item text-blue-700">
                    أدوات
                    <div class="mega-menu w-64 p-3 font-arabic">
                        <div class="flex flex-col gap-2">
                            <p class="text-[10px] text-gray-400 font-bold mb-1">البحث والاستبدال</p>
                            <input type="text" placeholder="بحث..." class="search-input" />
                            <input type="text" placeholder="استبدال..." class="search-input" />
                            <button class="execute-btn mt-1">تنفيذ</button>
                            <div class="divider"></div>
                            <button class="menu-btn">🔍 تدقيق إملائي</button>
                            <button class="menu-btn" @click="executeCommand('help')">❓ مساعدة</button>
                            <div class="divider"></div>
                            <button class="menu-btn font-bold">📕 تصدير PDF</button>
                            <button class="menu-btn font-bold">📘 تصدير Word</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filename Badge (Modern & Clean) -->
            <div class="mr-auto flex items-center gap-2">
                <span class="text-[9px] text-gray-400 uppercase tracking-widest font-mono">{{ store.editorMode }}</span>
                <div class="filename-badge shadow-sm">
                    {{ store.documentTitle }}
                </div>
            </div>
        </div>

        <!-- Row 2: Icon Toolbar (Frequent Actions) -->
        <div class="icon-toolbar flex items-center px-2 h-10 bg-white gap-0.5 overflow-x-auto no-scrollbar">
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
                <button class="icon-btn line-through" @click="executeCommand('strike')" :class="{'active': store.isActive('strike')}" title="يتوسطه خط">S</button>
            </div>
            
            <div class="v-divider"></div>
            
            <!-- Alignment -->
            <div class="flex items-center gap-0.5">
                <button class="icon-btn" @click="executeCommand('setTextAlign', 'right')" :class="{'active': store.isActive({textAlign: 'right'})}" title="محاذاة يمين">➡️</button>
                <button class="icon-btn" @click="executeCommand('setTextAlign', 'center')" :class="{'active': store.isActive({textAlign: 'center'})}" title="توسيط">↔️</button>
                <button class="icon-btn" @click="executeCommand('setTextAlign', 'left')" :class="{'active': store.isActive({textAlign: 'left'})}" title="محاذاة يسار">⬅️</button>
                <button class="icon-btn" @click="executeCommand('setTextAlign', 'justify')" :class="{'active': store.isActive({textAlign: 'justify'})}" title="ضبط">≡</button>
            </div>

            <div class="v-divider"></div>

            <!-- Navigation Cluster (Center) -->
            <div class="flex items-center bg-gray-50/80 rounded-md px-1 py-0.5 gap-1 border border-gray-100 flex-shrink-0">
                <div class="relative group">
                    <button class="flex items-center gap-1 px-2 py-1 text-[11px] font-bold text-blue-700 hover:bg-white rounded transition-colors shadow-sm bg-white/50 border border-gray-100">
                        انتقل ▾
                    </button>
                    <!-- Dropdown for structure -->
                    <div class="mega-menu w-40 hidden group-hover:block list-none">
                       <div class="menu-grid">
                            <button class="menu-btn">📚 الكتاب</button>
                            <button class="menu-btn">🚪 الباب</button>
                            <button class="menu-btn">📂 الفصل</button>
                            <button class="menu-btn italic">💡 المسألة</button>
                       </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-1 border-r border-gray-200 pr-1 mr-1">
                    <span class="text-[10px] text-gray-400 font-bold px-1 select-none">الرقم</span>
                    <input type="text" placeholder="5" class="w-10 h-6 text-[11px] text-center border border-gray-200 rounded outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-100 transition-all font-mono" />
                </div>
            </div>

            <div class="v-divider"></div>

            <!-- Heritage & Research Shortcuts -->
            <template v-if="['book', 'manuscript'].includes(store.editorMode)">
                <div class="flex items-center gap-0.5">
                    <button class="icon-btn" @click="executeCommand('insertPoetry')" title="إدراج بيت شعر">✒️</button>
                    <button class="icon-btn" @click="executeCommand('insertQuranic')" title="إدراج آية قرآنية">📖</button>
                    <button class="icon-btn" @click="executeCommand('insertFootnote')" title="إدراج حاشية">📌</button>
                    <button class="icon-btn" title="سند/متن">🏛️</button>
                </div>
                <div class="v-divider"></div>
            </template>

            <!-- Media Insertion & Misc -->
            <div class="flex items-center gap-0.5">
                <button class="icon-btn" @click="executeCommand('insertImage')" title="إدراج صورة">🖼️</button>
                <button class="icon-btn" @click="createLink" title="إدراج رابط">🔗</button>
                <button class="icon-btn" title="إدراج جدول">📊</button>
                <button class="icon-btn text-red-500" @click="executeCommand('unsetAllMarks')" title="مسح التنسيقات">✨</button>
            </div>

            <!-- Media Specific Quick Controls -->
            <template v-if="['audio', 'video'].includes(store.editorMode)">
                <div class="v-divider"></div>
                <div class="flex items-center gap-0.5">
                    <button class="icon-btn text-red-600" title="تشغيل/إيقاف">⏯️</button>
                    <button class="icon-btn text-red-600" title="ختم زمني">⏳</button>
                    <button class="icon-btn text-red-600" title="تمييز متحدث">🗣️</button>
                </div>
            </template>
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

.divider {
    height: 1px;
    background: #f3f4f6;
    margin: 4px 0;
}

.kb {
    color: #9ca3af;
    font-size: 9px;
    margin-right: auto;
    padding-right: 12px;
}

/* Hide scrollbar for icon toolbar */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
