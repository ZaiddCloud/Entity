<script setup>
import { Head } from '@inertiajs/vue3'
import SplitPane from './Layouts/SplitPane.vue'
import ReferencePane from './Panes/ReferencePane.vue'
import EditorPane from './Panes/EditorPane.vue'
import { useEditorStore } from '../Store/EditorStore'
import { onMounted, onUnmounted, computed, watch, ref, provide } from 'vue'

const props = defineProps({
    type: { type: String, required: true }, // 'manuscript' | 'audio' | 'video'
    entity: { type: Object, required: true },
    editorContent: { type: String, default: '' },
    title: { type: String, default: 'Entity Studio' },
    _legacy: { type: Object, default: () => ({}) }
})

const store = useEditorStore()

const isPlayerDocked = ref(true) // Default integrated (side-by-side) to avoid covering text

const toggleDock = () => {
    isPlayerDocked.value = !isPlayerDocked.value
}

provide('toggleDock', toggleDock)
provide('isPlayerDocked', isPlayerDocked)

const showSplitLayout = computed(() => {
   return props.type === 'manuscript' || isPlayerDocked.value
})

onMounted(() => {
    store.setEditorMode(props.type)
    if (props._legacy?.resource_data) {
        store.setResourceData(props._legacy.resource_data)
    } else if (props._legacy?.entity) {
        store.setResourceData(props._legacy.entity)
    }
    
    // Load document state
    if (props._legacy?.contentNode) {
        store.loadDocument(props.entity, props._legacy.contentNode, [], {})
    }
})

// Watch for content node changes (when navigating between segments/pages)
// Watch the slug specifically for better reactivity
watch(() => props._legacy?.contentNode?.slug, (newSlug, oldSlug) => {
    if (newSlug && newSlug !== oldSlug && props._legacy?.contentNode) {
        store.loadDocument(props.entity, props._legacy.contentNode, [], {})
    }
})

// Auto-save is now handled by the Page/Composable, not the layout
const saveStatusColor = computed(() => {
    if (store.isSaving) return 'text-yellow-500'
    return 'text-green-500'
})

const saveStatusText = computed(() => {
    if (store.isSaving) return 'جاري الحفظ...'
    return 'محفوظ'
})

const fetchFullTranscript = () => {
    console.log('StudioLayout entity:', props.entity)
    const children = props.entity.children || []

    const typeLabel = props.type === 'manuscript' ? 'كامل صفحات المخطوط' : 'كامل التفريغ النصي للملف';
    // if (!confirm(`سيتم استبدال المحتوى الحالي بـ ${typeLabel}. هل أنت متأكد؟`)) {
    //    return
    // }

    // Sort nodes by order
    const sortedNodes = Array.from(children).sort((a, b) => (a.order || 0) - (b.order || 0))

    let fullTranscript = ''
    let lastHeader = null

    sortedNodes.forEach((child) => {
        let currentHeader = null;
        let content = child.content || '';

        if (props.type === 'audio' || props.type === 'video') {
            currentHeader = child.metadata?.speaker || child.title || (props.type === 'audio' ? 'متحدث' : 'مشهد');
            
            // Clean up: avoid "Speaker: Speaker: text" redundancy
            const headerPattern = new RegExp(`^<p><strong>${currentHeader}</strong></p>|^<strong>${currentHeader}</strong>\n?`, 'i');
            content = content.replace(headerPattern, '').trim();
        } else if (props.type === 'manuscript') {
            // Use folio number as header for manuscripts
            currentHeader = child.title || `الصفحة ${child.order || '?'}`;
        }

        // Add header if it changed or if it's a manuscript (always header per page)
        if (currentHeader !== lastHeader || props.type === 'manuscript') {
            // Add vertical space before new header (except first one)
            if (lastHeader !== null) fullTranscript += '<p><br/></p>'
            
            fullTranscript += `<p><strong>${currentHeader}:</strong></p>`
            lastHeader = currentHeader
        }

        // Add description for video if content is empty
        if (props.type === 'video' && !content && child.description) {
            content = `<p>${child.description}</p>`;
        }

        fullTranscript += content
    })

    console.log('Final Full Transcript Length:', fullTranscript.length)
    store.updateContent(fullTranscript)
}
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
        <!-- Full Transcript Button (Supported for all types now) -->
        <button 
            v-if="['audio', 'video', 'manuscript'].includes(props.type)"
            class="hidden md:flex items-center gap-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 text-[11px] px-2.5 py-1.5 rounded border border-gray-700 transition-all"
            title="جلب كافة مقاطع التفريغ إلى المحرر"
            @click="fetchFullTranscript"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            جلب التفريغ الكامل
        </button>

        <!-- Save Status -->
        <span class="text-[10px] flex items-center gap-1.5 border-r border-gray-800 pr-3 mr-1" :class="saveStatusColor">
            <div class="w-1 h-1 rounded-full bg-current" :class="store.isSaving ? 'animate-pulse' : ''"></div>
            {{ saveStatusText }}
        </span>
        
        <button 
            class="bg-blue-600 hover:bg-blue-500 text-white text-xs px-4 py-1.5 rounded font-bold transition-colors shadow-lg shadow-blue-900/20"
            @click="store.save"
        >
            حفظ
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
    <!-- 
        Main Workspace 
        - Manuscript: Split Pane (Editor + Reference)
        - Media (Audio/Video): Full Width Editor + Floating Player (Hidden in DOM)
    -->
    <div class="flex-1 overflow-hidden relative">
      
      <!-- Scenario A: Manuscript (Split View) -->
      <SplitPane v-if="props.type === 'manuscript'" :initial-split="40" :min-size="20">
        <template #pane-1>
          <EditorPane :initial-content="props.editorContent" />
        </template>
        <template #pane-2>
          <ReferencePane
            :type="props.type"
            :entity="props.entity" 
            :active-slug="props._legacy?.contentNode?.slug"
            @navigate="(slug) => $inertia.visit(route('studio.show', { type: props.type, slug: slug }))"
          />
        </template>
      </SplitPane>

      <!-- Scenario B: Media (Integrated Player inside Editor) -->
      <div v-else class="w-full h-full relative">
          <EditorPane 
            :initial-content="props.editorContent" 
            :media-entity="props.entity"
            :type="props.type"
          />
      </div>

    </div>
  </div>
</template>
