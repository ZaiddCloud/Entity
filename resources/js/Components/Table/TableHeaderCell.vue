<script setup>
defineProps({
    align: {
        type: String,
        default: 'right', // 'left', 'right', 'center'
        validator: (value) => ['left', 'right', 'center'].includes(value)
    }
});
</script>

<template>
  <th 
    scope="col" 
    :class="[
      'px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest',
      align === 'left' ? 'text-left' : (align === 'center' ? 'text-center' : 'text-right')
    ]"
  >
    <slot />
  </th>
</template>
