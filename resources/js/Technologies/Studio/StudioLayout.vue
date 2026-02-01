<script setup>
import { Head, router } from '@inertiajs/vue3'
import SplitPane from './Layouts/SplitPane.vue'
import ReferencePane from './Panes/ReferencePane.vue'
import EditorPane from './Panes/EditorPane.vue'
import { useEditorStore } from '../Store/EditorStore'
import { useMediaStore } from '../Store/MediaStore'
import { Play } from 'lucide-vue-next'
import { onMounted, onUnmounted, computed, watch, ref, provide, nextTick } from 'vue'

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
const mediaStore = useMediaStore()

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
        
        // Seek player if node has start time
        if (props._legacy.contentNode.start_time !== undefined) {
            setTimeout(() => {
                 mediaStore.requestSeek(props._legacy.contentNode.start_time);
            }, 500);
        }
    }
})

// Watch for content node changes (when navigating between segments/pages)
watch(() => props.activeChildId, (newId, oldId) => {
    if (newId !== oldId) {
        if (props.isFullView) {
             store.loadDocument(props.entity, { id: 'full', title: 'كامل المحتوى', content: props.editorContent }, [], {})
        } else if (props._legacy?.contentNode) {
             store.loadDocument(props.entity, props._legacy.contentNode, [], {})
             
             // Seek player if node has start time
             if (props._legacy.contentNode.start_time !== undefined) {
                 mediaStore.requestSeek(props._legacy.contentNode.start_time);
             }
        }
    }
})

// SYNC: Watch for deep changes in contentNode (e.g. Title update from Player)
watch(() => props._legacy?.contentNode, (newNode) => {
    if (newNode && store.currentContentNode?.id === newNode.id) {
        // Update local store if props updated via router.reload()
        if (store.currentContentNode.title !== newNode.title) {
            console.log('[StudioLayout] Syncing title from props:', newNode.title);
            store.currentContentNode.title = newNode.title;
        }
    }
}, { deep: true });

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
    // If we have a child ID active, do nothing (already there), just open dropdown?
    // User wants to see dropdown ON CLICK.
    // If user clicks button, we toggle dropdown.
    toggleDropdown();
}

// Dropdown Logic
const isDropdownOpen = ref(false)
const searchInput = ref(null);

const toggleDropdown = async () => {
    isDropdownOpen.value = !isDropdownOpen.value;
    
    if (isDropdownOpen.value) {
        searchQuery.value = ''; // Reset search
        await nextTick();
        if (searchInput.value) searchInput.value.focus();
    }
}

const availableNodes = computed(() => {
    // 1. Get nodes from Props (Canonical/Permanent)
    let propNodes = []
    if (props.entity.children && props.entity.children.length > 0) {
        propNodes = props.entity.children.map(c => ({
            id: c._id || c.id,
            slug: c.slug,
            title: c.title || `مقطع #${c.order || '?'}`
        }))
    } else if (props._legacy && props._legacy.hierarchy) {
        propNodes = props._legacy.hierarchy.map(c => ({
            id: c._id || c.id,
            slug: c.slug,
            title: c.title || 'بدون عنوان'
        }))
    }

    // 2. Get segments from MediaStore (Ephemeral/Active)
    const storeSegments = mediaStore.segments.map(s => ({
        id: s.id || s.slug,
        slug: s.slug,
        title: s.label || s.title || 'مقطع غير مسمى',
        start: s.start,
        isEphemeral: true
    }));

    // Merge: Store segments take precedence for navigation within current file
    // But we keep prop nodes for reference.
    const unified = [...storeSegments];
    
    // Add prop nodes that aren't already represented by slug in store
    propNodes.forEach(pn => {
        if (!unified.find(u => u.slug === pn.slug)) {
            unified.push(pn);
        }
    });

    if (!searchQuery.value) return unified;

    // Filter by search query
    const lowerQuery = searchQuery.value.toLowerCase();
    return unified.filter(node => 
        (node.title && node.title.toLowerCase().includes(lowerQuery))
    );
})

const searchQuery = ref('');

const navigateToChild = (id) => {
    // Determine if id is a slug or internal ID
    // Try to find the node in availableNodes
    const node = availableNodes.value.find(n => n.id === id || n.slug === id);
    if (node) {
        navigateToNode(node);
    } else {
        console.warn('[StudioLayout] Navigate request failed, node not found:', id);
    }
}

