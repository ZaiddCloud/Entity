<script setup>
import { Head } from '@inertiajs/vue3';
import ReaderClient from '@/Technologies/Reader/ReaderClient.vue';

const props = defineProps({
    type: String,
    entity: Object,
    content: Object,
    html_content: String,
    activeSlug: String,
    hierarchy: Array,
    readingPosition: Object,
    title: String,
});
</script>

<template>
    <Head :title="title" />
    
    <ReaderClient 
        v-bind="props"
    />
</template>
