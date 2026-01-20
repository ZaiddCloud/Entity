<script setup>
defineProps({
    icon: {
        type: String,
        default: ''
    },
    label: {
        type: String,
        default: ''
    },
    active: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: ''
    },
    red: {
        type: Boolean,
        default: false
    }
})

defineEmits(['click'])
</script>

<template>
  <button
    type="button"
    :disabled="disabled"
    :title="title"
    class="flex items-center justify-center w-8 h-8 rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-100"
    :class="[
      active 
        ? 'bg-blue-100 text-blue-700 border border-blue-200' 
        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 border border-transparent',
      disabled ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer',
      red ? 'hover:bg-red-50 hover:text-red-600' : ''
    ]"
    @click="$emit('click')"
    @mousedown.prevent
  >
    <i
      v-if="icon"
      :class="icon"
    />
    <span
      v-if="label"
      class="text-xs font-medium ml-1"
    >{{ label }}</span>
    <slot />
  </button>
</template>
