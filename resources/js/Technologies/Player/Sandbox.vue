<script setup>
import PlayerClient from './PlayerClient.vue';

const props = defineProps({
    media: Object,
    type: String
});
</script>

<template>
  <div class="h-screen w-screen bg-black overflow-hidden flex flex-col">
    <!-- Header (Kept for Standalone Sandbox experience) -->
    <div class="h-14 bg-gray-900 border-b border-gray-800 flex items-center px-6 justify-between shrink-0">
      <h1 class="text-xl font-bold text-white tracking-wide">
        Entity Media Studio
      </h1>
      <div class="text-xs text-gray-500 font-mono">
        DEV MODE
      </div>
    </div>

    <!-- Client Wrapper -->
    <div class="flex-1 overflow-hidden">
      <PlayerClient 
        :media="props.media"
        :type="props.type"
      />
    </div>
  </div>
</template>
