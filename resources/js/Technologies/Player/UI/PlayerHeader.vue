<script setup>
import { Sidebar, Pin, Minus, Square, X, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
    title: { type: String, default: '' },
    isDocked: Boolean,
    isIntegrated: Boolean,
    isMaximized: Boolean
});

const emit = defineEmits(['toggle-dock', 'toggle-max', 'close', 'start-drag']);
</script>

<template>
    <div 
        dir="rtl" 
        class="header h-[30px] bg-[#1f1f1f] flex items-center justify-between px-2 cursor-grab select-none border-b border-[#2a2a2a]"
        :class="{'cursor-default': isDocked || isIntegrated}"
        @mousedown="(e) => emit('start-drag', e)"
    >
        <!-- Brand & Title (Right in RTL) -->
        <div class="flex items-center" dir="ltr">
            <div class="pot-logo ml-2 text-[#aaa] hover:text-white transition-colors cursor-pointer flex items-center gap-1 text-[11px] font-bold">
                PotPlayer <ChevronDown class="w-3 h-3" />
            </div>
            <span class="file-info text-yellow-500 opacity-80 text-[11px] mx-2 font-bold">MP3</span>
            <span class="border-l border-gray-700 h-3 mx-1"></span>
            <div class="track-title text-[#aaaaaa] text-[11px] max-w-[200px] truncate ml-2">
                {{ title }}
            </div>
        </div>

        <!-- Window Controls (Left in RTL) -->
        <div class="header-controls flex items-center h-full">
            <!-- Dock Toggle -->
            <div 
                class="win-btn w-7 h-full flex items-center justify-center hover:bg-[#333] cursor-pointer transition-colors" 
                :title="isIntegrated ? 'فك الدمج (عائم)' : (isDocked ? 'فك التثبيت (عائم)' : 'تثبيت جانبي')" 
                @click.stop="$emit('toggle-dock')"
            >
                <Sidebar class="w-3 h-3 text-[#aaaaaa]" :class="(isDocked || isIntegrated) ? 'text-yellow-500' : ''" />
            </div>

            <!-- Always Visible Controls -->
            <div class="win-btn w-7 h-full flex items-center justify-center hover:bg-[#333] cursor-pointer" title="Pin">
                <Pin class="w-3 h-3 text-[#aaaaaa] rotate-45" />
            </div>
            
            <div class="win-btn w-7 h-full flex items-center justify-center hover:bg-[#333] cursor-pointer" title="Minimize">
                <Minus class="w-3 h-3 text-[#aaaaaa]" />
            </div>
            
            <div 
                class="win-btn w-7 h-full flex items-center justify-center hover:bg-[#333] cursor-pointer" 
                title="Maximize"
                @click.stop="$emit('toggle-max')"
            >
                <Square class="w-3 h-3 text-[#aaaaaa]" :class="{'text-yellow-500': isMaximized}" />
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
