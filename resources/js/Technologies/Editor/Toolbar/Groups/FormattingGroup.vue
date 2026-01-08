<script setup>
import ToolbarButton from '../Components/ToolbarButton.vue'
import { useTiptapStore } from '@/Technologies/Editor/Core/TiptapStore'

const store = useTiptapStore()
</script>

<template>
    <div class="flex items-center gap-0.5">
        <ToolbarButton 
            icon="ri-bold" 
            title="عريض (Ctrl+B)" 
            :active="store.isActive('bold')" 
            @click="store.executeCommand('bold')" 
        />
        <ToolbarButton 
            icon="ri-italic" 
            title="مائل (Ctrl+I)" 
            :active="store.isActive('italic')" 
            @click="store.executeCommand('italic')" 
        />
        <ToolbarButton 
            icon="ri-underline" 
            title="تسطير (Ctrl+U)" 
            :active="store.isActive('underline')" 
            @click="store.executeCommand('underline')" 
        />
        <ToolbarButton 
            icon="ri-strikethrough" 
            title="شطب" 
            :active="store.isActive('strike')" 
            @click="store.executeCommand('strike')" 
        />
        <ToolbarButton 
            icon="ri-code-line" 
            title="كود (Inline)" 
            :active="store.isActive('code')" 
            @click="store.executeCommand('code')" 
        />
        <div class="w-px h-4 bg-gray-200 mx-1"></div>
        <ToolbarButton 
            icon="ri-mark-pen-line" 
            title="تمييز (Highlight)" 
            :active="store.isActive('highlight')" 
            @click="store.executeCommand('highlight')" 
        />
        <ToolbarButton 
            icon="ri-subscript-2" 
            title="منخفض (Subscript)" 
            :active="store.isActive('subscript')" 
            @click="store.executeCommand('subscript')" 
        />
        <ToolbarButton 
            icon="ri-superscript-2" 
            title="مرتفع (Superscript)" 
            :active="store.isActive('superscript')" 
            @click="store.executeCommand('superscript')" 
        />
        <div class="w-px h-4 bg-gray-200 mx-1"></div>
        <ToolbarButton 
            icon="ri-format-clear" 
            title="مسح التنسيق" 
            red
            @click="store.executeCommand('unsetAllMarks'); store.executeCommand('clearNodes')" 
        />
    </div>
</template>
