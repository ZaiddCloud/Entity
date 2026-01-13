<script setup>
import EditorClient from '../../Editor/EditorClient.vue'

const props = defineProps({
    initialContent: {
        type: String,
        default: ''
    }
})
</script>

<template>
  <div class="w-full h-full bg-white relative">
    <EditorClient :initial-content="props.initialContent" />
  </div>
</template>
