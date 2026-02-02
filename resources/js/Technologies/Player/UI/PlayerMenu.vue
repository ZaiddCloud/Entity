<script setup>
import { Keyboard, RotateCcw, Info, Monitor, Layers } from 'lucide-vue-next';
import { useMediaStore } from '@/Technologies/Store/MediaStore';

const props = defineProps({
    isOpen: Boolean
});

const emit = defineEmits(['close', 'show-shortcuts']);
const store = useMediaStore();

const handleReset = () => {
    store.resetLayout();
    emit('close');
};
</script>

<template>
    <div v-if="isOpen" class="player-menu-container absolute left-0 top-[30px] z-[200]">
        <!-- Backdrop -->
        <div class="fixed inset-0 z-[-1]" @click="$emit('close')"></div>

        <!-- Menu Card -->
        <div class="menu-card bg-[#1a1a1a] border border-white/5 rounded-lg shadow-2xl py-2 min-w-[200px] overflow-hidden">
            <!-- Section: System -->
            <div class="px-3 py-1.5 text-[10px] text-gray-500 font-bold uppercase tracking-wider flex items-center gap-2">
                <Monitor class="w-3 h-3" /> أدوات التحكم
            </div>
            
            <button @click="handleReset" class="menu-item transition-all active:scale-95">
                <RotateCcw class="w-3.5 h-3.5 text-yellow-500" />
                <span class="flex-1 text-right">إعادة ضبط الموضع</span>
                <span class="text-[9px] text-gray-500 font-mono">CMD+R</span>
            </button>

            <!-- Section: Help -->
            <div class="h-px bg-white/5 my-1 mx-2"></div>
            <div class="px-3 py-1.5 text-[10px] text-gray-500 font-bold uppercase tracking-wider flex items-center gap-2">
                <Layers class="w-3 h-3" /> المساعدة والمعلومات
            </div>

            <button @click="$emit('show-shortcuts')" class="menu-item transition-all">
                <Keyboard class="w-3.5 h-3.5 text-blue-400" />
                <span class="flex-1 text-right">دليل الاختصارات</span>
            </button>

            <div class="menu-item group cursor-default">
                <Info class="w-3.5 h-3.5 text-emerald-400" />
                <span class="flex-1 text-right">EntPlayer v2.1</span>
                <span class="text-[9px] text-emerald-500/50">نشط</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.player-menu-container {
    animation: menu-slide 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    transform-origin: top left;
}

@keyframes menu-slide {
    from { opacity: 0; transform: translateY(-10px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.menu-card {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.4);
}

.menu-item {
    width: 100%;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 11px;
    color: #ccc;
    text-align: right;
    border: none;
    background: transparent;
    cursor: pointer;
}

.menu-item:hover {
    background: rgba(255, 255, 255, 0.05);
    color: white;
}

.menu-item:hover svg {
    filter: drop-shadow(0 0 5px currentColor);
}
</style>
