<script setup>
import ToolbarButton from '../Components/ToolbarButton.vue'
import { useTiptapStore } from '@/Technologies/Editor/Core/TiptapStore'

const store = useTiptapStore()
</script>

<template>
    <div class="flex items-center gap-0.5">
        <ToolbarButton 
            icon="ri-list-unordered" 
            title="قائمة نقطية" 
            :active="store.isActive('bulletList')" 
            @click="store.executeCommand('bulletList')" 
        />
        <ToolbarButton 
            icon="ri-list-ordered" 
            title="قائمة رقمية" 
            :active="store.isActive('orderedList')" 
            @click="store.executeCommand('orderedList')" 
        />
    </div>
</template>
