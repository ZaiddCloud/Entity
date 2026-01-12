<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

// State
const shotNumber = ref(1);
const viewMode = ref('list'); // 'list' (vertical reading), 'grid', 'default' (slider)
const isCompareMode = ref(false);
const windowWidth = ref(1024); // Default to desktop logic initially

// Versions Data (Mocking Real Data found in Storage)
const versions = [
    { id: 'original', name: 'النسخة الأصلية', folder: '3014', offset: 90 },
    { id: 'copy_a', name: 'نسخة برلين', folder: 'New Folder', offset: 0 },
    { id: 'copy_b', name: 'نسخة باريس', folder: 'New Folder 1', offset: 4 }
];

const selectedVersionIds = ref(['original']); // Default to Original

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
    // Total files in folder 3014 is ~219. 
    return 219; 
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
    // Return versions sorted by their original order in the 'versions' definition
    // to keep the UI stable.
    return versions.filter(v => selectedVersionIds.value.includes(v.id));
});

// Logic: Reset widths when displayedVersions changes
watch(displayedVersions, (newVersions) => {
    if (newVersions.length > 0) {
        // Distribute width equally
        const width = 100 / newVersions.length
        panelWidths.value = newVersions.map(() => width)
    }
}, { immediate: true })

// Watch displayedVersions to reset widths
watch(displayedVersions, (newVersions) => {
    if (newVersions.length > 0) {
        const width = 100 / newVersions.length
        panelWidths.value = newVersions.map(() => width)
    }
}, { immediate: true })

// Image URL Generation
const getPageUrl = (shotIndex, version = versions[0]) => {
    const number = shotIndex + version.offset;
    const padded = number.toString().padStart(5, '0');
    // Encode the folder name to handle spaces (e.g. "New Folder")
    const folder = encodeURIComponent(version.folder);
    return `/storage/manuscripts/${version.folder}/DSC${padded}.JPG`;
};
</script>

