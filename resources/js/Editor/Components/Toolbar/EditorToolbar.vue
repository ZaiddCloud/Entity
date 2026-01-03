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
    <header id="main-toolbar" class="glass-toolbar z-50">
        <!-- اسم الملف (ملتصق باليمين) -->
        <span
            class="font-bold text-gray-800 text-[10px] px-3 bg-gray-50 py-0.5 rounded border border-gray-100"
            >{{ store.documentTitle }}</span
        >
        <!-- الأقسام الخمسة (منزاحة للداخل) -->
        <div class="flex-1 flex items-center px-40 gap-0">
            <!-- 1. التنظيم (Organization) -->
            <div class="flex-1 flex items-center gap-2 px-2">
                <div class="ribbon-item">
                    هيكلية
                    <div class="mega-menu">
                        <div class="menu-grid">
                            <button class="menu-btn" @click="executeCommand('setParagraph')">فقرة</button>
                            <div class="divider"></div>
                            <button class="menu-btn font-bold">📚 كتاب فرعي</button>
                            <button class="menu-btn font-bold">📑 جزء</button>
                            <button class="menu-btn font-bold">🚪 باب</button>
                            <button class="menu-btn">📂 فصل</button>
                            <button class="menu-btn italic">💡 مسألة</button>
                            <div class="divider"></div>
                            <button class="menu-btn text-[11px]">🗺️ عرض الشجرة</button>
                            <button class="menu-btn text-blue-600 font-bold">📍 القسم الحالي</button>
                        </div>
                    </div>
                </div>
                <div class="ribbon-item text-amber-700">
                    تراث
                    <div class="mega-menu w-56">
                        <div class="menu-grid">
                            <p class="text-[9px] text-amber-600 px-2">أدوات تراثية</p>
                            <button class="menu-btn" @click="executeCommand('insertPoetry')">✒️ بيت شعر (صدر/عجز)</button>
                            <button class="menu-btn" @click="executeCommand('insertQuranic')">📖 آية (رسم عثماني)</button>
                            <button class="menu-btn">🏛️ سند/متن</button>
                            <div class="divider"></div>
                            <p class="text-[9px] text-blue-600 px-2">تحقيق علمي</p>
                            <button class="menu-btn">📑 إدارة الحواشي</button>
                            <button class="menu-btn" @click="executeCommand('insertFootnote')">📌 إدراج حاشية</button>
                            <button class="menu-btn">⏳ ختم زمن</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. أدوات عامة (Utilities) -->
            <div
                class="flex-1 flex items-center justify-center gap-1 border-r border-l border-gray-100 px-2"
            >
                <div class="ribbon-item">
                    بحث
                    <div class="mega-menu p-3 w-56">
                        <input
                            type="text"
                            placeholder="بحث..."
                            class="w-full p-2 border rounded text-[10px] mb-1 text-right"
                        />
                        <input
                            type="text"
                            placeholder="استبدال..."
                            class="w-full p-2 border rounded text-[10px] mb-2 text-right"
                        />
                        <button
                            class="w-full bg-blue-600 text-white p-1 rounded text-[10px] font-bold"
                        >
                            تنفيذ
                        </button>
                    </div>
                </div>
                <div
                    class="ribbon-item font-bold cursor-pointer"
                    :class="{ 
                        'text-blue-600': saveState === 'idle',
                        'text-blue-400': saveState === 'saving',
                        'text-green-600': saveState === 'saved'
                    }"
                    @click="handleSave"
                >
                    {{ saveState === 'idle' ? '💾 حفظ' : (saveState === 'saving' ? '⌛ جاري...' : '✅ تم الحفظ') }}
                </div>
                <div class="ribbon-item">
                    تصدير
                    <div class="mega-menu">
                        <div class="menu-grid">
                            <button class="menu-btn">📕 PDF</button>
                            <button class="menu-btn">📘 Word</button>
                            <button class="menu-btn">📒 Markdown</button>
                        </div>
                    </div>
                </div>
                <div class="ribbon-item">
                    إعدادات
                    <div class="mega-menu w-48">
                        <div class="menu-grid">
                            <button class="menu-btn">🔍 تدقيق</button>
                            <button class="menu-btn text-[10px]">👁️ الكود المصدري</button>
                            <button class="menu-btn">❓ مساعدة</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. الأساسيات (Basics) -->
            <div class="flex-1 flex items-center justify-center gap-2 px-2">
                <div class="ribbon-item">
                    ملف
                    <div class="mega-menu">
                        <div class="menu-grid">
                            <button class="menu-btn">✨ جديد</button>
                            <button class="menu-btn">🖨️ طباعة</button>
                            <button class="menu-btn text-[10px]">🕒 تاريخ المراجعات</button>
                        </div>
                    </div>
                </div>
                <div class="ribbon-item">
                    الرئيسية
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
                <div class="ribbon-item">
                    تنسيق
                    <div class="mega-menu">
                        <div class="menu-grid">
                            <button class="menu-btn font-bold" @click="executeCommand('bold')" :class="{'bg-blue-50': store.isActive('bold')}">B عريض</button>
                            <button class="menu-btn italic" @click="executeCommand('italic')" :class="{'bg-blue-50': store.isActive('italic')}">I مائل</button>
                            <button class="menu-btn underline" @click="executeCommand('underline')" :class="{'bg-blue-50': store.isActive('underline')}">U تحت خط</button>
                            <button class="menu-btn line-through" @click="executeCommand('strike')" :class="{'bg-blue-50': store.isActive('strike')}">S يتوسطه خط</button>
                            <div class="divider"></div>
                            <button class="menu-btn text-[10px]" @click="executeCommand('subscript')"><sub>x</sub> منخفض</button>
                            <button class="menu-btn text-[10px]" @click="executeCommand('superscript')"><sup>x</sup> مرتفع</button>
                            <div class="divider"></div>
                            <button class="menu-btn text-red-500" @click="executeCommand('unsetAllMarks')">✨ مسح</button>
                        </div>
                    </div>
                </div>
                <div class="ribbon-item">
                    فقرة
                    <div class="mega-menu">
                        <div class="menu-grid">
                            <button class="menu-btn" @click="executeCommand('setTextAlign', 'right')">➡️ محاذاة يمين</button>
                            <button class="menu-btn" @click="executeCommand('setTextAlign', 'center')">↔️ توسيط</button>
                            <button class="menu-btn" @click="executeCommand('setTextAlign', 'left')">⬅️ محاذاة يسار</button>
                            <button class="menu-btn" @click="executeCommand('setTextAlign', 'justify')">≡ ضبط</button>
                            <div class="divider"></div>
                            <button class="menu-btn">• قائمة نقطية</button>
                            <button class="menu-btn">1. قائمة رقمية</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. المرفقات (Center) -->
            <div
                class="flex-1 flex items-center justify-center gap-1 border-r border-l border-gray-100 px-2"
            >
                <div class="ribbon-item text-blue-600 font-bold">
                    المرفقات
                    <div class="mega-menu">
                        <div class="menu-grid">
                            <button class="menu-btn" @click="executeCommand('insertImage')">🖼️ صورة</button>
                            <button class="menu-btn" @click="executeCommand('insertAudio')">🎧 صوت</button>
                            <button class="menu-btn" @click="executeCommand('insertVideo')">🎬 فيديو</button>
                            <button class="menu-btn">📕 PDF</button>
                            <div class="divider"></div>
                            <button class="menu-btn text-[10px]" @click="createLink">🔗 رابط تشعبي</button>
                            <button class="menu-btn text-[10px]">📊 جدول</button>
                            <button class="menu-btn text-[10px]">🔣 رموز وشارات</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 5. نوع المحرر (Mode) -->
            <div class="flex-1 flex items-center justify-end gap-1 px-2">
                <div class="ribbon-item text-purple-700 font-bold">
                    نوع المحرر
                    <div class="mega-menu">
                        <div class="menu-grid">
                            <button class="menu-btn" :class="{'bg-purple-50': store.editorMode === 'book'}" @click="executeCommand('setMode', 'book')">📖 كتاب</button>
                            <button class="menu-btn" :class="{'bg-purple-50': store.editorMode === 'audio'}" @click="executeCommand('setMode', 'audio')">🎤 صوت</button>
                            <button class="menu-btn" :class="{'bg-purple-50': store.editorMode === 'manuscript'}" @click="executeCommand('setMode', 'manuscript')">📜 مخطوط</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- زر التثبيت وأزرار النافذة (ملتصقة باليسار) -->
        <div class="flex items-center gap-1 px-3">
            <div class="divider-v h-4 w-px bg-gray-200"></div>
            <!-- زر التثبيت -->
            <button
                class="w-6 h-6 flex items-center justify-center hover:bg-gray-100 rounded text-gray-400"
                :class="{ 'pin-active': store.isToolbarPinned }"
                @click="store.togglePin"
            >
                <svg
                    class="w-3 h-3"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2V5zM7 9v11a2 2 0 002 2h6a2 2 0 002-2V9H7z"
                    ></path>
                </svg>
            </button>
            <div class="divider-v h-4 w-px bg-gray-200 mx-1"></div>
            <!-- أزرار التحكم بالنافذة -->
            <button
                class="w-6 h-6 flex items-center justify-center hover:bg-gray-100 rounded text-gray-500 transition-colors"
                @click="executeCommand('min')"
            >
                <svg
                    class="w-3 h-3"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 12H4"
                    ></path>
                </svg>
            </button>
            <button
                class="w-6 h-6 flex items-center justify-center hover:bg-gray-100 rounded text-gray-500 transition-colors"
                @click="executeCommand('max')"
            >
                <svg
                    class="w-3 h-3"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"
                    ></path>
                </svg>
            </button>
            <button
                class="w-6 h-6 flex items-center justify-center hover:bg-red-500 hover:text-white rounded text-gray-500 transition-colors"
                @click="executeCommand('close')"
            >
                <svg
                    class="w-3 h-3"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    ></path>
                </svg>
            </button>
            <!-- نهاية أزرار النافذة -->
        </div>
    </header>
</template>

<style scoped>
.glass-toolbar {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid #e5e7eb;
    height: 42px;
    display: flex; /* Ensure flex display is explicitly set */
    align-items: center;
    padding: 0 0.75rem;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 100;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s;
}

.ribbon-item {
    position: relative;
    cursor: pointer;
    padding: 0.2rem 0.6rem;
    border-radius: 0.3rem;
    transition: all 0.2s;
    font-weight: 500;
    color: #4b5563;
    font-size: 13px;
    display: inline-block; /* Helps with layout */
}
.ribbon-item:hover {
    background-color: #f3f4f6;
    color: #000;
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
    min-width: 200px;
    padding: 0.5rem;
    margin-top: 4px;
}
.ribbon-item:hover .mega-menu {
    display: block;
}
.pin-active {
    color: #2563eb !important;
    background-color: #eff6ff !important;
}
.kb {
    color: #999;
    font-size: 9px;
    margin-right: auto;
    padding-right: 15px;
}
.menu-grid {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 4px;
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
.divider {
    height: 1px;
    background: #eee;
    margin: 4px 0;
}
</style>
