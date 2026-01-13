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
    }
})

const containerRef = ref(null)
const splitPercent = ref(props.initialSplit)
const isResizing = ref(false)

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
    // In RTL, 0 is on the right.
    // X coordinate is relative to viewport.
    // Let's calculate percentage based on width.
    
    // For English (LTR): percent = ((e.clientX - rect.left) / rect.width) * 100
    // For RTL: percent = ((rect.right - e.clientX) / rect.width) * 100
    // Usually browser handles structure, but we need to supply width percentages.
    
    // Let's assume standard Flexbox behavior where order matters.
    // If pane 1 is Right (Start) and pane 2 is Left (End).
    
    // Let's calculate from Left for simplicity as Flex logic usually follows DOM order.
    // If standard LTR DOM: Left Box | Handle | Right Box
    // If RTL: Right Box | Handle | Left Box
    
    // We will calculate width of the *First Child* (Start Pane).
    // In RTL, Start is Right.
    
    const x = e.clientX - rect.left
    const width = rect.width
    let percent = (x / width) * 100
    
    // RTL Adjustment: If dir="rtl", 0 is right. 
    // Actually, getBoundingClientRect().left is always visual left.
    // If RTL, the logical "Start" is on the right.
    // But let's verify DOM order. Standard: <Pane1> <Handle> <Pane2>.
    // In RTL, Pane1 is visually on Right. Pane2 on Left.
    // If mouse moves LEFT (decreasing X), Pane1 (Right) grows? No, Pane1 (Right) needs to grab width-difference.
    
    // Let's stick to simple visual calculation.
    // We want Split to represent width of the First Pane.
    // If RTL, First Pane is on the Right.
    // So if I drag Left (smaller X), the Right Pane grows. 
    // Wait, rect.right is constant. e.clientX decreases. difference increases.
    
    const isRtl = document.dir === 'rtl' || document.documentElement.dir === 'rtl'
    
    if (isRtl) {
        percent = ((rect.right - e.clientX) / width) * 100
    } else {
        percent = ((e.clientX - rect.left) / width) * 100
    }
    
    splitPercent.value = Math.min(100 - props.minSize, Math.max(props.minSize, percent))
}

const stopResize = () => {
    isResizing.value = false
    document.body.style.cursor = ''
    document.body.style.userSelect = ''
    window.removeEventListener('mousemove', handleMouseMove)
    window.removeEventListener('mouseup', stopResize)
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
