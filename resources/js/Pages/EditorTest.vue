<script setup>
import EditorClient from '../Technologies/Editor/EditorClient.vue'

const props = defineProps({
    book: {
        type: Object,
        required: true
    },
    child: {
        type: Object,
        required: true
    }
})
</script>

<template>
  <EditorClient
    :initial-content="child.content"
    :media-entity="book"
    type="book"
  />
</template>
