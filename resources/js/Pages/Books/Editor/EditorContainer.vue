<template>
    <div class="editor-workspace h-screen flex flex-col overflow-hidden bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
        <!-- Workspace Header / Mode Switcher -->
        <header class="workspace-header glass-effect z-50 flex items-center justify-between px-6 py-2 border-b border-white/20 dark:border-white/10">
            <div class="flex items-center gap-4">
                <button @click="$emit('close')" class="p-2 rounded-full hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                    <ArrowLeftIcon class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                </button>
                <h1 class="text-lg font-bold text-slate-800 dark:text-slate-200 truncate max-w-sm">
                    {{ title }}
                </h1>
                <span class="px-2 py-0.5 rounded text-xs font-medium bg-secondary/10 text-secondary border border-secondary/20 uppercase tracking-wider">
                    {{ typeLabel }}
                </span>
            </div>

            <!-- Triple Mode Switcher -->
            <div class="flex items-center bg-slate-200/50 dark:bg-slate-800/50 p-1 rounded-xl glass-inset">
                <button 
                    v-for="m in modes" 
                    :key="m.id"
                    @click="mode = m.id"
                    :class="[
                        'flex items-center gap-2 px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-300',
                        mode === m.id 
                            ? 'bg-white dark:bg-slate-700 text-primary shadow-sm scale-105' 
                            : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                    ]"
                >
                    <component :is="m.icon" class="w-4 h-4" />
                    {{ m.label }}
                </button>
            </div>

            <div class="flex items-center gap-3">
                <div v-if="isSaving" class="flex items-center gap-2 text-xs text-slate-500 italic">
                    <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                    جاري الحفظ...
                </div>
                <button 
                    @click="handleSave"
                    :disabled="isSaving"
                    class="btn-primary px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-primary/25 disabled:opacity-50"
                >
                    حفظ التعديلات
                </button>

                <div class="h-6 w-px bg-slate-200 dark:bg-slate-800 mx-1"></div>

                <!-- Export Dropdown -->
                <div class="relative group">
                    <button class="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                        <DownloadIcon class="w-4 h-4" />
                        تصدير
                        <ChevronDownIcon class="w-4 h-4" />
                    </button>
                    <div class="absolute left-0 top-full mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-[100] transform origin-top-left scale-95 group-hover:scale-100 p-2 flex flex-col gap-1">
                        <button @click="handleExport('pdf')" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-medium text-slate-700 dark:text-slate-300">
                             <FileTextIcon class="w-4 h-4 text-red-500" /> PDF Document
                        </button>
                        <button @click="handleExport('docx')" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-medium text-slate-700 dark:text-slate-300">
                             <FileTextIcon class="w-4 h-4 text-blue-500" /> Word Document
                        </button>
                        <button @click="handleExport('md')" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-medium text-slate-700 dark:text-slate-300">
                             <FileCodeIcon class="w-4 h-4 text-slate-500" /> Markdown File
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Editor Main Area -->
        <main :class="['flex-1 overflow-hidden transition-all duration-500', layoutClass]">
            <!-- In-place/Full Toolbar -->
            <div v-if="mode !== 'split'" class="px-6 py-2 border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 flex justify-center">
                <Toolbar :editor="editor" />
            </div>

            <!-- Editor Content -->
            <div :class="['editor-scroller flex-1 overflow-y-auto px-4 py-12 scroll-smooth', mode === 'split' ? 'w-1/2' : 'w-full']">
                <div :class="['mx-auto transition-all duration-500', contentMaxWidth]">
                    <div ref="editorRef" class="prose prose-slate dark:prose-invert max-w-none prose-lg relative">
                        <!-- Block Handle -->
                        <BlockHandle 
                            :visible="handleVisible" 
                            :top="handleTop" 
                            :left="handleLeft"
                            @add="handleBlockAdd"
                        />

                        <!-- TipTap Editor Instance -->
                        <editor-content :editor="editor" @mouseover="updateHandlePosition" />
                    </div>
                </div>
            </div>

            <!-- Split Preview Area -->
            <div v-if="mode === 'split'" class="preview-area w-1/2 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-y-auto p-12 rtl">
                <div class="prose prose-slate dark:prose-invert max-w-none prose-lg reader-content">
                    <div v-html="previewHtml"></div>
                </div>
            </div>

            <!-- Annotation Sidebar (Scholarly Panel) -->
            <aside v-if="mode !== 'split'" class="annotation-sidebar w-80 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50 p-6 flex flex-col gap-4 overflow-y-auto overflow-x-hidden">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2">
                    <MessageSquareIcon class="w-4 h-4" />
                    Annotations / Footnotes
                </div>
                
                <div v-if="activeFootnote" class="footnote-editor animate-in slide-in-from-right-4 duration-300">
                    <div class="flex items-center justify-between mb-3">
                         <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-700 text-[10px] font-bold">
                            MARKER: {{ activeFootnote.marker || '*' }}
                         </span>
                         <button @click="removeFootnote" class="text-xs text-red-500 hover:underline">إزالة الهامش</button>
                    </div>
                    <textarea 
                        v-model="activeFootnote.content"
                        @input="updateFootnoteContent"
                        class="w-full h-48 p-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                        placeholder="اكتب الهامش هنا..."
                    ></textarea>
                    <p class="text-[10px] text-slate-400 mt-2 italic">* التعديلات تُحفظ تلقائياً في النص</p>
                </div>
                
                <div v-else class="h-full flex flex-col items-center justify-center text-center opacity-30 select-none py-12">
                    <BookOpenIcon class="w-12 h-12 mb-4 text-slate-300" />
                    <p class="text-sm font-medium">حدد هامشاً في النص لتعديله أو أضف هامشاً جديداً</p>
                </div>
            </aside>
        </main>

        <!-- Floating Bubble Menu (TipTap) Placeholder -->
        <bubble-menu :editor="editor" v-if="editor" class="bubble-menu glass-effect p-1 rounded-xl shadow-2xl border border-white/20 flex gap-1">
             <button @click="editor.chain().focus().toggleBold().run()" :class="{ 'is-active': editor.isActive('bold') }" class="p-2 hover:bg-black/5 dark:hover:bg-white/5 rounded-lg transition-colors">
                <BoldIcon class="w-4 h-4" />
             </button>
             <button @click="editor.chain().focus().toggleItalic().run()" :class="{ 'is-active': editor.isActive('italic') }" class="p-2 hover:bg-black/5 dark:hover:bg-white/5 rounded-lg transition-colors">
                <ItalicIcon class="w-4 h-4" />
             </button>
             <button @click="editor.chain().focus().toggleLink().run()" :class="{ 'is-active': editor.isActive('link') }" class="p-2 hover:bg-black/5 dark:hover:bg-white/5 rounded-lg transition-colors">
                <LinkIcon class="w-4 h-4" />
             </button>
        </bubble-menu>

        <!-- Smart Navigator (Bottom Bar) -->
        <footer class="z-50">
            <SmartNavigator @jump="handleJump" @search="handleSearch" />
        </footer>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Editor, EditorContent, BubbleMenu } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import Link from '@tiptap/extension-link'
