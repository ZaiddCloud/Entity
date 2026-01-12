<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: String,
    as: {
        type: String,
        default: 'button',
    },
    color: {
        type: String,
        default: 'gray',
    }
});

const colorClasses = {
    gray: 'text-gray-400 hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500/10',
    indigo: 'text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10',
    emerald: 'text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10',
    rose: 'text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10',
};
</script>

<template>
  <component 
    :is="href ? Link : 'button'" 
    :href="href"
    :class="['p-2 rounded-xl transition-all', colorClasses[color]]"
  >
    <slot />
  </component>
</template>
