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
const isToolbarVisible = ref(false) // Hidden by default in new design
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
    // Start hidden, unless pinned
    if (!store.isToolbarPinned) {
        hideToolbar()
    } else {
        showToolbar()
    }
})

onUnmounted(() => {
    clearTimeout(hideTimeout)
})
</script>

<template>
    <div class="h-screen flex flex-col bg-[#f3f4f6] overflow-hidden relative font-ui" dir="rtl">
        <Head :title="title" />
        
        <!-- Toolbar Interaction Zone (Top 10px) -->
        <div 
            @mouseenter="showToolbar"
            class="fixed top-0 left-0 right-0 h-2 z-[100]"
        ></div>
        
        <!-- Main Toolbar Wrapper -->
        <div 
            @mouseenter="showToolbar" 
            @mouseleave="hideToolbar"
            class="fixed top-0 left-0 right-0 z-50 transition-all duration-400 ease-out"
            :class="[
                (isToolbarVisible || store.isToolbarPinned) ? 'translate-y-0 opacity-100 pointer-events-auto' : '-translate-y-[95%] opacity-10 pointer-events-none hover:translate-y-0 hover:opacity-100'
            ]"
        >
            <slot name="toolbar" />
        </div>
        
        <!-- Workspace -->
        <div class="flex-1 flex overflow-hidden relative pt-[42px]"> <!-- pt-[42px] to prevent content overlap -->
            <!-- Sidebar (Optional) -->
            <slot name="sidebar" />
            
            <!-- Editor Content -->
            <main class="flex-1 overflow-y-auto scroll-smooth">
                <div class="max-w-[850px] mx-auto py-10 px-4">
                     <!-- Paper Sheet Effect -->
                    <div class="bg-white shadow-[0_10px_25px_rgba(0,0,0,0.05)] rounded-sm min-h-[calc(100vh-100px)] p-16 transition-all duration-300">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
        
        <!-- Status Bar -->
        <footer class="h-6 bg-white border-t border-gray-200 flex items-center px-4 justify-between text-[10px] text-gray-400 fixed bottom-0 left-0 right-0 z-40">
            <div class="flex items-center gap-4">
                 <span v-if="store.isSaving" class="text-blue-500 font-bold animate-pulse">
                    ⌛ جاري الحفظ...
                </span>
                <span v-else-if="store.lastSaved" class="text-green-600 flex items-center gap-1">
                    ✅ تم الحفظ {{ new Date(store.lastSaved).toLocaleTimeString('ar-SA') }}
                </span>
                 <span v-else>جاهز للعمل</span>
            </div>
            <div dir="ltr">Entity Editor v2.0 | Dual Interface</div>
        </footer>
    </div>
</template>

<style>
.font-ui {
    font-family: 'Noto Kufi Arabic', sans-serif;
}
</style>