import TextAlign from '@tiptap/extension-text-align'
import Subscript from '@tiptap/extension-subscript'
import Superscript from '@tiptap/extension-superscript'
import Toolbar from './Components/Toolbar.vue'
import SmartNavigator from './Components/SmartNavigator.vue'
import BlockHandle from './Components/BlockHandle.vue'
import { ScholarlyFootnote } from './Extensions/ScholarlyFootnote'
import { 
    ArrowLeftIcon, 
    MonitorIcon, 
    MaximizeIcon, 
    ColumnsIcon,
    BoldIcon,
    ItalicIcon,
    LinkIcon,
    MessageSquareIcon,
    BookOpenIcon,
    GripVerticalIcon,
    DownloadIcon,
    ChevronDownIcon,
    FileTextIcon,
    FileCodeIcon
} from 'lucide-vue-next'

const props = defineProps({
    childId: String,
    title: String,
    type: String,
    initialContent: Array
})

const emit = defineEmits(['close', 'save'])

const mode = ref('in-place') // 'in-place', 'full', 'split'
const isSaving = ref(false)

const modes = [
    { id: 'in-place', label: 'تحرير في المكان', icon: MonitorIcon },
    { id: 'full', label: 'المحرر الشامل', icon: MaximizeIcon },
    { id: 'split', label: 'العرض المزدوج', icon: ColumnsIcon },
]

const editor = ref(null)

const layoutClass = computed(() => {
    if (mode.value === 'split') return 'flex flex-row-reverse'
    return 'flex flex-col'
})

