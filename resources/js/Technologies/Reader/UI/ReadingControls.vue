<script setup>
import { inject } from 'vue';
import { useTheme } from '../Core/useTheme';

const store = inject('readerStore');
const themeClasses = inject('themeClasses');
const { themes, setTheme } = useTheme();

const fontSizes = [12, 14, 16, 18, 20, 24, 28, 32];

const props = defineProps({
    isOpen: Boolean
});

const emit = defineEmits(['close']);

</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="emit('close')">
        <div :class="['w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden animate-slide-up', themeClasses.bg, themeClasses.text]">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-lg italic">إعدادات القراءة</h3>
                    <button @click="emit('close')" class="p-2 hover:bg-black/5 rounded-full transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Theme Selection -->
                <div class="mb-8">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">السمة البصرية</label>
                    <div class="grid grid-cols-3 gap-3">
                        <button 
                            v-for="(theme, key) in themes" 
                            :key="key"
                            @click="setTheme(key)"
                            :class="[
                                'flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all',
                                store.theme === key ? 'border-blue-500 scale-105 shadow-md' : 'border-transparent hover:bg-black/5'
                            ]"
                        >
                            <div :class="['h-8 w-8 rounded-full border border-black/10', theme.bg]"></div>
                            <span class="text-[10px] font-bold">{{ key === 'light' ? 'فاتح' : key === 'dark' ? 'داكن' : 'قديم' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Font Size Selection -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-widest">حجم الخط</label>
                        <span class="text-xs font-mono font-bold px-2 py-1 bg-black/5 rounded-md">{{ store.fontSize }}px</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <button 
                            @click="store.setFontSize(store.fontSize - 2)"
                            class="p-3 bg-black/5 rounded-xl hover:bg-black/10 transition-colors disabled:opacity-30"
                            :disabled="store.fontSize <= 12"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                        </button>
                        
                        <div class="flex-1 h-2 bg-black/5 rounded-full overflow-hidden relative">
                             <input 
                                type="range" 
                                :min="12" 
                                :max="32" 
                                :step="2"
                                v-model.number="store.fontSize"
                                @input="store.setFontSize(store.fontSize)"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                             >
                             <div 
                                :class="['h-full transition-all duration-300', themeClasses.accent.replace('text-', 'bg-')]"
                                :style="{ width: `${((store.fontSize - 12) / (32 - 12)) * 100}%` }"
                             ></div>
                        </div>

                        <button 
                            @click="store.setFontSize(store.fontSize + 2)"
                            class="p-3 bg-black/5 rounded-xl hover:bg-black/10 transition-colors disabled:opacity-30"
                            :disabled="store.fontSize >= 32"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Display Sample -->
                <div :class="['p-4 rounded-xl border border-dashed mb-4', themeClasses.border, themeClasses.bg]">
                    <p :style="{ fontSize: `${store.fontSize}px` }" class="text-center truncate">
                        هذا مثال لنص القراءة
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-slide-up {
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
