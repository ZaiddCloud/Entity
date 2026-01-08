<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'

defineOptions({
  name: 'ManuscriptViewer'
})

const props = defineProps(['resource', 'currentNode'])

const activeVersionIndex = ref(0)
const isCompareMode = ref(false)
const selectedVersionIndexes = ref([0])

// Width tracking for versions in compare mode
const panelWidths = ref([])
const isResizing = ref(-1) // Index of handle being dragged
const containerRef = ref(null)
let startX = 0
let startWidths = []

const zoomLevel = ref(1)
const zoomIn = () => { if (zoomLevel.value < 5) zoomLevel.value += 0.2 }
const zoomOut = () => { if (zoomLevel.value > 0.4) zoomLevel.value -= 0.2 }
const resetZoom = () => { zoomLevel.value = 1 }

// Panning State
const isPanning = ref(false)
const panStartX = ref(0)
const panStartY = ref(0)
const panScrollLeft = ref(0)
const panScrollTop = ref(0)
const viewerRefs = ref([]) // To track individual panel containers

const handlePanStart = (e, idx) => {
    const container = e.currentTarget
    isPanning.value = true
    panStartX.value = e.pageX - container.offsetLeft
    panStartY.value = e.pageY - container.offsetTop
    panScrollLeft.value = container.scrollLeft
    panScrollTop.value = container.scrollTop
    container.style.cursor = 'grabbing'
}

const handlePanMove = (e) => {
    if (!isPanning.value) return
    e.preventDefault()
    const container = e.currentTarget
    const x = e.pageX - container.offsetLeft
    const y = e.pageY - container.offsetTop
    const walkX = (x - panStartX.value) * 1.5 // Scroll speed
    const walkY = (y - panStartY.value) * 1.5
    container.scrollLeft = panScrollLeft.value - walkX
    container.scrollTop = panScrollTop.value - walkY
}

const handlePanEnd = (e) => {
    if (!isPanning.value) return
    isPanning.value = false
    e.currentTarget.style.cursor = 'grab'
}

const versions = computed(() => {
    const v = []
    
    // 1. Prioritize current node image if present (The actual page being edited)
    if (props.currentNode?.image_url) {
        v.push({ title: props.currentNode.title || 'الصفحة الحالية', url: props.currentNode.image_url })
    }

    // 2. Add other versions from resource
    if (props.resource?.versions && Array.isArray(props.resource.versions)) {
        props.resource.versions.forEach(version => v.push(version))
    }
    
    // 3. Fallback if still empty
    if (v.length === 0 && props.resource?.url) {
        v.push({ title: 'الملف الأساسي', url: props.resource.url })
    }

    return v
})

const displayedVersions = computed(() => {
    if (!isCompareMode.value) {
        const active = versions.value[activeVersionIndex.value]
        return active ? [active] : []
    }
    return versions.value.filter((_, index) => selectedVersionIndexes.value.includes(index))
})

// Initialize widths when versions change
watch(() => displayedVersions.value.length, (count) => {
    panelWidths.value = new Array(count).fill(100 / count)
}, { immediate: true })

const toggleVersionSelection = (index) => {
    if (!isCompareMode.value) {
        activeVersionIndex.value = index
        return
    }
    
    if (selectedVersionIndexes.value.includes(index)) {
        if (selectedVersionIndexes.value.length > 1) {
            selectedVersionIndexes.value = selectedVersionIndexes.value.filter(i => i !== index)
        }
    } else {
        selectedVersionIndexes.value.push(index)
    }
}

// Resizing logic
const startResizing = (e, index) => {
    e.preventDefault() // CRITICAL: Prevents default drag behavior which causes "unlatching"
    isResizing.value = index
    startX = e.clientX
    startWidths = [...panelWidths.value]
    
    document.addEventListener('mousemove', handleMouseMove)
    document.addEventListener('mouseup', stopResizing)
    document.body.style.cursor = 'col-resize'
    document.body.style.userSelect = 'none'
}

const handleMouseMove = (e) => {
    if (isResizing.value === -1 || !containerRef.value) return
    
    const containerRect = containerRef.value.getBoundingClientRect()
    const totalWidth = containerRect.width
    const isRTL = document.dir === 'rtl'
    
    // Calculate delta in percentage
    const mouseDeltaX = e.clientX - startX
    const deltaPercent = (mouseDeltaX / totalWidth) * 100
    
    // Apply delta (negate in RTL because moving mouse left should expand the right-side panel)
    const delta = isRTL ? -deltaPercent : deltaPercent
    
    const newLeftWidth = startWidths[isResizing.value] + delta
    const newRightWidth = startWidths[isResizing.value + 1] - delta
    
    // Minimum panel width 10%
    const minWidth = 10
    
    if (newLeftWidth > minWidth && newRightWidth > minWidth) {
        panelWidths.value[isResizing.value] = newLeftWidth
        panelWidths.value[isResizing.value + 1] = newRightWidth
    }
}

const stopResizing = () => {
    isResizing.value = -1
    document.removeEventListener('mousemove', handleMouseMove)
    document.removeEventListener('mouseup', stopResizing)
    document.body.style.cursor = ''
    document.body.style.userSelect = ''
}

onUnmounted(() => {
    stopResizing()
})
</script>

