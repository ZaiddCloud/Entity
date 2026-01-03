<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'

defineOptions({
  name: 'ManuscriptViewer'
})

const props = defineProps(['resource'])

const activeVersionIndex = ref(0)
const isCompareMode = ref(false)
const selectedVersionIndexes = ref([0])

// Width tracking for versions in compare mode
const panelWidths = ref([])
const isResizing = ref(-1) // Index of handle being dragged
const containerRef = ref(null)
let startX = 0
let startWidths = []

const versions = computed(() => {
    if (props.resource?.versions && Array.isArray(props.resource.versions)) {
        return props.resource.versions
    }
    if (props.resource?.url) {
        return [{ title: 'النسخة الأصلية', url: props.resource.url }]
    }
    return []
})

const displayedVersions = computed(() => {
    if (!isCompareMode.value) {
        return [versions.value[activeVersionIndex.value]]
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
                    {{ version.title }}
                </button>
            </div>
            
            <!-- Compare Mode Toggle -->
            <div v-if="versions.length > 1" class="flex items-center gap-2 border-r border-gray-100 pr-3 mr-1">
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
                    class="bg-white flex flex-col h-full rounded-sm shadow-sm relative overflow-hidden group min-w-[150px]"
                    :style="{ width: panelWidths[idx] + '%' }"
                >
                    <!-- Version Header -->
                    <div class="px-2 py-1 bg-gray-50 border-b border-gray-100 flex justify-between items-center shrink-0">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tight truncate">{{ version.title }}</span>
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
                    <div class="flex-1 flex flex-col items-center justify-center p-4">
                        <div class="w-full h-full flex flex-col items-center justify-center">
                            <div class="w-full max-w-lg bg-gray-50/50 border border-gray-100 rounded-sm p-4 text-center">
                                <div class="aspect-[3/4] bg-gray-100 flex items-center justify-center border-2 border-dashed border-gray-200 rounded-lg mb-4">
                                    <div class="text-gray-400">
                                        <p class="text-[10px]">نسخة التحقيق: {{ version.title }}</p>
                                    </div>
                                </div>
                                <p class="text-[9px] text-gray-400 font-mono truncate px-2">{{ version.url }}</p>
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
            <button class="w-10 h-10 rounded-full bg-white/80 backdrop-blur shadow-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-blue-500 transition-all hover:scale-110">
                <i class="fas fa-plus"></i>
            </button>
            <button class="w-10 h-10 rounded-full bg-white/80 backdrop-blur shadow-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-blue-500 transition-all hover:scale-110">
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
</style>
