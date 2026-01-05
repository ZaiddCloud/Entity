<script setup>
import { ref } from 'vue';

defineProps({
    modelValue: [String, Number],
    options: {
        type: Array,
        default: () => [],
    },
    labelKey: {
        type: String,
        default: 'name',
    },
    valueKey: {
        type: String,
        default: 'id',
    },
    placeholder: {
        type: String,
        default: 'Please select',
    },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="relative">
        <select
            class="w-full pr-10 pl-4 py-3 bg-gray-50 dark:bg-white/5 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-black focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-xs font-bold transition-all appearance-none cursor-pointer"
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option :value="undefined">{{ placeholder }}</option>
            <option v-for="option in options" :key="option[valueKey]" :value="option[valueKey]">
                {{ option[labelKey] }}
            </option>
        </select>
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>
</template>
