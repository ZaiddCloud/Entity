<script setup>
import PlayerClient from './PlayerClient.vue';

const props = defineProps({
    media: Object,
    type: String
});
</script>

<template>
  <div class="h-screen w-screen overflow-hidden flex flex-col">
    <!-- Header (Kept for Standalone Sandbox experience) -->
    <!-- Header removed for immersive experience -->

    <!-- Client Wrapper -->
    <div class="flex-1 overflow-hidden">
      <PlayerClient 
        :media="props.media"
        :type="props.type"
      />
    </div>
  </div>
</template>