const navigateToNode = (node) => {
    isDropdownOpen.value = false
    
    // Always navigate to the segment in the editor
    // The editor will load the segment content and the player will sync automatically
    router.visit(route('studio.show', { type: props.type, slug: props.entity.slug, childId: node.id }))
}

const specificNodeTitle = computed(() => {
    if (props.isFullView) return 'عرض مقطع محدد';
    
    // Check if activeSlug matches any of our unified nodes
    const active = availableNodes.value.find(n => n.slug === mediaStore.activeSegmentSlug || n.id === props.activeChildId);
    return active?.title || props._legacy?.contentNode?.title || 'المقطع الحالي';
})
</script>

<template>
  <Head :title="title" />
  
  <div class="h-screen w-screen overflow-hidden bg-gray-950 flex flex-col font-ui text-right" dir="rtl">
    <!-- 
        Global Studio Header 
        (Top Bar: Navigation, Save, User, etc.)
     -->
    <header class="h-12 bg-[#1e1e1e] border-b border-gray-800 flex items-center justify-between px-4 shrink-0 z-[90]">
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

            <!-- Specific View Group (Button + Dropdown) -->
            <div class="relative flex items-center">
                <button 
                    @click="toggleDropdown"
                    :class="[
                        'flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded-r transition-all',
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-500" :class="{'rotate-180': isDropdownOpen}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div 
                    v-if="isDropdownOpen"
                    class="absolute top-full right-0 mt-2 w-64 bg-[#1e1e1e] border border-gray-700 rounded-md shadow-xl overflow-hidden z-[60] flex flex-col max-h-[80vh]"
                >
                    <!-- Header / Search -->
                    <div class="px-2 py-2 bg-gray-800 border-b border-gray-700">
                        <input 
                            v-model="searchQuery"
                            ref="searchInput"
                            type="text" 
                            placeholder="ابحث عن مقطع..." 
                            class="w-full bg-gray-900 border border-gray-600 rounded px-2 py-1 text-[11px] text-gray-200 placeholder-gray-500 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all"
                            @click.stop
                        />
                    </div>

                    <!-- Scrollable List -->
                    <div class="overflow-y-auto flex-1 p-1 custom-scrollbar bg-[#1a1a1a]/50 backdrop-blur-xl">
                        <button 
                            v-for="node in availableNodes" 
                            :key="node.slug || node.id"
                            @click="navigateToNode(node)"
                            class="w-full text-right px-3 py-2.5 text-xs rounded-lg hover:bg-white/5 flex items-center justify-between gap-3 transition-all group/item border border-transparent"
                            :class="(node.slug === mediaStore.activeSegmentSlug || node.id === props.activeChildId) ? 'bg-amber-500/10 text-amber-500 border-amber-500/20 font-bold' : 'text-gray-300 hover:border-white/5'"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <span v-if="node.start !== undefined" class="font-mono text-[9px] px-1 rounded bg-white/5 text-gray-500 group-hover/item:text-amber-500/70 transition-colors">
                                    {{ mediaStore.formatTime(node.start) }}
                                </span>
                                <span class="truncate">{{ node.title }}</span>
                            </div>
                            
                            <span v-if="node.slug === mediaStore.activeSegmentSlug || node.id === props.activeChildId" class="text-amber-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                        </button>
                        
                        <div v-if="availableNodes.length === 0" class="text-center py-6 text-gray-600 text-xs italic">
                            لا توجد مقاطع متاحة
                        </div>
                    </div>
                </div>
            
                <!-- Backdrop to close -->
                <div v-if="isDropdownOpen" @click="isDropdownOpen = false" class="fixed inset-0 z-[55] cursor-default"></div>
            </div>
        </div>

        <!-- Save Status -->
        <span class="text-[10px] flex items-center gap-1.5 border-r border-gray-800 pr-3 mr-1" :class="saveStatusColor">
            <div class="w-1 h-1 rounded-full bg-current" :class="store.isSaving ? 'animate-pulse' : ''"></div>
            {{ saveStatusText }}
        </span>

        <!-- Open Player Button (Touch #2) -->
        <button 
          v-if="props.type !== 'manuscript' && !mediaStore.isOpen"
          @click="mediaStore.setOpen(true)"
          class="flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-all shadow-lg shadow-blue-900/20 active:scale-95"
          title="فتح المشغل"
        >
          <Play class="w-4 h-4 fill-current" />
        </button>
        
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
            @navigate="navigateToChild"
          />
      </div>

    </div>
  </div>
</template>
