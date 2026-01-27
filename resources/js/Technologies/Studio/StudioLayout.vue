<script setup>
import { Head, router } from '@inertiajs/vue3'
import SplitPane from './Layouts/SplitPane.vue'
import ReferencePane from './Panes/ReferencePane.vue'
import EditorPane from './Panes/EditorPane.vue'
import { useEditorStore } from '../Store/EditorStore'
import { onMounted, onUnmounted, computed, watch, ref, provide } from 'vue'

const props = defineProps({
    type: { type: String, required: true }, // 'manuscript' | 'audio' | 'video'
    entity: { type: Object, required: true },
    editorContent: { type: String, default: '' },
    isFullView: { type: Boolean, default: false },
    activeChildId: { type: String, default: null },
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
    if (props.isFullView) {
        // Pseudo-node for full view to satisfy store if needed
        store.loadDocument(props.entity, { id: 'full', title: 'كامل المحتوى', content: props.editorContent }, [], {})
    } else if (props._legacy?.contentNode) {
        store.loadDocument(props.entity, props._legacy.contentNode, [], {})
    }
})

// Watch for content node changes (when navigating between segments/pages)
watch(() => props.activeChildId, (newId, oldId) => {
    if (newId !== oldId) {
        if (props.isFullView) {
             store.loadDocument(props.entity, { id: 'full', title: 'كامل المحتوى', content: props.editorContent }, [], {})
        } else if (props._legacy?.contentNode) {
             store.loadDocument(props.entity, props._legacy.contentNode, [], {})
        }
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

const navigateToFull = () => {
    router.visit(route('studio.show', { type: props.type, slug: props.entity.slug }))
}

const navigateToSpecific = () => {
    // Determine target ID: use activeChildId (if valid) or fallback to first child
    let targetId = props.activeChildId;
    
    // Check entity.children
    if (!targetId && props.entity.children && props.entity.children.length > 0) {
        targetId = props.entity.children[0]._id || props.entity.children[0].id;
    }
    
    // Fallback to hierarchy from service (more reliable for Mongo hybrid)
    if (!targetId && props._legacy && props._legacy.hierarchy && props._legacy.hierarchy.length > 0) {
        const firstNode = props._legacy.hierarchy[0];
        targetId = firstNode._id || firstNode.id;
    }
    
    if (targetId) {
        router.visit(route('studio.show', { type: props.type, slug: props.entity.slug, childId: targetId }))
    }
}

const specificNodeTitle = computed(() => {
    if (props.isFullView) return 'عرض مقطع محدد';
    return props._legacy?.contentNode?.title || 'المقطع الحالي';
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
        <!-- Dual View Toggle -->
        <div class="hidden md:flex items-center bg-gray-800 rounded-lg p-1 gap-1 border border-gray-700">
            <!-- Full View Button -->
            <button 
                @click="navigateToFull"
                :class="[
                    'flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded transition-all',
                    props.isFullView 
                        ? 'bg-amber-500/10 text-amber-500 font-bold shadow-sm' 
                        : 'text-gray-400 hover:text-white hover:bg-white/5'
                ]"
                title="عرض كافة المحتوى مدمجاً"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                كامل المحتوى
            </button>

            <!-- Divider -->
            <div class="w-px h-3 bg-gray-700"></div>

            <!-- Specific View Button -->
            <button 
                @click="navigateToSpecific"
                :class="[
                    'flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded transition-all',
                    !props.isFullView 
                        ? 'bg-blue-500/10 text-blue-400 font-bold shadow-sm' 
                        : 'text-gray-400 hover:text-white hover:bg-white/5'
                ]"
                :title="!props.isFullView ? 'أنت في وضع التركيز على هذا المقطع' : 'الانتقال للتركيز على مقطع محدد'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                {{ specificNodeTitle }}
            </button>
        </div>

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
            :active-child-id="props.activeChildId"
            @navigate="(id) => router.visit(route('studio.show', { type: props.type, slug: props.entity.slug, childId: id }))"
            @navigate-full="() => router.visit(route('studio.show', { type: props.type, slug: props.entity.slug }))"
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
