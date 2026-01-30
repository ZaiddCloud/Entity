<script setup>
import { Pin, PinOff, Minus, Square, X, ChevronDown, Maximize2, Minimize2, StretchHorizontal } from 'lucide-vue-next';

const props = defineProps({
    title: { type: String, default: '' },
    isDocked: Boolean,
    isIntegrated: Boolean,
    isCollapsed: Boolean,
    sizeMode: { type: String, default: 'standard' }
});

const emit = defineEmits(['toggle-dock', 'cycle-size', 'close', 'start-drag', 'toggle-collapse']);
</script>

<template>
    <div 
        dir="rtl" 
        class="header h-[30px] bg-[#1f1f1f] flex items-center justify-between px-2 cursor-grab select-none border-b border-[#2a2a2a] shrink-0 shadow-md relative z-[100]"
        :class="{'cursor-default': isDocked || isIntegrated}"
        @mousedown="(e) => emit('start-drag', e)"
    >
        <!-- Brand & Title (Right in RTL) -->
        <div class="flex items-center" dir="ltr">
            <div class="pot-logo ml-2 text-[#aaa] hover:text-white transition-colors cursor-pointer flex items-center gap-1 text-[11px] font-bold">
                EntPlayer <ChevronDown class="w-3 h-3" />
            </div>
            <span class="file-info text-yellow-500 opacity-80 text-[11px] mx-2 font-bold">MP3</span>
            <span class="border-l border-gray-700 h-3 mx-1"></span>
            <div class="track-title text-[#aaaaaa] text-[11px] max-w-[200px] truncate ml-2">
                {{ title }}
            </div>
        </div>

        <!-- Window Controls (Left in RTL) -->
        <div class="header-controls flex items-center h-full">
            <!-- Dock/Float Toggle -->
            <div 
                class="win-btn w-7 h-full flex items-center justify-center hover:bg-[#333] cursor-pointer transition-colors" 
                :title="(isDocked || isIntegrated) ? 'إلغاء التثبيت (تعويم)' : 'تثبيت'" 
                @click.stop="$emit('toggle-dock')"
            >
                <PinOff 
                    v-if="!isDocked && !isIntegrated"
                    class="w-3 h-3 text-[#aaaaaa]" 
                />
                <Pin 
                    v-else
                    class="w-3 h-3 text-yellow-500" 
                />
            </div>
            
            <div 
                class="win-btn w-7 h-full flex items-center justify-center hover:bg-[#333] cursor-pointer" 
                :title="isCollapsed ? 'توسيع' : 'تصغير'"
                @click.stop="$emit('toggle-collapse')"
            >
                <Minus class="w-3 h-3 text-[#aaaaaa]" />
            </div>
            
            <div 
                class="win-btn w-7 h-full flex items-center justify-center hover:bg-[#333] cursor-pointer" 
                :title="sizeMode === 'mini' ? 'الحجم الصغير' : (sizeMode === 'standard' ? 'الحجم القياسي' : (sizeMode === 'theater' ? 'حجم المسرح' : 'الملء الكامل'))"
                @click.stop="$emit('cycle-size')"
            >
                <component 
                    :is="sizeMode === 'mini' ? Minimize2 : (sizeMode === 'standard' ? Square : (sizeMode === 'theater' ? StretchHorizontal : Maximize2))" 
                    class="w-3 h-3 text-[#aaaaaa]" 
                    :class="sizeMode !== 'standard' ? 'text-yellow-500' : ''" 
                />
            </div>
            
            <div 
                class="win-btn close w-7 h-full flex items-center justify-center hover:bg-[#d00] hover:text-white cursor-pointer group" 
                @click.stop="$emit('close')" 
                title="Close"
            >
                <X class="w-3 h-3 text-[#aaaaaa] group-hover:text-white" />
            </div>
        </div>
    </div>
</template>
