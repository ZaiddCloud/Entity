<script setup>
import { computed } from 'vue'
import ManuscriptClient from '../../Manuscripter/ManuscriptClient.vue'
import PlayerClient from '../../Player/PlayerClient.vue'

const props = defineProps({
    type: { type: String, required: true }, // 'manuscript' | 'audio' | 'video'
    entity: { type: Object, required: true }
})

// Normalize type for internal switching logic
const normalizedType = computed(() => {
    if (props.type === 'manuscript') return 'manuscript'
    if (['audio', 'video'].includes(props.type)) return 'media'
    return 'unknown'
})
</script>

<template>
  <div class="w-full h-full bg-black relative">
    <!-- 
        1. Manuscript Viewer
        Expects: manuscript, siblings
     -->
    <ManuscriptClient
      v-if="normalizedType === 'manuscript'"
      :manuscript="props.entity"
      :siblings="props.entity.siblings || []" 
    />

    <!-- 
        2. Media Player (Audio/Video)
        Expects: media, type
     -->
    <PlayerClient
      v-else-if="normalizedType === 'media'"
      :media="props.entity"
      :type="props.type" 
    />

    <!-- Fallback -->
    <div 
      v-else 
      class="w-full h-full flex flex-col items-center justify-center text-gray-500"
    >
      <span class="text-4xl mb-4">🧩</span>
      <p>نوع المحتوى غير مدعوم: {{ props.type }}</p>
    </div>
  </div>
</template>
