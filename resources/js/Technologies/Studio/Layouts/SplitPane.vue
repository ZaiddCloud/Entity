<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    initialSplit: {
        type: Number,
        default: 50
    },
    minSize: {
        type: Number,
        default: 20
    },
    persistenceKey: {
        type: String,
        default: 'studio-split-pane'
    }
})

const containerRef = ref(null)
const splitPercent = ref(props.initialSplit)
const isResizing = ref(false)

onMounted(() => {
    // Restore from localStorage
    if (props.persistenceKey) {
        const saved = localStorage.getItem(props.persistenceKey)
        if (saved) {
            const val = parseFloat(saved)
            if (!isNaN(val) && val >= props.minSize && val <= (100 - props.minSize)) {
                splitPercent.value = val
            }
        }
    }
})

const startResize = () => {
    isResizing.value = true
    document.body.style.cursor = 'col-resize'
    document.body.style.userSelect = 'none'
    window.addEventListener('mousemove', handleMouseMove)
    window.addEventListener('mouseup', stopResize)
}

const handleMouseMove = (e) => {
    if (!isResizing.value || !containerRef.value) return

    const rect = containerRef.value.getBoundingClientRect()
    const width = rect.width
    
    // Determine RTL
    const isRtl = document.dir === 'rtl' || document.documentElement.dir === 'rtl'
    
    let percent = 0
    if (isRtl) {
        percent = ((rect.right - e.clientX) / width) * 100
    } else {
        percent = ((e.clientX - rect.left) / width) * 100
    }
    
    const newPercent = Math.min(100 - props.minSize, Math.max(props.minSize, percent))
    splitPercent.value = newPercent
}

const stopResize = () => {
    isResizing.value = false
    document.body.style.cursor = ''
    document.body.style.userSelect = ''
    window.removeEventListener('mousemove', handleMouseMove)
    window.removeEventListener('mouseup', stopResize)
    
    // Save to localStorage
    if (props.persistenceKey) {
        localStorage.setItem(props.persistenceKey, splitPercent.value)
    }
}
</script>

<template>
  <div 
    ref="containerRef"
    class="w-full h-full flex overflow-hidden bg-gray-900 border-t border-gray-800"
  >
    <!-- Pane 1 (Start/Right in RTL) -->
    <div 
      class="relative overflow-hidden flex flex-col"
      :style="{ width: `${splitPercent}%` }"
    >
      <slot name="pane-1" />
      
      <!-- Overlay when resizing to prevent iframe logic stealing mouse -->
      <div 
        v-if="isResizing" 
        class="absolute inset-0 z-50 bg-transparent" 
      />
    </div>

    <!-- Handle -->
    <div
      class="w-1 bg-gray-800 hover:bg-blue-500 cursor-col-resize z-10 flex items-center justify-center transition-colors delay-75 relative group"
      @mousedown.prevent="startResize"
    >
      <!-- Hit area expander -->
      <div class="absolute inset-y-0 -left-2 -right-2 z-20 cursor-col-resize" />
      
      <!-- Visual indicator -->
      <div class="w-0.5 h-8 bg-gray-600 rounded-full group-hover:bg-white transition-colors" />
    </div>

    <!-- Pane 2 (End/Left in RTL) -->
    <div 
      class="flex-1 relative overflow-hidden flex flex-col"
      :style="{ width: `${100 - splitPercent}%` }"
    >
      <slot name="pane-2" />
      
      <div 
        v-if="isResizing" 
        class="absolute inset-0 z-50 bg-transparent" 
      />
    </div>
  </div>
</template>
