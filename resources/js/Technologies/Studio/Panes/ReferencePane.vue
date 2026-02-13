<script setup>
import { computed, inject } from 'vue' // Added inject
import ManuscriptClient from '../../Manuscripter/ManuscriptClient.vue'
import PlayerClient from '../../Player/PlayerClient.vue'

const props = defineProps({
    type: { type: String, required: true }, // 'manuscript' | 'audio' | 'video'
    entity: { type: Object, required: true },
    activeSlug: { type: String, default: null }, // Legacy
    activeChildId: { type: String, default: null },
    isIntegrated: { type: Boolean, default: false },
    isStudioContext: { type: Boolean, default: false } // NEW: Indicates Studio environment
})

const emit = defineEmits(['navigate', 'navigate-full', 'toggle-dock', 'add-node'])

const isPlayerDocked = inject('isPlayerDocked', { value: false }) // Inject with default

// Normalize type for internal switching logic
const normalizedType = computed(() => {
    if (props.type === 'manuscript') return 'manuscript'
    if (['audio', 'video'].includes(props.type)) return 'media'
    return 'unknown'
})
</script>

<template>
  <div class="relative" :class="(normalizedType === 'media' && !isPlayerDocked.value) ? '' : 'w-full h-full bg-black'">
    <!-- 
        1. Manuscript Viewer
        Expects: manuscript, siblings
     -->
    <ManuscriptClient
      v-if="normalizedType === 'manuscript'"
      :manuscript="props.entity"
      :siblings="props.entity.siblings || []" 
      :active-child-id="props.activeChildId"
      @navigate="(id) => $emit('navigate', id)"
      @navigate-full="() => $emit('navigate-full')"
    />

    <!-- 
        2. Media Player (Audio/Video)
        Expects: media, type
     -->
    <PlayerClient
      v-else-if="normalizedType === 'media'"
      :media="props.entity"
      :type="props.type" 
      :active-child-id="props.activeChildId"
      :is-integrated="props.isIntegrated"
      :is-studio-context="props.isStudioContext"
      @toggle-dock="$emit('toggle-dock')"
      @navigate="(id) => $emit('navigate', id)"
      @navigate-full="() => $emit('navigate-full')"
      @add-node="(data) => $emit('add-node', data)"
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
