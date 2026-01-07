<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

defineOptions({
  name: 'MediaPlayer'
})

const props = defineProps(['resource', 'mode', 'hierarchy'])

const isPlaying = ref(false)
const currentTime = ref(0)
const duration = ref(360) // 6:00
const playbackSpeed = ref(1)
const activeVersionIndex = ref(0)
const mediaRef = ref(null)

const versions = computed(() => {
    if (props.resource?.versions && Array.isArray(props.resource.versions)) {
        return props.resource.versions
    }
    if (props.resource?.url) {
        return [{ title: 'الملف الأساسي', url: props.resource.url }]
    }
    return []
})

const activeVersion = computed(() => versions.value[activeVersionIndex.value] || {})

const togglePlay = () => {
    if (!mediaRef.value) return
    if (isPlaying.value) {
        mediaRef.value.pause()
    } else {
        mediaRef.value.play()
    }
}

const skip = (seconds) => {
    if (!mediaRef.value) return
    mediaRef.value.currentTime += seconds
}

const seekMedia = (event) => {
    if (!mediaRef.value) return
    const rect = event.currentTarget.getBoundingClientRect()
    const x = event.clientX - rect.left
    const percent = x / rect.width
    mediaRef.value.currentTime = percent * duration.value
}

const updateSpeed = () => {
    if (!mediaRef.value) return
    mediaRef.value.playbackRate = playbackSpeed.value
}

const insertTimestamp = () => {
    // This will be implemented when connected to the content editor
    alert(`Timestamp inserted: [${formatTime(currentTime.value)}]`)
}

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60)
    const secs = Math.floor(seconds % 60)
    return `${mins}:${secs.toString().padStart(2, '0')}`
}

const gotoSegment = (segment) => {
    router.visit(route('editor.show', { type: props.mode, slug: segment.slug }))
}

const speeds = [0.5, 0.75, 1, 1.25, 1.5, 2]
</script>