<template>
    <div class="h-full bg-gray-50 flex flex-col border-l border-gray-200 overflow-hidden">
        <!-- Versions Toolbar -->
        <div class="flex items-center justify-between border-b border-gray-200 bg-white px-2">
            <div class="flex items-center gap-4 overflow-hidden">
                <!-- Manuscript Title -->
                <div v-if="resource?.title" class="text-sm font-bold text-gray-700 whitespace-nowrap border-l pl-4 ml-2 my-2">
                    <i class="fas fa-book-open text-gray-400 ml-2"></i>
                    {{ resource?.title }}
                </div>

                <div class="flex overflow-x-auto no-scrollbar">
                <button 
                    v-for="(version, index) in versions" 
                    :key="index"
                    @click="toggleVersionSelection(index)"
                    class="px-4 py-2 text-xs font-medium transition-colors border-b-2 whitespace-nowrap"
                    :class="[
                        (!isCompareMode && activeVersionIndex === index) || (isCompareMode && selectedVersionIndexes.includes(index))
                            ? 'border-blue-500 text-blue-600 bg-blue-50/50' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                    ]"
                >
                    {{ version?.title }}
                </button>

            </div>
            </div> <!-- Closing the gap-4 wrapper -->
            
            <!-- Compare Mode Toggle -->
            <div class="flex items-center gap-2 border-r border-gray-100 pr-3 mr-1">
                <label class="relative inline-flex items-center cursor-pointer scale-75">
                    <input type="checkbox" v-model="isCompareMode" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    <span class="mr-2 text-[10px] font-bold text-gray-400 uppercase">مقارنة</span>
                </label>
            </div>
        </div>

        <!-- Viewer Content (Resizable Flex) -->
        <div 
            ref="containerRef"
            class="flex-1 flex gap-0 p-1 bg-gray-200 overflow-auto relative h-full"
        >
            <template v-for="(version, idx) in displayedVersions" :key="idx">
                <div 
                    v-if="version"
                    class="bg-white flex flex-col h-full rounded-sm shadow-sm relative overflow-hidden group min-w-[150px]"
                    :style="{ width: panelWidths[idx] + '%' }"
                >
                    <!-- Version Header -->
                    <div class="px-2 py-1 bg-gray-50 border-b border-gray-100 flex justify-between items-center shrink-0">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tight truncate">
                                {{ resource?.title }} - {{ version?.title }}
                            </span>
                            <!-- Simplified Shot Number Input -->
                            <div class="flex items-center bg-white border border-gray-200 rounded px-1 h-5 hover:border-blue-200 transition-colors">
                                <input 
                                    type="text" 
                                    placeholder="رقم اللقطة" 
                                    class="w-12 bg-transparent text-[9px] text-blue-600 font-bold border-none p-0 focus:ring-0 text-center placeholder:text-gray-300 placeholder:font-normal"
                                    @keyup.enter="$event.target.blur()"
                                >
                            </div>
                        </div>
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                            <button class="text-gray-400 hover:text-blue-500"><i class="fas fa-expand-alt text-[10px]"></i></button>
                        </div>
                    </div>

                    <!-- Viewer Area -->
                    <div 
                        class="flex-1 overflow-auto bg-gray-900 custom-scrollbar p-10 cursor-grab active:cursor-grabbing select-none"
                        @mousedown="handlePanStart($event, idx)"
                        @mousemove="handlePanMove($event)"
                        @mouseup="handlePanEnd($event)"
                        @mouseleave="handlePanEnd($event)"
                    >
                        <div class="flex flex-col items-center justify-start min-h-full transition-all duration-300 mx-auto pointer-events-none" :style="{ width: (zoomLevel * 100) + '%', minWidth: '100%' }">
                            <!-- Image Renderer -->
                            <div v-if="version?.url && (version.url.toLowerCase().endsWith('.jpg') || version.url.toLowerCase().endsWith('.jpeg') || version.url.toLowerCase().endsWith('.png') || version.url.toLowerCase().endsWith('.webp'))" 
                                 class="relative inline-block w-full text-center">
                                <img :src="version?.url" class="inline-block max-w-full shadow-2xl rounded-sm border-4 border-gray-800" alt="Manuscript Page" />
                            </div>
                            
                            <!-- Placeholder / PDF Renderer -->
                            <div v-else class="w-full max-w-lg bg-gray-50/50 border border-gray-100 rounded-sm p-4 text-center mt-10">
                                <div class="aspect-[3/4] bg-gray-100 flex items-center justify-center border-2 border-dashed border-gray-200 rounded-lg mb-4">
                                    <div class="text-gray-400">
                                        <p class="text-[10px]">نسخة التحقيق: {{ version?.title }}</p>
                                    </div>
                                </div>
                                <p class="text-[9px] text-gray-400 font-mono truncate px-2">{{ version?.url }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resize Handle between panels -->
                <div 
                    v-if="idx < displayedVersions.length - 1"
                    class="w-1.5 h-full bg-transparent hover:bg-blue-400/30 cursor-col-resize z-20 shrink-0 transition-colors"
                    @mousedown="startResizing($event, idx)"
                ></div>
            </template>
        </div>
        
        <!-- Global Controls Floating -->
        <div class="absolute bottom-6 left-6 flex flex-col gap-2 z-30">
            <button @click="resetZoom" class="w-10 h-10 rounded-full bg-white/80 backdrop-blur shadow-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-blue-500 transition-all hover:scale-110" title="ملاءمة الشاشة">
                <i class="fas fa-expand"></i>
            </button>
            <button @click="zoomIn" class="w-10 h-10 rounded-full bg-white/80 backdrop-blur shadow-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-blue-500 transition-all hover:scale-110" title="تكبير">
                <i class="fas fa-plus"></i>
            </button>
            <button @click="zoomOut" class="w-10 h-10 rounded-full bg-white/80 backdrop-blur shadow-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-blue-500 transition-all hover:scale-110" title="تصغير">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
    height: 8px;
    display: block !important;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #1f2937; /* gray-800 */
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #4b5563; /* gray-600 */
    border-radius: 4px;
    border: 2px solid #1f2937;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #6b7280; /* gray-500 */
}

/* Ensure firefox supports it too */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #4b5563 #1f2937;
}
</style>
