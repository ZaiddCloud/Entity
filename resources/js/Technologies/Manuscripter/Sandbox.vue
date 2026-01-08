<script setup>
import { onMounted, ref } from 'vue'
import ManuscriptViewer from './ManuscriptViewer.vue'

// Mock Data for Sandbox
const mockResource = ref({
    title: 'مخطوطة كتاب الشفاء',
    // We can use a placeholder image or a real one if available.
    // Ensure this path is valid or handled gracefully.
    url: 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c9/Avicenna_Canon_of_Medicine.jpg/640px-Avicenna_Canon_of_Medicine.jpg'
})

const mockCurrentNode = ref({
    _id: 1,
    title: 'الصفحة الأولى',
    coordinates: { x: 100, y: 100, w: 200, h: 50 }, // Mock coordinates if needed
    page: 1
})

</script>

<template>
    <div class="min-h-screen bg-stone-900 p-8 font-ui text-white" dir="rtl">
        <div class="max-w-6xl mx-auto h-[90vh] flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-amber-500">📜 مختبر المخطوطات (Manuscripter Sandbox)</h1>
                    <p class="text-gray-400 text-sm">بيئة تطوير معزولة لعارض المخطوطات.</p>
                </div>
            </div>

            <!-- The Manuscripter Assembly -->
            <div class="flex-1 bg-black/50 rounded-xl shadow-2xl border border-stone-700 overflow-hidden relative">
                <ManuscriptViewer 
                    :resource="mockResource"
                    :current-node="mockCurrentNode"
                />
            </div>
        </div>
    </div>
</template>

<style>
.font-ui {
    font-family: 'Inter', 'Noto Sans Arabic', sans-serif;
}
</style>
