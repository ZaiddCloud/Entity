<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import ResourceNavigator from '@/Technologies/Common/ResourceNavigator.vue'

defineOptions({
  name: 'MediaPlayer'
})

const props = defineProps(['resource', 'mode', 'hierarchy'])

const versions = computed(() => {
    if (!props.resource?.versions) return []
    return props.resource.versions
})

const activeVersionIndex = ref(0)
const activeVersion = computed(() => versions.value[activeVersionIndex.value] || {})

// Media State
const currentTime = ref(0)
const duration = ref(0)
const isPlaying = ref(false)
const playbackSpeed = ref(1)
const speeds = [0.5, 0.75, 1, 1.25, 1.5, 2]
const mediaRef = ref(null)

const togglePlay = () => {
    const el = mediaRef.value || document.querySelector('video')
    if (el) {
        if (el.paused) el.play()
        else el.pause()
    }
}

const seekMedia = (e) => {
    const el = mediaRef.value || document.querySelector('video')
    if (!el || !duration.value) return
    const rect = e.currentTarget.getBoundingClientRect()
    const percent = Math.min(Math.max((e.clientX - rect.left) / rect.width, 0), 1)
    el.currentTime = percent * duration.value
}

const skip = (seconds) => {
    const el = mediaRef.value || document.querySelector('video')
    if (el) el.currentTime += seconds
}

const updateSpeed = () => {
    const el = mediaRef.value || document.querySelector('video')
    if (el) el.playbackRate = playbackSpeed.value
}

const formatTime = (s) => {
    if (!s) return '00:00'
    const m = Math.floor(s / 60)
    const sec = Math.floor(s % 60)
    return `${m}:${sec.toString().padStart(2, '0')}`
}

const gotoSegment = (segment) => {
    router.visit(route('editor.show', { type: 'audio', slug: segment.slug }))
}

const insertTimestamp = () => {
    // Logic to insert timestamp into editor would go here
    console.log('Insert timestamp:', formatTime(currentTime.value))
}
</script>

