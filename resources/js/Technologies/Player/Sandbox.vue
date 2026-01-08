<script setup>
import { onMounted } from 'vue'
import MediaPlayer from './MediaPlayer.vue'
import { useMediaStore } from '@/Technologies/Editor/Core/MediaStore'

const store = useMediaStore()

// Mock logic for player sandbox
onMounted(() => {
    store.setMode('audio')
    // Mocking some hierarchy data for the playlist
    const mockHierarchy = [
        { _id: 1, title: 'المقدمة', type: 'chapter', audio_url: '/storage/samples/intro.mp3' },
        { _id: 2, title: 'الفصل الأول', type: 'chapter', audio_url: '/storage/samples/ch1.mp3' },
    ]
    // In a real scenario, we'd load this into the store or pass it as props
})
</script>

<template>
    <div class="min-h-screen bg-gray-900 p-8 font-ui text-white" dir="rtl">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-blue-400">🎧 مختبر المشغل (Player Sandbox)</h1>
                    <p class="text-gray-400 text-sm">بيئة تطوير معزولة لمشغل الوسائط.</p>
                </div>
                <div class="flex gap-2">
                    <button @click="store.setMode('audio')" class="px-4 py-2 rounded text-sm transition-colors" :class="store.mode === 'audio' ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-300'">صوت</button>
                    <button @click="store.setMode('video')" class="px-4 py-2 rounded text-sm transition-colors" :class="store.mode === 'video' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-300'">فيديو</button>
                </div>
            </div>

            <!-- The Player Assembly -->
            <div class="bg-black/50 rounded-xl shadow-2xl border border-gray-800 overflow-hidden min-h-[400px] flex items-center justify-center relative">
                <div class="absolute inset-0 flex items-center justify-center opacity-10 pointer-events-none">
                    <span class="text-6xl">🎵</span>
                </div>
                
                <!-- We pass mock props for now since the component expects them -->
                <MediaPlayer 
                    :mode="store.mode"
                    :resource="{ title: 'مقطع تجريبي', url: '#' }"
                    :hierarchy="[]"
                />
            </div>

            <div class="mt-8 p-4 bg-gray-800 rounded-lg">
                <h3 class="font-bold text-gray-300 mb-2">حالة المشغل (State):</h3>
                <pre class="bg-black/30 p-4 rounded text-xs text-green-400 font-mono">{{ store.$state }}</pre>
            </div>
        </div>
    </div>
</template>

<style>
.font-ui {
    font-family: 'Inter', 'Noto Sans Arabic', sans-serif;
}
</style>
