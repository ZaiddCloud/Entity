<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useEditorStore } from './Store/editorStore'

const props = defineProps({
    title: {
        type: String,
        default: 'Entity Editor'
    }
})

const store = useEditorStore()
const isPinned = ref(false)
const showToolbar = ref(false)

// Resizing Logic
const isResizing = ref(false)
const viewerWidth = ref(store.editorMode === 'manuscript' ? 50 : 30) // Default width based on mode

const startResize = () => {
    isResizing.value = true
    document.body.style.cursor = 'col-resize'
    document.body.style.userSelect = 'none'
}

const stopResize = () => {
    isResizing.value = false
    document.body.style.cursor = ''
    document.body.style.userSelect = ''
}

const doResize = (e) => {
    if (!isResizing.value) return
    
    // Calculate new width percentage based on X position
    // Since we are in RTL, X=0 is right side of screen? No, X is always from left.
    // In RTL, aside is on the right.
    // Width of aside = (WindowWidth - e.clientX) / WindowWidth * 100
    const width = ((window.innerWidth - e.clientX) / window.innerWidth) * 100
    
    // Constraints
    if (width > 20 && width < 80) {
        viewerWidth.value = width
    }
}

// Handle scroll or mouse position to show/hide toolbar if not pinned
const handleMouseMove = (e) => {
    if (isResizing.value) {
        doResize(e)
        return
    }
    
    if (isPinned.value) return
    if (e.clientY < 50) {
        showToolbar.value = true
    } else if (e.clientY > 150) {
        showToolbar.value = false
    }
}

onMounted(() => {
    window.addEventListener('mousemove', handleMouseMove)
    window.addEventListener('mouseup', stopResize)
})

onUnmounted(() => {
    window.removeEventListener('mousemove', handleMouseMove)
    window.removeEventListener('mouseup', stopResize)
})
</script>

<template>
    <div class="h-screen flex flex-col bg-[#f3f4f6] overflow-hidden relative font-ui" dir="rtl">
        <!-- Title Bar (Optional/Subtle) -->
        <div v-if="title" class="hidden">{{ title }}</div>

        <!-- Workspace -->
        <div class="flex-1 flex overflow-hidden relative">
            <!-- Editor Content (Right Side in RTL) -->
            <main class="flex-1 overflow-y-auto scroll-smooth bg-white relative">
                <!-- Integrated Toolbar (Sticky inside Editor Area) -->
                <div class="sticky top-0 z-50 bg-white border-b border-gray-100">
                    <slot name="toolbar" />
                </div>

                <div class="w-full h-full transition-all duration-300">
                    <!-- Edge-to-Edge Workspace -->
                    <div class="min-h-full transition-all duration-300">
                        <slot />
                    </div>
                </div>
            </main>

            <!-- Resize Handle -->
            <div 
                v-if="$slots.viewer || store.editorMode === 'manuscript'"
                class="w-1 bg-gray-200 hover:bg-blue-400 cursor-col-resize transition-colors z-20 flex items-center justify-center relative group"
                @mousedown="startResize"
            >
                <div class="absolute inset-y-0 -left-1 -right-1 cursor-col-resize"></div>
                <!-- Small handle dots -->
                <div class="flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="w-1 h-3 bg-blue-500/30 rounded-full"></span>
                </div>
            </div>

            <!-- Viewer Slot (Left Side in RTL) -->
            <aside 
                v-if="$slots.viewer || store.editorMode === 'manuscript'"
                class="border-r border-gray-200 bg-gray-50 overflow-y-auto relative z-10 shadow-inner h-full"
                :style="{ width: viewerWidth + '%' }"
            >
                <slot name="viewer" />
            </aside>
        </div>

        <!-- Status Bar -->
        <footer class="h-6 bg-white border-t border-gray-200 flex items-center px-4 justify-between text-[10px] text-gray-400 fixed bottom-0 left-0 right-0 z-40">
            <div class="flex items-center gap-4">
                <span>جاهز للعمل</span>
                <span class="w-2 h-2 rounded-full bg-green-400"></span>
            </div>
            <div dir="ltr">Entity Editor v2.0 | Dual Interface | {{ Math.round(100 - viewerWidth) }}% Editor</div>
        </footer>
    </div>
</template>

<style scoped>
.font-ui {
    font-family: 'Inter', 'Noto Sans Arabic', sans-serif;
}
aside {
    transition: none; /* Disable transition during resize for smoothness */
}
</style>