const contentMaxWidth = computed(() => {
    if (mode.value === 'full') return 'max-w-4xl'
    if (mode.value === 'split') return 'max-w-3xl'
    return 'max-w-5xl' // In-place matches reader width
})

const typeLabels = {
    'sub-book': 'كتاب فرعي',
    'part': 'جزء',
    'bab': 'باب',
    'chapter': 'فصل',
    'masala': 'مسألة',
}
const typeLabel = computed(() => typeLabels[props.type] || 'وحدة')

const previewHtml = ref('')
const activeFootnote = ref(null)

// Block Handle State
const handleVisible = ref(false)
const handleTop = ref(0)
const handleLeft = ref(0)
const activeNodePos = ref(null)

const handleSave = async () => {
    isSaving.value = true
    const json = editor.value.getJSON()
    emit('save', json.content)
    setTimeout(() => { isSaving.value = false }, 1000)
}

const handleExport = (format) => {
    const url = route('api.book-children.export', { child: props.childId, format: format })
    window.open(url, '_blank')
}

onMounted(() => {
    editor.value = new Editor({
        content: {
            type: 'doc',
            content: props.initialContent || []
        },
        extensions: [
            StarterKit,
            Underline,
            Link.configure({
                openOnClick: false,
            }),
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
            Subscript,
            Superscript,
            ScholarlyFootnote,
        ],
        autofocus: true,
        editable: true,
        injectCSS: false,
        onUpdate({ editor }) {
            previewHtml.value = editor.getHTML()
        },
        onCreate({ editor }) {
            previewHtml.value = editor.getHTML()
        },
        onSelectionUpdate({ editor }) {
            const attrs = editor.getAttributes('scholarlyFootnote')
            if (attrs && attrs.marker !== undefined) {
                activeFootnote.value = { ...attrs }
            } else {
                activeFootnote.value = null
            }
        },
    })
})

const updateFootnoteContent = () => {
    if (activeFootnote.value) {
        editor.value.chain().focus().setFootnote({ 
            content: activeFootnote.value.content,
            marker: activeFootnote.value.marker 
        }).run()
    }
}

const removeFootnote = () => {
    editor.value.chain().focus().unsetFootnote().run()
    activeFootnote.value = null
}

const updateHandlePosition = (event) => {
    if (!editor.value) return
    
    const view = editor.value.view
    const pos = view.posAtCoords({ left: event.clientX, top: event.clientY })
    
    if (pos) {
        const node = view.state.doc.nodeAt(pos.pos) || view.state.doc.resolve(pos.pos).parent
        const nodeDOM = view.nodeDOM(view.state.doc.resolve(pos.pos).before()) || event.target.closest('.ProseMirror > *')
        
        if (nodeDOM) {
            const rect = nodeDOM.getBoundingClientRect()
            const containerRect = editorRef.value.getBoundingClientRect()
            
            handleVisible.value = true
            handleTop.value = rect.top - containerRect.top + 4
            handleLeft.value = rect.right - containerRect.left // Position exactly at the right edge
            activeNodePos.value = pos.pos
        }
    }
}

const handleBlockAdd = () => {
    if (activeNodePos.value !== null) {
        editor.value.chain().focus().insertContentAt(activeNodePos.value, { type: 'paragraph' }).run()
    }
}

const handleJump = (query) => {
    console.log('Jumping to:', query)
    // Here we would find the node with this digit or title and scroll to it
    // For now, let's just log
}

const handleSearch = (query) => {
    console.log('Searching for:', query)
}

onBeforeUnmount(() => {
    editor.value.destroy()
})
</script>

<style scoped>
.glass-effect {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.dark .glass-effect {
    background: rgba(15, 23, 42, 0.7);
}

.workspace-header {
    height: 64px;
}

.glass-inset {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
}

.editor-scroller::-webkit-scrollbar {
    width: 6px;
}
.editor-scroller::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.bubble-menu button.is-active {
    background: rgba(var(--color-primary-rgb), 0.1);
    color: var(--color-primary);
}

:deep(.ProseMirror span[data-footnote]) {
  vertical-align: super;
  font-size: 0.75em;
  font-weight: bold;
  color: #b45309; /* amber-700 */
  background: #fef3c7; /* amber-100 */
  padding: 0 4px;
  border-radius: 4px;
  cursor: pointer;
  margin: 0 2px;
}

:deep(.ProseMirror span[data-footnote]:hover) {
  background: #fde68a; /* amber-200 */
}
</style>
