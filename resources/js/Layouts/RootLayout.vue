<script setup>
import GlobalSyncObserver from '@/Core/UI/GlobalSyncObserver.vue';
import QuickSearch from '@/Core/UI/QuickSearch.vue';
</script>

<template>
    <GlobalSyncObserver />
    <QuickSearch />
    <slot />
</template>
