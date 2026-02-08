<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import GlobalSyncObserver from '@/Core/UI/GlobalSyncObserver.vue';
import QuickSearch from '@/Core/UI/QuickSearch.vue';

const page = usePage();

const shouldShowSync = computed(() => {
    // Hide on Auth pages (Login, Register)
    return !page.component.startsWith('Auth/');
});

const shouldShowSearch = computed(() => {
    // Hide on Auth pages or potential Error pages
    return !page.component.startsWith('Auth/') && 
           !page.component.startsWith('Error/');
});
</script>

<template>
    <GlobalSyncObserver v-if="shouldShowSync" />
    <QuickSearch v-if="shouldShowSearch" />
    <slot />
</template>
