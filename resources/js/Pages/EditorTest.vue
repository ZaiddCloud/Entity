<script setup>
import EditorPage from './Editor/EditorPage.vue'

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
    <EditorPage :book="book" :child="child" />
</template>
