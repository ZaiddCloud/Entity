<template>
  <div class="fixed inset-0 bg-white z-50 p-8">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">
        Editor Placeholder
      </h2>
      <button
        class="p-2 bg-gray-100 rounded"
        @click="$emit('close')"
      >
        Close
      </button>
    </div>
    <p>The EditorContainer component is currently missing. This is a placeholder.</p>
  </div>
</template>

<script setup>
defineProps({
    title: String,
    type: String,
    initialContent: Array,
});
defineEmits(['close', 'save']);
</script>
