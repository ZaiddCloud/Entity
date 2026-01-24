<script setup>
import EditorClient from '../../Editor/EditorClient.vue'

const props = defineProps({
    initialContent: { type: String, default: '' },
    mediaEntity: { type: Object, default: null }, // NEW
    type: { type: String, default: 'manuscript' } // NEW
})
</script>

<template>
  <div class="w-full h-full bg-white relative">
    <EditorClient 
        :initial-content="props.initialContent" 
        :media-entity="props.mediaEntity"
        :type="props.type"
    />
  </div>
</template>
