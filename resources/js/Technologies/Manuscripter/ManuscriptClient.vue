<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    manuscript: Object,
    siblings: {
        type: Array,
        default: () => []
    },
    activeSlug: {
        type: String,
        default: null
    }
});

const emit = defineEmits(['navigate']);

// State
const shotNumber = ref(1);
const viewMode = ref('list'); // 'list' (vertical reading), 'grid', 'default' (slider)
const isCompareMode = ref(false);
const windowWidth = ref(1024);

// Versions Data (Derived from DB)
const versions = computed(() => {
    return [
        { 
            id: props.manuscript.id, 
            name: 'النسخة الحالية', 
            manuscript: props.manuscript,
            pages: props.manuscript.children || []
        },
        ...props.siblings.map(s => ({
            id: s.id,
            name: s.catalog_number || s.title,
            manuscript: s,
            pages: s.children || []
        }))
    ];
});

const selectedVersionIds = ref([props.manuscript.id]); // Default to Original manuscript ID

// Initialize Shot Number based on Active Slug
onMounted(() => {
    if (props.activeSlug && versions.value[0]?.pages) {
        const pageIndex = versions.value[0].pages.findIndex(p => p.slug === props.activeSlug);
        if (pageIndex !== -1) {
            shotNumber.value = pageIndex + 1;
        }
    }
    updateWidth();
    window.addEventListener('resize', updateWidth);
});

// Watch Shot Number to Emit Navigation
watch(shotNumber, (newShot) => {
    // Only emit if valid range and we have pages
    const mainVersion = versions.value[0];
    if (!mainVersion || !mainVersion.pages) return;
    
    const page = mainVersion.pages[newShot - 1];
    
    // Check if we need to navigate (avoid loop if already on slug)
    if (page && page.slug !== props.activeSlug) {
        // Debounce slightly or just emit. Inertia handles visits well.
        emit('navigate', page.slug);
    }
});

// Watch Active Slug to Update Shot Number (if changed externally, e.g. browser back button)
watch(() => props.activeSlug, (newSlug) => {
    if (!newSlug || !versions.value[0]?.pages) return;
    
    const pageIndex = versions.value[0].pages.findIndex(p => p.slug === newSlug);
    if (pageIndex !== -1 && (pageIndex + 1) !== shotNumber.value) {
        shotNumber.value = pageIndex + 1;
    }
});

// Resizing State
const panelWidths = ref([])
const isResizing = ref(false)
const resizeIndex = ref(-1)

// Resize Handlers
const startResizing = (e, index) => {
    isResizing.value = true
    resizeIndex.value = index
    window.addEventListener('mousemove', handleResizeMove)
    window.addEventListener('mouseup', stopResizing)
    document.body.style.cursor = 'col-resize'
    document.body.style.userSelect = 'none'
}

const handleResizeMove = (e) => {
    if (!isResizing.value) return

    const container = document.getElementById('compare-container')
    if (!container) return

    const containerWidth = container.getBoundingClientRect().width
    
    // In RTL, moving Left (negative X) means:
    // The panel to the RIGHT (index) grows.
    // The panel to the LEFT (index+1) shrinks.
    // So: Growth = -movementX
    const deltaPercent = (-e.movementX / containerWidth) * 100
    
    const idx = resizeIndex.value
    const nextIdx = idx + 1
    
    if (nextIdx >= panelWidths.value.length) return
    
    const newCurrent = panelWidths.value[idx] + deltaPercent
    const newNext = panelWidths.value[nextIdx] - deltaPercent
    
    if (newCurrent < 10 || newNext < 10) return
    
    panelWidths.value[idx] = newCurrent
    panelWidths.value[nextIdx] = newNext
}

const stopResizing = () => {
    isResizing.value = false
    resizeIndex.value = -1
    window.removeEventListener('mousemove', handleResizeMove)
    window.removeEventListener('mouseup', stopResizing)
    document.body.style.cursor = ''
    document.body.style.userSelect = ''
}



// Responsive Filmstrip Logic
const updateWidth = () => {
    if (typeof window !== 'undefined') {
        windowWidth.value = window.innerWidth;
    }
};

