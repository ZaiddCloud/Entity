<script setup>
defineProps({
    color: {
        type: String,
        default: 'indigo', // indigo, gray, green, red, blue
    }
});

const colorClasses = {
    indigo: 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400',
    gray: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300',
    blue: 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400',
    emerald: 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    rose: 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400',
};
</script>

<template>
  <span :class="['px-2 py-0.5 rounded-lg text-xs font-bold', colorClasses[color] || colorClasses.gray]">
    <slot />
  </span>
</template>
