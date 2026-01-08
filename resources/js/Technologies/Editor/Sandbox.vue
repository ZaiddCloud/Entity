<script setup>
import { onMounted } from 'vue'
import TiptapEditor from './Core/TiptapEditor.vue'
import EditorToolbar from './Toolbar/EditorToolbar.vue'
import { useTiptapStore } from './Core/TiptapStore'

const store = useTiptapStore()

// Mock logic for sandbox
onMounted(() => {
    store.updateContent('<p>مرحباً بك في بيئة المحرر المعزولة (Sandbox)! 🧪</p><p>يمكنك تجربة كل خصائص المحرر هنا بحرية.</p>')
})

const handleCommand = ({ command, value }) => {
    store.executeCommand(command, value)
}
</script>

<template>
    <div class="h-screen bg-gray-50 font-ui flex flex-col overflow-hidden" dir="rtl">
        <!-- Sandbox Header (Minimal) -->
        <div class="flex items-center justify-between px-4 py-2 bg-white border-b border-gray-200 shrink-0 z-40">
            <div class="flex items-center gap-3">
                <span class="text-xl">🏗️</span>
                <div>
                    <h1 class="text-sm font-bold text-gray-800 leading-none">مختبر المحرر</h1>
                    <p class="text-[10px] text-gray-400 leading-none mt-0.5">Sandbox Environment</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-50 text-xs">تصفير</button>
                <button class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs shadow-sm">حفظ وهمي</button>
            </div>
        </div>

        <!-- The Editor Assembly (Full Screen) -->
        <div class="flex-1 flex flex-col bg-white overflow-hidden relative">
            <!-- Toolbar -->
            <EditorToolbar @command="handleCommand" />
            
            <!-- Editor Core -->
            <div class="flex-1 overflow-y-auto bg-white">
                <div class="w-full min-h-full p-8 md:p-12">
                     <TiptapEditor v-model="store.content" @set-editor="store.setEditor" />
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.font-ui {
    font-family: 'Inter', 'Noto Sans Arabic', sans-serif;
}
</style>