const filmstripCount = computed(() => {
    return totalPages.value; 
});

onMounted(() => {
    updateWidth();
    window.addEventListener('resize', updateWidth);
});

onUnmounted(() => {
    window.removeEventListener('resize', updateWidth);
});

// Scroll to specific shot in vertical reading mode
const scrollToShot = (shotIndex) => {
    // Use nextTick to ensure DOM is updated after viewMode change
    setTimeout(() => {
        const element = document.getElementById(`shot-${shotIndex}`);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 100);
};

// Toggle Version Selection (for Compare Mode)
const toggleVersionSelection = (versionId) => {
    if (!isCompareMode.value) {
        // Single Mode: direct switch
        selectedVersionIds.value = [versionId];
    } else {
        // Compare Mode: toggle logic
        const index = selectedVersionIds.value.indexOf(versionId);
        if (index === -1) {
            selectedVersionIds.value.push(versionId);
        } else {
            // Allow deselecting unless it's the last one remaining
            if (selectedVersionIds.value.length > 1) {
                selectedVersionIds.value.splice(index, 1);
            }
        }
    }
};

// Computed: Versions to display
const displayedVersions = computed(() => {
    return versions.value.filter(v => selectedVersionIds.value.includes(v.id));
});

// Logic: Reset widths when displayedVersions changes
watch(displayedVersions, (newVersions) => {
    if (newVersions.length > 0) {
        // Distribute width equally
        const width = 100 / newVersions.length
        panelWidths.value = newVersions.map(() => width)
    }
}, { immediate: true })

// Image URL Generation
const getPageUrl = (shotIndex, version) => {
    if (!version || !version.pages || version.pages.length === 0) return '';
    
    // shotIndex is 1-based, array is 0-based
    const page = version.pages[shotIndex - 1];
    return page ? page.image_url : '';
};

// Computed: Max total pages across all selected versions
const totalPages = computed(() => {
    if (displayedVersions.value.length === 0) return 0;
    return Math.max(...displayedVersions.value.map(v => v.pages.length));
});
</script>

<template>
  <!-- 
        Reusable Container:
        Takes full height/width of PARENT (not screen).
        Parent is responsible for layout positioning.
    -->
  <div
    class="w-full h-full bg-stone-900 relative overflow-hidden font-ui text-gray-800 flex flex-col"
    dir="rtl"
  >
    <!-- 
            =========================================
            1. The Content Layer (Viewport) - BASE
            =========================================
            Occupies the entire screen space behind controls.
        -->
    <main class="flex-1 relative z-0 bg-[#0c0c0c] overflow-hidden">
      <!-- 
                VIEW 1: INDEX VIEW (SHOTS METADATA TABLE)
            -->
      <div
        v-if="viewMode === 'default'"
        class="w-full h-full overflow-y-auto custom-scrollbar"
      >
        <div class="max-w-5xl mx-auto flex flex-col gap-2 py-8 pt-20 pb-20 px-4">
          <div
            v-for="i in totalPages"
            :key="i" 
            class="w-full h-20 flex items-center gap-4 bg-white/5 border border-white/5 rounded-lg p-2 hover:bg-white/10 hover:border-white/10 transition-colors group cursor-pointer"
            @click="shotNumber = i"
            @dblclick="shotNumber = i; viewMode = 'list'; scrollToShot(i);"
          >
            <!-- Thumbnail -->
            <div class="h-full aspect-[2/3] bg-black rounded overflow-hidden relative shadow-sm">
              <img
                :src="getPageUrl(i, displayedVersions[0])"
                loading="lazy"
                class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity"
              >
            </div>

            <!-- Metadata Info -->
            <div class="flex flex-col flex-1 gap-1">
              <div class="flex items-center gap-2">
                <span class="text-emerald-400 font-mono text-sm font-bold">#{{ i }}</span>
                <span class="text-white/60 text-xs">لقطة {{ i }}</span>
              </div>
              <span class="text-xs text-white/40">DSC{{ String(i + 90).padStart(5, '0') }}.JPG</span>
            </div>

            <!-- Actions (Mock) -->
            <div class="flex items-center gap-2 px-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <button
                class="p-2 rounded hover:bg-white/10 text-white/60 hover:text-white"
                title="عرض"
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                /><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                /></svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 
                VIEW 2: GRID VIEW (THUMBNAILS / CONTACT SHEET)
            -->
      <div
        v-else-if="viewMode === 'grid'"
        class="w-full h-full overflow-y-auto custom-scrollbar px-4"
      >
        <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-2 pt-20 pb-20">
          <div
            v-for="i in totalPages"
            :key="i" 
            class="aspect-[2/3] relative group cursor-pointer border border-white/5 rounded overflow-hidden bg-white/5 hover:border-blue-500/50 transition-colors"
            @click="shotNumber = i"
            @dblclick="shotNumber = i; viewMode = 'list'; scrollToShot(i);"
          >
            <img
              :src="getPageUrl(i, displayedVersions[0])"
              loading="lazy"
              class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity"
            >
                        
            <!-- Overlay: Number Only (Compact) -->
            <div class="absolute inset-0 flex items-end justify-start p-1 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
              <span class="text-[10px] font-bold text-white font-mono">#{{ i }}</span>
            </div>

            <!-- Active Indicator (Single Mode) -->
            <div
              v-if="i === shotNumber"
              class="absolute top-1 right-1 w-1.5 h-1.5 rounded-full bg-blue-500 shadow-sm shadow-blue-500/50"
            />
          </div>
        </div>
      </div>

      <!-- 
                VIEW 3: LIST VIEW (VERTICAL SCROLL / READING MODE)
            -->

      <!-- COMPARE MODE: Side-by-side comparison (VERSION COMPARISON) -->
      <!-- 
                NOTE: Overriding standard 'list' behavior when isCompareMode is true.
                We display multiple versions of the SAME shot side-by-side. 
            -->
      <div
        v-if="viewMode === 'list' && isCompareMode"
        id="compare-container"
        class="w-full h-full flex overflow-hidden"
      >
        <template
          v-for="(version, idx) in displayedVersions"
          :key="version.id"
        >
          <div 
            class="flex flex-col items-center justify-center bg-black/40 overflow-hidden relative border-white/5"
            :class="{'border-l': idx > 0}"
            :style="{ width: panelWidths[idx] + '%' }"
          >
            <!-- Image -->
            <div class="w-full h-full flex items-center justify-center p-4">
              <img
                :src="getPageUrl(shotNumber, version)" 
                class="max-h-full max-w-full object-contain shadow-2xl opacity-90 transition-opacity hover:opacity-100"
              >
            </div>
                        
            <!-- Minimal Filename Overlay -->
            <div class="absolute bottom-4 right-4 bg-black/40 backdrop-blur px-2 py-1 rounded text-white/50 text-[10px] font-mono pointer-events-none">
              {{ getPageUrl(shotNumber, version).split('/').pop().split('.')[0] }}
            </div>
          </div>

          <!-- Resize Handle -->
          <div 
            v-if="idx < displayedVersions.length - 1"
            class="w-4 -mr-2 -ml-2 z-50 h-full flex items-center justify-center cursor-col-resize group/handle select-none relative"
            @mousedown.prevent="startResizing($event, idx)"
          >
            <!-- Hit Area & Visual Line -->
            <div class="h-full w-[1px] bg-white/10 group-hover/handle:bg-blue-500/50 transition-colors" />
                        
            <!-- Grabber Icon (Vertical Dots) -->
            <div class="absolute w-4 h-8 bg-black/60 border border-white/20 rounded-full flex flex-col items-center justify-center gap-0.5 opacity-0 group-hover/handle:opacity-100 transition-opacity backdrop-blur hover:bg-black/80 hover:border-blue-500/50">
              <div class="w-0.5 h-0.5 rounded-full bg-white/70" />
              <div class="w-0.5 h-0.5 rounded-full bg-white/70" />
              <div class="w-0.5 h-0.5 rounded-full bg-white/70" />
            </div>
          </div>
        </template>
      </div>

      <!-- NORMAL MODE: Vertical scroll reading -->
      <div
        v-else-if="viewMode === 'list'"
        class="w-full h-full overflow-y-auto custom-scrollbar"
      >
        <div class="flex flex-col items-center gap-6 py-8 px-4 md:px-0 pt-20 pb-20">
          <div
            v-for="i in totalPages"
            :id="`shot-${i}`"
            :key="i"
            class="w-full max-w-3xl relative group shadow-2xl bg-black"
          >
            <img
              :src="getPageUrl(i, displayedVersions[0])"
              loading="lazy"
              class="w-full h-auto object-contain transition-opacity opacity-90 group-hover:opacity-100"
            >
                        
            <!-- Minimal Overlay -->
            <div class="absolute bottom-4 right-4 bg-black/40 backdrop-blur px-2 py-1 rounded text-white/60 text-xs font-mono opacity-0 group-hover:opacity-100 transition-opacity">
              #{{ i }}
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- 
            =========================================
            2. Floating Toolbar (Overlay - HUD Style)
            =========================================
        -->
    <header class="absolute top-0 left-0 right-0 z-10 h-12 px-4 flex items-center justify-between gap-4 transition-all bg-gradient-to-b from-black/60 to-transparent">
      <!-- Right Section: Navigation Tools -->
      <div class="flex items-center gap-4 flex-1 md:flex-initial">
        <!-- Versions / Copies List -->
        <div class="flex items-center gap-1 bg-white/5 rounded-full p-1 border border-white/5 backdrop-blur-sm overflow-hidden">
          <div 
            v-for="version in versions" 
            :key="version.id"
            class="flex items-center rounded-full transition-all"
            :class="selectedVersionIds.includes(version.id) 
              ? 'bg-blue-600 text-white shadow-sm pl-1 pr-3 py-1' 
              : 'px-3 py-1 text-stone-400 hover:text-stone-200 hover:bg-white/5 cursor-pointer'"
            @click="!selectedVersionIds.includes(version.id) ? toggleVersionSelection(version.id) : null"
          >
            <span 
              v-if="selectedVersionIds.includes(version.id)"
              class="mr-2 cursor-pointer opacity-80 hover:opacity-100"
              @click.stop="toggleVersionSelection(version.id)"
            >✕</span>

            <span class="text-[11px] font-medium whitespace-nowrap mr-2">{{ version.name }}</span>

            <input 
              v-if="selectedVersionIds.includes(version.id)"
              v-model="shotNumber" 
              type="number"
              class="w-10 bg-black/20 border-none rounded px-1 py-0.5 text-[10px] font-mono text-white text-center focus:ring-1 focus:ring-white/30 outline-none"
              placeholder="#"
              @click.stop
            >
          </div>
        </div>

        <div class="w-px h-4 bg-white/10 hidden md:block" />

        <!-- Shot Input -->
        <div class="flex items-center gap-2 group cursor-text">
          <span class="text-[12px] text-stone-400 group-hover:text-stone-200 transition-colors hidden sm:inline">لقطة</span>
          <div class="relative">
            <input 
              v-model="shotNumber" 
              type="number"
              :max="totalPages"
              min="1"
              class="w-12 text-[14px] font-bold text-stone-200 bg-transparent outline-none p-0 border-b border-white/20 focus:border-blue-400 transition-colors placeholder-stone-600 text-center"
              placeholder="#"
            >
          </div>
          <span class="text-[12px] text-stone-500">/ {{ totalPages }}</span>
        </div>
      </div>

      <!-- Left Section: View Options -->
      <div class="flex items-center gap-4">
        <!-- View Modes -->
        <div class="flex items-center bg-white/5 rounded-full p-1 border border-white/5 backdrop-blur-sm">
          <button 
            class="w-8 h-8 flex items-center justify-center rounded-full transition-all"
            :class="viewMode === 'list' ? 'bg-indigo-500 text-white shadow-sm' : 'text-stone-400 hover:text-stone-200 hover:bg-white/5'"
            title="القراءة العمودية"
            @click="viewMode = 'list'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect width="20" height="12" x="2" y="6" rx="2"/></svg>
          </button>

          <button 
            class="w-8 h-8 flex items-center justify-center rounded-full transition-all"
            :class="viewMode === 'grid' ? 'bg-indigo-500 text-white shadow-sm' : 'text-stone-400 hover:text-stone-200 hover:bg-white/5'"
            title="عرض الشبكة"
            @click="viewMode = 'grid'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
          </button>

          <button 
            class="w-8 h-8 flex items-center justify-center rounded-full transition-all"
            :class="viewMode === 'default' ? 'bg-indigo-500 text-white shadow-sm' : 'text-stone-400 hover:text-stone-200 hover:bg-white/5'"
            title="الفهرس"
            @click="viewMode = 'default'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>
          </button>
        </div>

        <div class="w-px h-4 bg-white/10" />

        <!-- Compare Switcher -->
        <div class="flex items-center gap-3">
          <button 
            class="text-[13px] transition-colors focus:outline-none"
            :class="!isCompareMode ? 'font-bold text-stone-200' : 'font-medium text-stone-500 hover:text-stone-300'"
            @click="isCompareMode = false; selectedVersionIds = [selectedVersionIds[0] || props.manuscript.id]"
          >
            مفرد
          </button>
          <button 
            class="text-[13px] transition-colors focus:outline-none"
            :class="isCompareMode ? 'font-bold text-indigo-400' : 'font-medium text-stone-500 hover:text-stone-300'"
            @click="isCompareMode = true"
          >
            مقارنة
          </button>
        </div>
      </div>
    </header>

    <!-- 
            =========================================
            3. Floating Footer (Overlay - HUD Style)
            =========================================
        -->
    <footer class="absolute bottom-0 left-0 right-0 z-10 h-12 w-full bg-gradient-to-t from-black/80 to-transparent flex items-center px-4 gap-4 transition-all">
      <!-- Filmstrip -->
      <div
        class="flex-1 flex gap-1.5 md:gap-2 overflow-x-auto items-center custom-scrollbar h-full py-0.5 justify-start md:justify-center"
        dir="ltr"
      >
        <div
          v-for="i in filmstripCount"
          :key="i" 
          class="w-7 md:w-8 h-9 md:h-10 rounded-sm shadow-sm cursor-pointer transition-all hover:-translate-y-0.5 flex flex-shrink-0 items-center justify-center text-[9px] font-bold border border-white/10"
          :class="[
            i === shotNumber 
              ? 'bg-blue-600 text-white ring-1 ring-blue-400 ring-offset-1 ring-offset-black' 
              : 'bg-white/5 text-stone-400 hover:bg-white/10 hover:text-stone-200'
          ]"
          @click="shotNumber = i"
        >
          {{ i }}
        </div>
      </div>

      <!-- Controls & Info Group -->
      <div class="flex items-center gap-3 border-l border-white/10 pl-3 shrink-0 h-8">
        <!-- Zoom Controls -->
        <div class="flex items-center gap-1 bg-white/5 rounded-full px-1.5 py-0.5 border border-white/5 backdrop-blur-sm">
          <button class="w-5 h-5 flex items-center justify-center rounded-full hover:bg-white/10 text-stone-400 hover:text-stone-200 transition-colors" title="تصغير">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M5 12h14" /></svg>
          </button>
          <span class="text-[10px] font-bold text-stone-300 w-8 text-center font-mono">100%</span>
          <button class="w-5 h-5 flex items-center justify-center rounded-full hover:bg-white/10 text-stone-400 hover:text-stone-200 transition-colors" title="تكبير">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M5 12h14" /><path d="M12 5v14" /></svg>
          </button>
        </div>

        <!-- Page Number -->
        <div class="flex flex-col items-end leading-none gap-0.5">
          <span class="text-[9px] font-bold text-stone-500 uppercase tracking-wider">لقطة</span>
          <span class="font-mono text-xs font-bold text-stone-300">#{{ shotNumber }}</span>
        </div>
      </div>
    </footer>
  </div>
</template>

<style>
/* Scoped styles mainly for scrollbars, kept minimal */
.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
