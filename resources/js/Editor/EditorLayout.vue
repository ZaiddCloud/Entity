<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useEditorStore } from './Store/editorStore'

const props = defineProps({
    title: {
        type: String,
        default: 'Entity Editor'
    }
})

const store = useEditorStore()
const isToolbarVisible = ref(true)
let hideTimeout = null

const showToolbar = () => {
    clearTimeout(hideTimeout)
    isToolbarVisible.value = true
}

const hideToolbar = () => {
    if (store.isToolbarPinned) return
    hideTimeout = setTimeout(() => {
        isToolbarVisible.value = false
    }, 2500)
}

watch(() => store.isToolbarPinned, (isPinned) => {
    if (isPinned) showToolbar()
    else hideToolbar()
})

onMounted(() => {
    hideToolbar()
})

onUnmounted(() => {
    clearTimeout(hideTimeout)
})
</script>

<template>
    <div class="h-screen flex flex-col bg-gray-100 overflow-hidden" dir="rtl">
        <Head :title="title" />
        
        <!-- Toolbar Interaction Zone -->
        <div 
            @mouseenter="showToolbar" 
            @mouseleave="hideToolbar"
            class="absolute top-0 left-0 right-0 h-2 z-50"
        ></div>
        
        <!-- Main Toolbar -->
        <transition name="toolbar-slide">
            <div v-show="isToolbarVisible || store.isToolbarPinned" class="z-40">
                <slot name="toolbar" />
            </div>
        </transition>
        
        <!-- Workspace -->
        <div class="flex-1 flex overflow-hidden relative">
            <!-- Sidebar (Optional) -->
            <slot name="sidebar" />
            
            <!-- Editor Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 selection:bg-blue-100">
                <div class="max-w-[850px] mx-auto py-12 px-4">
                    <slot />
                </div>
            </main>
        </div>
        
        <!-- Status Bar -->
        <footer class="h-6 bg-white border-t border-gray-200 flex items-center px-4 justify-between text-[10px] text-gray-500 z-10">
            <div class="flex items-center gap-4">
                <span v-if="store.isSaving" class="flex items-center gap-1">
                    <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                    جاري الحفظ...
                </span>
                <span v-else-if="store.lastSaved" class="text-gray-400">
                    آخر حفظ: {{ new Date(store.lastSaved).toLocaleTimeString('ar-SA') }}
                </span>
            </div>
            <div class="flex items-center gap-4 bg-gray-50 px-2 py-0.5 rounded border border-gray-100">
                <span>النمط: {{ store.editorMode }}</span>
                <span class="w-px h-3 bg-gray-300"></span>
                <span dir="ltr">Entity Editor v2.0</span>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.toolbar-slide-enter-active,
.toolbar-slide-leave-active {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
}

.toolbar-slide-enter-from,
.toolbar-slide-leave-to {
    transform: translateY(-100%);
    opacity: 0;
}

.font-arabic {
    font-family: 'Amiri', 'Traditional Arabic', serif;
}
</style>
