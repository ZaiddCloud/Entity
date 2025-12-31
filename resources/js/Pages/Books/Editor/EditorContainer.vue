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
                    <div ref="editorRef" class="prose prose-slate dark:prose-invert max-w-none prose-lg">
                        <!-- TipTap Editor Instance will be here -->
                        <editor-content :editor="editor" />
                    </div>
                </div>
            </div>

            <!-- Split Preview Area -->
            <div v-if="mode === 'split'" class="preview-area w-1/2 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-y-auto p-12 rtl">
                <div class="prose prose-slate dark:prose-invert max-w-none prose-lg reader-content">
                    <!-- Static preview of the content -->
                    <div v-html="previewHtml"></div>
                </div>
            </div>
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
            <SmartNavigator />
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
import { 
    ArrowLeftIcon, 
    MonitorIcon, 
    MaximizeIcon, 
    ColumnsIcon,
    BoldIcon,
    ItalicIcon,
    LinkIcon
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

const handleSave = async () => {
    isSaving.value = true
    const json = editor.value.getJSON()
    // In TipTap format, we might need to convert this back or save as is
    // For now, let's assume we save the content_blocks as the editor's JSON output
    emit('save', json.content)
    setTimeout(() => { isSaving.value = false }, 1000)
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
    })
})

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
</style>
