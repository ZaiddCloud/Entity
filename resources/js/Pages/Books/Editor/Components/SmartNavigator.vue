<template>
    <div class="smart-navigator h-12 glass-effect border-t border-white/20 dark:border-white/10 flex items-center px-6 justify-between animate-in slide-in-from-bottom-full duration-500">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 group cursor-pointer" @click="isExpanded = !isExpanded">
                <div class="p-1.5 rounded-lg bg-primary/10 text-primary group-hover:bg-primary/20 transition-colors">
                    <NavigationIcon class="w-4 h-4" />
                </div>
                <span class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest group-hover:text-primary transition-colors">
                    Hierarchy Navigator
                </span>
            </div>
            
            <div class="h-4 w-px bg-slate-200 dark:bg-slate-800"></div>
            
            <!-- Quick Link / Jump by Digit -->
            <div class="flex items-center gap-2">
                <HashIcon class="w-3 h-3 text-slate-400" />
                <input 
                    type="text" 
                    placeholder="Jump to..." 
                    class="bg-transparent border-none p-0 text-xs focus:ring-0 w-20 text-slate-600 dark:text-slate-400 font-medium"
                />
            </div>
        </div>

        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2 text-[10px] text-slate-400 font-bold uppercase tracking-tighter">
                <div class="flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">
                    <kbd class="font-sans">Ctrl</kbd> + <kbd class="font-sans">K</kbd>
                </div>
                <span>Quick Insertion</span>
            </div>
            
            <div class="flex items-center gap-2 text-[10px] text-slate-400 font-bold uppercase tracking-tighter">
                <div class="flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">
                    <kbd class="font-sans">/</kbd>
                </div>
                <span>Slash Menu</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { NavigationIcon, HashIcon } from 'lucide-vue-next'

const isExpanded = ref(false)
</script>

<style scoped>
.glass-effect {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
}
.dark .glass-effect {
    background: rgba(15, 23, 42, 0.8);
}
</style>
