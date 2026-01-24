<script setup>
import ManuscriptClient from './ManuscriptClient.vue';

const props = defineProps({
    manuscript: Object,
    siblings: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
  <!-- 
        Sandbox Wrapper:
        Provides the full-screen context for the client.
    -->
  <div class="h-screen w-screen bg-stone-900 overflow-hidden">
    <ManuscriptClient 
      :manuscript="props.manuscript"
      :siblings="props.siblings"
    />
  </div>
</template>
