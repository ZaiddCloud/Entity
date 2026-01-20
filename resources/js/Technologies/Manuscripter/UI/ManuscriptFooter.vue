<script setup>
import { useManuscriptStore } from '@/Technologies/Store/ManuscriptStore'

const store = useManuscriptStore()
</script>

<template>
    <footer class="absolute bottom-0 left-0 right-0 z-10 h-12 w-full bg-gradient-to-t from-black/80 to-transparent flex items-center px-4 gap-4 transition-all">
        <!-- Filmstrip -->
        <div
            class="flex-1 flex gap-1.5 md:gap-2 overflow-x-auto items-center custom-scrollbar h-full py-0.5 justify-start md:justify-center"
            dir="ltr"
        >
            <div
                v-for="i in store.totalPages"
                :key="i" 
                class="w-7 md:w-8 h-9 md:h-10 rounded-sm shadow-sm cursor-pointer transition-all hover:-translate-y-0.5 flex flex-shrink-0 items-center justify-center text-[9px] font-bold border border-white/10"
                :class="[
                    i === store.shotNumber 
                    ? 'bg-blue-600 text-white ring-1 ring-blue-400 ring-offset-1 ring-offset-black' 
                    : 'bg-white/5 text-stone-400 hover:bg-white/10 hover:text-stone-200'
                ]"
                @click="store.shotNumber = i"
            >
                {{ i }}
            </div>
        </div>

        <!-- Controls & Info Group -->
        <div class="flex items-center gap-3 border-l border-white/10 pl-3 shrink-0 h-8">
            <!-- Zoom Controls -->
            <div class="flex items-center gap-1 bg-white/5 rounded-full px-1.5 py-0.5 border border-white/5 backdrop-blur-sm">
                <button
                    class="w-5 h-5 flex items-center justify-center rounded-full hover:bg-white/10 text-stone-400 hover:text-stone-200 transition-colors"
                    title="تصغير"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M5 12h14" /></svg>
                </button>
                <span class="text-[10px] font-bold text-stone-300 w-8 text-center font-mono">100%</span>
                <button
                    class="w-5 h-5 flex items-center justify-center rounded-full hover:bg-white/10 text-stone-400 hover:text-stone-200 transition-colors"
                    title="تكبير"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M5 12h14" /><path d="M12 5v14" /></svg>
                </button>
            </div>

            <!-- Page Number -->
            <div class="flex flex-col items-end leading-none gap-0.5">
                <span class="text-[9px] font-bold text-stone-500 uppercase tracking-wider">لقطة</span>
                <span class="font-mono text-xs font-bold text-stone-300">#{{ store.shotNumber }}</span>
            </div>
        </div>
    </footer>
</template>
