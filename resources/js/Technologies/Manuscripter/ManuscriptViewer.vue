<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import ResourceNavigator from '@/Technologies/Common/ResourceNavigator.vue'

defineOptions({
  name: 'ManuscriptViewer'
})

const props = defineProps(['resource', 'currentNode'])

const versions = computed(() => {
    console.log('ManuscriptViewer Resource:', props.resource)
    if (!props.resource?.versions) {
        console.warn('No versions found in resource')
        return []
    }
    console.log('Versions found:', props.resource.versions)
    return props.resource.versions
})

const activeVersionIndex = ref(0)
const selectedVersionIndexes = ref([0])
const isCompareMode = ref(false)

// Viewer State
const zoomLevel = ref(1.0)
const panelWidths = ref([])
const isResizing = ref(false)
const containerRef = ref(null)

// Panning State
const isPanning = ref(false)
const panStart = ref({ x: 0, y: 0 })
const scrollStart = ref({ left: 0, top: 0 })

// Initialize panels
watch([versions, isCompareMode], () => {
    if (isCompareMode.value) {
        // Equal widths for selected versions
        const count = selectedVersionIndexes.value.length || 1
        panelWidths.value = Array(count).fill(100 / count)
    } else {
        // Single view
        panelWidths.value = [100]
    }
}, { immediate: true })

const displayedVersions = computed(() => {
    if (isCompareMode.value) {
        return selectedVersionIndexes.value.map(i => versions.value[i])
    }
    return [versions.value[activeVersionIndex.value]]
})

const toggleVersionSelection = (index) => {
    if (!isCompareMode.value) {
        activeVersionIndex.value = index
        return
    }
    
    const i = selectedVersionIndexes.value.indexOf(index)
    if (i === -1) {
        selectedVersionIndexes.value.push(index)
    } else if (selectedVersionIndexes.value.length > 1) {
        selectedVersionIndexes.value.splice(i, 1)
    }
    selectedVersionIndexes.value.sort((a, b) => a - b)
}

// Zoom Controls
const zoomIn = () => zoomLevel.value = Math.min(zoomLevel.value + 0.1, 3)
const zoomOut = () => zoomLevel.value = Math.max(zoomLevel.value - 0.1, 0.5)
const resetZoom = () => zoomLevel.value = 1.0

// Panning Logic
const handlePanStart = (e, idx) => {
    isPanning.value = true
    panStart.value = { x: e.clientX, y: e.clientY }
    // We assume the target element is the scrollable container
    const target = e.currentTarget
    scrollStart.value = { left: target.scrollLeft, top: target.scrollTop }
}

const handlePanMove = (e) => {
    if (!isPanning.value) return
    const dx = e.clientX - panStart.value.x
    const dy = e.clientY - panStart.value.y
    const target = e.currentTarget
    target.scrollLeft = scrollStart.value.left - dx
    target.scrollTop = scrollStart.value.top - dy
}

const handlePanEnd = () => {
    isPanning.value = false
}

// Panel Resizing (Simplified)
const startResizing = (e, idx) => {
    // Implementation left simple for now
    console.log('Resize start', idx)
}
</script>

<template>
    <div class="h-full bg-gray-50 flex flex-col border-l border-gray-200 overflow-hidden">
        <!-- Versions Toolbar (Glassmorphism) -->
        <div class="glass-header flex items-center justify-between border-b border-gray-200 px-2 z-10 sticky top-0">
            <div class="flex items-center gap-4 overflow-hidden">
                
                <!-- Resource Navigator -->
                <div class="border-l border-gray-200 pl-4 ml-2">
                     <ResourceNavigator type="manuscript" :current-id="resource?.id" />
                </div>

                <!-- Manuscript Title -->
                <div v-if="resource?.title" class="text-sm font-bold text-gray-700 whitespace-nowrap my-2 flex items-center">
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
            <div class="flex items-center gap-2 border-r border-gray-200 pr-3 mr-1">
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
                            <div v-if="version?.url" 
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
.glass-header {
    background: rgba(255, 255, 255, 0.98) !important;
    backdrop-filter: blur(12px) !important;
}

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
