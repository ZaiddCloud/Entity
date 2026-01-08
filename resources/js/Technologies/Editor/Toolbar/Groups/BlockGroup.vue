<script setup>
import ToolbarButton from '../Components/ToolbarButton.vue'
import { useTiptapStore } from '@/Technologies/Editor/Core/TiptapStore'

const store = useTiptapStore()
</script>

<template>
    <div class="flex items-center gap-0.5">
        <ToolbarButton 
            icon="ri-double-quotes-l" 
            title="اقتباس (Blockquote)" 
            :active="store.isActive('blockquote')" 
            @click="store.executeCommand('blockquote')" 
        />
        <ToolbarButton 
            icon="ri-code-box-line" 
            title="كتلة كود (Code Block)" 
            :active="store.isActive('codeBlock')" 
            @click="store.executeCommand('codeBlock')" 
        />
        <ToolbarButton 
            icon="ri-separator" 
            title="فاصل أفقي (Horizontal Rule)" 
            @click="store.executeCommand('horizontalRule')" 
        />
    </div>
</template>