<template>
    <div class="h-full bg-gray-50 flex flex-col border-l border-gray-200 font-ui overflow-hidden">
        
        <!-- 1. Fixed Header Area (App Toolbar) -->
        <!-- Glassmorphism Style applied here -->
        <div class="glass-header border-b border-gray-200 p-3 flex items-center justify-between shrink-0 z-20 shadow-sm min-h-[50px]">
            <div class="flex items-center gap-3">
                <span class="text-[10px] uppercase tracking-wider text-gray-500 font-bold">Media Tools</span>
                <!-- All Resources Navigation -->
                <ResourceNavigator :type="mode" :current-id="resource?.id" />
            </div>


        </div>


        <!-- Recording Tabs -->
        <div v-if="versions.length > 1" class="flex items-center bg-white border-b border-gray-200 px-2 shrink-0">
            <button 
                v-for="(version, index) in versions" 
                :key="index"
                @click="activeVersionIndex = index"
                class="px-4 py-2 text-[10px] font-bold transition-all border-b-2 whitespace-nowrap uppercase tracking-wider"
                :class="[
                    activeVersionIndex === index 
                        ? 'border-blue-500 text-blue-600 bg-blue-50' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                ]"
            >
                {{ version.title }}
            </button>
        </div>


        <!-- 2. Scrollable Content Area -->
        <div class="flex-1 flex flex-col p-6 overflow-y-auto">
            
            <!-- Main Display (Video/Audio) -->
            <div class="relative bg-black/20 border border-slate-700/50 rounded-xl overflow-hidden mb-6 group">
                 <div class="relative min-h-[160px] flex flex-col justify-center bg-[#0a0f1e]">
                    <!-- Video Mode -->
                    <div v-if="mode === 'video' && activeVersion?.url" class="relative w-full aspect-video bg-black">
                        <video 
                            class="w-full h-full object-contain"
                            :src="activeVersion.url"
                            @timeupdate="currentTime = $event.target.currentTime"
                            @durationchange="duration = $event.target.duration"
                            @play="isPlaying = true"
                            @pause="isPlaying = false"
                            controlsList="nodownload"
                        ></video> 
                    </div>

                    <!-- Audio Mode -->
                    <div v-else class="w-full px-4 py-8">
                         <!-- Media Info -->
                        <div class="flex items-center gap-4 mb-6 opacity-80">
                            <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center text-blue-400">
                                <i class="fas fa-waveform"></i>
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <p class="text-xs text-white font-medium truncate">
                                    {{ activeVersion.title || 'ملف التسجيل' }}
                                </p>
                                <p class="text-[10px] text-slate-500 font-mono truncate">{{ activeVersion.url || 'لا يوجد مصدر' }}</p>
                            </div>
                        </div>

                        <!-- Waveform Bars -->
                        <div class="flex items-end gap-[2px] h-16 opacity-60">
                            <div 
                                v-for="i in 40" 
                                :key="i"
                                class="flex-1 bg-slate-700 rounded-t-sm transition-all duration-300"
                                :style="{ height: Math.random() * 80 + 20 + '%' }"
                                :class="{ 'bg-blue-500': (i/40) < (currentTime/duration) }"
                            ></div>
                        </div>
                    </div>

                    <!-- Hidden Audio Element -->
                    <audio 
                        v-if="mode === 'audio' && activeVersion?.url" 
                        ref="mediaRef"
                        :src="activeVersion.url"
                        @timeupdate="currentTime = $event.target.currentTime"
                        @durationchange="duration = $event.target.duration"
                        @play="isPlaying = true"
                        @pause="isPlaying = false"
                        class="hidden"
                    ></audio>
                </div>

                <!-- PLAYER CONTROLS (Moved back here) -->
                <div class="bg-[#0f172a] border-t border-slate-800 p-3 flex flex-col gap-3">
                    
                    <!-- Progress Bar -->
                    <div class="space-y-1 group/progress cursor-pointer" @click="seekMedia">
                       <div class="h-1 bg-slate-700 rounded-full relative overflow-hidden">
                            <div 
                                class="absolute left-0 top-0 bottom-0 bg-blue-500 transition-all duration-100" 
                                :style="{ width: (currentTime / duration) * 100 + '%' }"
                            ></div>
                        </div>
                    </div>

                    <!-- Toolbar Controls Row -->
                    <div class="flex items-center justify-between">
                        
                        <!-- Left: Time & Info -->
                        <div class="flex items-center gap-3 text-[10px] font-mono text-slate-400">
                            <span>{{ formatTime(currentTime) }}</span>
                            <span class="opacity-50">/</span>
                            <span>{{ formatTime(duration) }}</span>
                        </div>

                        <!-- Center: Primary Playback -->
                        <div class="flex items-center gap-4">
                            <button @click="skip(-5)" class="text-slate-500 hover:text-white transition-colors p-1" title="-5s">
                                <i class="fas fa-undo-alt"></i>
                            </button>
                            
                            <button 
                                @click="togglePlay"
                                class="w-8 h-8 rounded-full bg-white text-black hover:bg-blue-500 hover:text-white flex items-center justify-center transition-all shadow-lg"
                            >
                                <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'" class="text-xs"></i>
                            </button>

                            <button @click="skip(5)" class="text-slate-500 hover:text-white transition-colors p-1" title="+5s">
                                <i class="fas fa-redo-alt"></i>
                            </button>
                        </div>

                        <!-- Right: Secondary Tools -->
                        <div class="flex items-center gap-3">
                            <div class="relative group">
                                <button class="text-slate-500 hover:text-white text-[10px] font-bold bg-slate-800 px-2 py-1 rounded">
                                    {{ playbackSpeed }}x
                                </button>
                                <select 
                                    v-model="playbackSpeed" 
                                    @change="updateSpeed" 
                                    class="absolute inset-0 opacity-0 cursor-pointer"
                                >
                                    <option v-for="s in speeds" :key="s" :value="s">{{ s }}x</option>
                                </select>
                            </div>

                            <button @click="insertTimestamp" class="text-slate-500 hover:text-blue-400" title="Timestamp">
                                <i class="fas fa-thumbtack"></i>
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Segments Index (Hierarchy) -->
            <div v-if="hierarchy && hierarchy.length > 0" class="mt-4 p-4 border-t border-slate-800">
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
.glass-header {
    background: rgba(255, 255, 255, 0.98) !important;
    backdrop-filter: blur(12px) !important;
}
select {
    appearance: none;
    background-image: none;
}
</style>
```
