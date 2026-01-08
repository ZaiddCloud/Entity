<script setup>
import ToolbarButton from '../Components/ToolbarButton.vue'
import { useTiptapStore } from '@/Technologies/Editor/Core/TiptapStore'

const store = useTiptapStore()
</script>

<template>
    <div class="flex items-center gap-0.5">
        <ToolbarButton 
            icon="ri-quill-pen-line" 
            title="إدراج بيت شعر" 
            @click="store.executeCommand('insertHeritagePoetry')" 
        />
        <ToolbarButton 
            icon="ri-book-open-line" 
            title="إدراج آية قرآنية" 
            @click="store.executeCommand('insertQuranicVerse')" 
        />
        <ToolbarButton 
            icon="ri-footprint-line" 
            title="إدراج حاشية" 
            @click="store.executeCommand('insertFootnote')" 
        />
    </div>
</template>
