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
    <div class="min-h-screen bg-gray-100 p-8 font-ui" dir="rtl">
        <div class="max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">🏗️ مختبر المحرر (Editor Sandbox)</h1>
                    <p class="text-gray-500 text-sm">بيئة تطوير معزولة لبناء المحرر بشكل مستقل.</p>
                </div>
                <div class="flex gap-2">
                    <button class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50 text-sm">تصفير المحتوى</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">حفظ وهمي</button>
                </div>
            </div>

            <!-- The Editor Assembly -->
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden min-h-[800px] flex flex-col">
                <!-- Toolbar -->
                <EditorToolbar @command="handleCommand" />
                
                <!-- Editor Core -->
                <div class="flex-1 bg-white relative">
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