<template>
    <div class="h-full bg-[#0f172a] text-slate-300 flex flex-col border-l border-slate-800 font-ui overflow-hidden">
        <!-- Recording Tabs (Conditional) -->
        <div v-if="versions.length > 1" class="flex items-center bg-[#1e293b] border-b border-slate-800/50 px-2 shrink-0">
            <button 
                v-for="(version, index) in versions" 
                :key="index"
                @click="activeVersionIndex = index"
                class="px-4 py-2 text-[10px] font-bold transition-all border-b-2 whitespace-nowrap uppercase tracking-wider"
                :class="[
                    activeVersionIndex === index 
                        ? 'border-blue-500 text-blue-400 bg-blue-500/5' 
                        : 'border-transparent text-slate-500 hover:text-slate-300 hover:bg-slate-800/50'
                ]"
            >
                {{ version.title }}
            </button>
        </div>

        <div class="flex-1 flex flex-col p-6 overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8 shrink-0">
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Transcription Hub</span>
                    <h3 class="text-white font-medium text-sm">{{ mode === 'audio' ? 'مشغل صوتي' : 'مشغل مرئي' }}</h3>
                </div>
                <div class="w-2 h-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]"></div>
            </div>

            <!-- Media Info Card -->
            <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700/50 mb-6 group cursor-pointer hover:bg-slate-800 transition-colors shrink-0">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center text-blue-400">
                        <i v-if="mode === 'audio'" class="fas fa-waveform"></i>
                        <i v-else class="fas fa-video"></i>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-xs text-white font-medium truncate mb-1">
                            {{ activeVersion.title || 'ملف التسجيل' }}
                        </p>
                        <p class="text-[10px] text-slate-500 truncate">{{ activeVersion.url || 'لا يوجد مصدر' }}</p>
                    </div>
                </div>
            </div>

            <!-- Waveform Skeleton (Visual) -->
            <div class="flex items-end gap-[2px] h-12 mb-4 bg-slate-900/50 rounded-lg px-2 py-1 border border-slate-800">
                <div 
                    v-for="i in 30" 
                    :key="i"
                    class="flex-1 bg-slate-700 rounded-t-sm"
                    :style="{ height: Math.random() * 100 + '%' }"
                    :class="{ 'bg-blue-500/50': i < 15 }"
                ></div>
            </div>

            <!-- Controls -->
            <div class="space-y-6">
                <!-- Actual Media Element -->
                <div class="hidden">
                    <audio 
                        v-if="mode === 'audio' && activeVersion?.url" 
                        ref="mediaRef"
                        :src="activeVersion.url"
                        @timeupdate="currentTime = $event.target.currentTime"
                        @durationchange="duration = $event.target.duration"
                        @play="isPlaying = true"
                        @pause="isPlaying = false"
                    ></audio>
                    <video 
                        v-if="mode === 'video' && activeVersion?.url" 
                        ref="mediaRef"
                        :src="activeVersion.url"
                        @timeupdate="currentTime = $event.target.currentTime"
                        @durationchange="duration = $event.target.duration"
                        @play="isPlaying = true"
                        @pause="isPlaying = false"
                    ></video>
                </div>

                <!-- Video Display for Video Mode -->
                <div v-if="mode === 'video' && activeVersion?.url" class="aspect-video bg-black rounded-lg overflow-hidden mb-4 border border-slate-700 shadow-2xl">
                    <video 
                        class="w-full h-full"
                        :src="activeVersion.url"
                        @timeupdate="currentTime = $event.target.currentTime"
                        @durationchange="duration = $event.target.duration"
                        @play="isPlaying = true"
                        @pause="isPlaying = false"
                        controls
                    ></video>
                </div>

                <!-- Progress Bar -->
                <div class="space-y-2">
                    <div 
                        class="h-1.5 bg-slate-800 rounded-full relative cursor-pointer overflow-hidden group"
                        @click="seekMedia"
                    >
                        <div 
                            class="absolute left-0 top-0 bottom-0 bg-blue-500 transition-all duration-100" 
                            :style="{ width: (currentTime / duration) * 100 + '%' }"
                        ></div>
                        <div 
                            class="absolute top-0 bottom-0 w-0.5 bg-white opacity-0 group-hover:opacity-100 transition-opacity"
                            :style="{ left: (currentTime / duration) * 100 + '%' }"
                        ></div>
                    </div>
                    <div class="flex justify-between text-[10px] font-mono text-slate-500 uppercase tracking-tighter">
                        <span>{{ formatTime(currentTime) }}</span>
                        <span>{{ formatTime(duration) }}</span>
                    </div>
                </div>

                <!-- Playback Actions -->
                <div class="flex items-center justify-center gap-6">
                    <button @click="skip(-5)" class="text-slate-500 hover:text-white transition-colors" title="إرجاع 5 ثواني">
                        <i class="fas fa-undo-alt text-lg"></i>
                    </button>
                    <button 
                        @click="togglePlay"
                        class="w-12 h-12 rounded-full bg-blue-600 hover:bg-blue-500 text-white flex items-center justify-center shadow-lg transition-all active:scale-95"
                    >
                        <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'" class="text-xl"></i>
                    </button>
                    <button @click="skip(5)" class="text-slate-500 hover:text-white transition-colors" title="تقديم 5 ثواني">
                        <i class="fas fa-redo-alt text-lg"></i>
                    </button>
                </div>

                <!-- Speed & Volume -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-800/30 rounded-lg p-2 border border-slate-700/30">
                        <p class="text-[9px] text-slate-500 mb-1 uppercase font-bold text-center">السرعة</p>
                        <select v-model="playbackSpeed" @change="updateSpeed" class="w-full bg-transparent text-xs text-white border-none focus:ring-0 cursor-pointer text-center">
                            <option v-for="s in speeds" :key="s" :value="s" class="bg-slate-800">{{ s }}x</option>
                        </select>
                    </div>
                    <div class="bg-slate-800/30 rounded-lg p-2 border border-slate-700/30 flex flex-col items-center justify-center">
                        <p class="text-[9px] text-slate-500 mb-1 uppercase font-bold text-center">الختم الزمني</p>
                        <button @click="insertTimestamp" class="text-[10px] text-blue-400 hover:text-blue-300 font-bold">إدراج [{{ formatTime(currentTime) }}]</button>
                    </div>
                </div>
            </div>

            <!-- Segments Index (Hierarchy) -->
            <div v-if="hierarchy && hierarchy.length > 0" class="mt-10 p-4 border-t border-slate-800">
                <h4 class="text-[10px] text-slate-500 uppercase font-bold mb-3">فهرس المقاطع</h4>
                <div class="space-y-1">
                    <button 
                        v-for="item in hierarchy" 
                        :key="item._id"
                        @click="gotoSegment(item)"
                        class="w-full flex items-center gap-3 p-2 rounded-lg text-left transition-all group"
                        :class="[
                            $page.url.includes(item.slug) 
                                ? 'bg-blue-500/10 border border-blue-500/20 text-blue-400' 
                                : 'hover:bg-slate-800/50 text-slate-400 hover:text-white border border-transparent'
                        ]"
                    >
                        <div class="w-6 h-6 rounded flex items-center justify-center bg-slate-900 text-[10px]">
                            {{ item.order }}
                        </div>
                        <span class="text-[10px] font-medium truncate flex-1">{{ item.title }}</span>
                        <i v-if="$page.url.includes(item.slug)" class="fas fa-play text-[8px] animate-pulse"></i>
                    </button>
                </div>
            </div>

            <!-- Transcribe Tips -->
            <div class="mt-10 p-4 border-t border-slate-800">
                <h4 class="text-[10px] text-slate-500 uppercase font-bold mb-3">اختصارات التفريغ</h4>
                <ul class="text-[10px] space-y-2 text-slate-400">
                    <li class="flex justify-between"><span>تشغيل/إيقاف</span> <kbd class="bg-slate-800 px-1 rounded">Shift + Space</kbd></li>
                    <li class="flex justify-between"><span>إدراج زمن</span> <kbd class="bg-slate-800 px-1 rounded">Ctrl + T</kbd></li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-ui {
    font-family: 'Inter', 'Noto Sans Arabic', sans-serif;
}
select {
    appearance: none;
    background-image: none;
}
</style>