<template>
  <!-- 
        Immersive Layout: 
        The Viewport takes the full screen (absolute inset-0).
        Toolbar and Footer float on top (z-10).
    -->
  <div
    class="h-screen w-screen bg-stone-900 relative overflow-hidden font-ui text-gray-800"
    dir="rtl"
  >
    <!-- 
            =========================================
            1. The Content Layer (Viewport) - BASE
            =========================================
            Occupies the entire screen space behind controls.
        -->
    <main class="absolute inset-0 z-0 bg-[#0c0c0c] overflow-hidden">
      <!-- 
                VIEW 1: INDEX VIEW (SHOTS METADATA TABLE)
            -->
      <div
        v-if="viewMode === 'default'"
        class="w-full h-full overflow-y-auto custom-scrollbar"
      >
        <div class="max-w-5xl mx-auto flex flex-col gap-2 py-8 pt-20 pb-20 px-4">
          <div
            v-for="i in 50"
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
            v-for="i in 100"
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
      <!-- COMPARE MODE: Side-by-side comparison (VERSION COMPARISON) -->
      <!-- 
                NOTE: Overriding standard 'list' behavior when isCompareMode is true.
                We display multiple versions of the SAME shot side-by-side. 
            -->
      <!-- COMPARE MODE: Side-by-side comparison (VERSION COMPARISON) -->
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
            v-for="i in 15"
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
            DESIGN NOTE:
            This toolbar floats on top of the manuscript image (z-10).
            It uses a "Ghost" style: no solid background bars, only icons and text.
            We use a subtle gradient (black/60 to transparent) to ensure text readability
            regardless of the image brightness underneath.
        -->
    <header class="absolute top-0 left-0 right-0 z-10 h-12 px-4 flex items-center justify-between gap-4 transition-all bg-gradient-to-b from-black/60 to-transparent">
      <!-- Right Section: Navigation Tools -->
      <div class="flex items-center gap-4 flex-1 md:flex-initial">
        <!-- 
                    1. Versions / Copies List
                    Lists available versions. In Single Mode, acts as a switcher. In Compare Mode, acts as a multi-selector.
                 -->
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
            <!-- Checkbox/Toggle for Compare Mode (Implicit in click) -->
            <span 
              v-if="selectedVersionIds.includes(version.id)"
              class="mr-2 cursor-pointer opacity-80 hover:opacity-100"
              @click.stop="toggleVersionSelection(version.id)"
            >✕</span>

            <span class="text-[11px] font-medium whitespace-nowrap mr-2">{{ version.name }}</span>

            <!-- Input Box (Only visible if selected) -->
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

        <!-- Separator (Visual divider) -->
        <div class="w-px h-4 bg-white/10 hidden md:block" />

        <!-- 
                    2. Shot Input (Direct Navigation)
                    Allows user to type a shot number to jump directly.
                    Style: Minimal border-b, no box.
                -->
        <div class="flex items-center gap-2 group cursor-text">
          <span class="text-[12px] text-stone-400 group-hover:text-stone-200 transition-colors hidden sm:inline">لقطة</span>
          <div class="relative">
            <input 
              v-model="shotNumber" 
              type="number"
              class="w-12 text-[14px] font-bold text-stone-200 bg-transparent outline-none p-0 border-b border-white/20 focus:border-blue-400 transition-colors placeholder-stone-600 text-center"
              placeholder="#"
            >
          </div>
          <span class="text-[12px] text-stone-500">/ 450</span>
        </div>
      </div>

      <!-- Left Section: View Options -->
      <div class="flex items-center gap-4">
        <!-- 
                    3. View Modes (Segmented Control)
                    Direct access to List (Vertical Reading), Grid, and Slider views.
                 -->
        <div class="flex items-center bg-white/5 rounded-full p-1 border border-white/5 backdrop-blur-sm">
          <!-- List View (Vertical Reading - DEFAULT) -->
          <button 
            class="w-8 h-8 flex items-center justify-center rounded-full transition-all"
            :class="viewMode === 'list' ? 'bg-indigo-500 text-white shadow-sm' : 'text-stone-400 hover:text-stone-200 hover:bg-white/5'"
            title="القراءة العمودية"
            @click="viewMode = 'list'"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="lucide lucide-rectangle-horizontal"
            ><rect
              width="20"
              height="12"
              x="2"
              y="6"
              rx="2"
            /></svg>
          </button>

          <!-- Grid View -->
          <button 
            class="w-8 h-8 flex items-center justify-center rounded-full transition-all"
            :class="viewMode === 'grid' ? 'bg-indigo-500 text-white shadow-sm' : 'text-stone-400 hover:text-stone-200 hover:bg-white/5'"
            title="عرض الشبكة"
            @click="viewMode = 'grid'"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="lucide lucide-layout-grid"
            ><rect
              width="7"
              height="7"
              x="3"
              y="3"
              rx="1"
            /><rect
              width="7"
              height="7"
              x="14"
              y="3"
              rx="1"
            /><rect
              width="7"
              height="7"
              x="14"
              y="14"
              rx="1"
            /><rect
              width="7"
              height="7"
              x="3"
              y="14"
              rx="1"
            /></svg>
          </button>

          <!-- Index View (Metadata Table) -->
          <button 
            class="w-8 h-8 flex items-center justify-center rounded-full transition-all"
            :class="viewMode === 'default' ? 'bg-indigo-500 text-white shadow-sm' : 'text-stone-400 hover:text-stone-200 hover:bg-white/5'"
            title="الفهرس"
            @click="viewMode = 'default'"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="lucide lucide-list"
            ><line
              x1="8"
              x2="21"
              y1="6"
              y2="6"
            /><line
              x1="8"
              x2="21"
              y1="12"
              y2="12"
            /><line
              x1="8"
              x2="21"
              y1="18"
              y2="18"
            /><line
              x1="3"
              x2="3.01"
              y1="6"
              y2="6"
            /><line
              x1="3"
              x2="3.01"
              y1="12"
              y2="12"
            /><line
              x1="3"
              x2="3.01"
              y1="18"
              y2="18"
            /></svg>
          </button>
        </div>

        <!-- Separator -->
        <div class="w-px h-4 bg-white/10" />

        <!-- 
                    5. Compare Switcher (Renumbered)
                    Toggle between Single vs Compare mode side-by-side.
                 -->
        <div class="flex items-center gap-3">
          <button 
            class="text-[13px] transition-colors focus:outline-none"
            :class="!isCompareMode ? 'font-bold text-stone-200' : 'font-medium text-stone-500 hover:text-stone-300'"
            @click="isCompareMode = false; selectedVersionIds = [selectedVersionIds[0] || 'original']"
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
            DESIGN NOTE:
            Simple, translucent rail for navigating shots (pages).
            We use a gradient background to lift the thumbnails off the image.
        -->
    <footer class="absolute bottom-0 left-0 right-0 z-10 h-12 w-full bg-gradient-to-t from-black/80 to-transparent flex items-center px-4 gap-4 transition-all">
      <!-- 
                Filmstrip: Horizontal scroll of thumbnails.
                LAYOUT LOGIC:
                - flex-1: Takes all available width between start and Status Info.
                - dir="ltr": Forces left-to-right order for consistency (Shot 1, 2, 3...).
                - justify-start md:justify-center: 
                  On Mobile: Aligns to start to ensure the first items are reachable via scroll (scrolling center-aligned content can clip start).
                  On Desktop: Centers the items if they fit, for a balanced "Dock" look.
                - v-for="i in filmstripCount":
                  Dynamically renders 10 items on mobile and 30 on desktop to adapt to screen width.
             -->
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

      <!-- 
                Controls & Info Group
                Includes: Zoom Controls + Shot Indicator
             -->
      <div class="flex items-center gap-3 border-l border-white/10 pl-3 shrink-0 h-8">
        <!-- Zoom Controls (Mini) -->
        <div class="flex items-center gap-1 bg-white/5 rounded-full px-1.5 py-0.5 border border-white/5 backdrop-blur-sm">
          <button
            class="w-5 h-5 flex items-center justify-center rounded-full hover:bg-white/10 text-stone-400 hover:text-stone-200 transition-colors"
            title="تصغير"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="12"
              height="12"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="lucide lucide-minus"
            ><path d="M5 12h14" /></svg>
          </button>
          <span class="text-[10px] font-bold text-stone-300 w-8 text-center font-mono">100%</span>
          <button
            class="w-5 h-5 flex items-center justify-center rounded-full hover:bg-white/10 text-stone-400 hover:text-stone-200 transition-colors"
            title="تكبير"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="12"
              height="12"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="lucide lucide-plus"
            ><path d="M5 12h14" /><path d="M12 5v14" /></svg>
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
.font-ui {
    font-family: 'Inter', 'Noto Sans Arabic', sans-serif;
}
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
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
