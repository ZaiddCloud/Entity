<script setup>
import EditorClient from '../../Editor/EditorClient.vue'

const props = defineProps({
    initialContent: { type: String, default: '' },
    mediaEntity: { type: Object, default: null }, // NEW
    type: { type: String, default: 'manuscript' } // NEW
})

const emit = defineEmits(['navigate', 'navigate-full']);
</script>

<template>
  <div class="w-full h-full bg-white relative">
    <EditorClient 
        :initial-content="props.initialContent" 
        :media-entity="props.mediaEntity"
        :type="props.type"
        @navigate="(id) => $emit('navigate', id)"
        @navigate-full="$emit('navigate-full')"
    />
  </div>
</template>
