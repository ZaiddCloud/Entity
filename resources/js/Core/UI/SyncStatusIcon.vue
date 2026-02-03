<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String, // synced, pending, offline, error, conflict
        default: 'synced'
    },
    size: {
        type: String,
        default: 'md' // sm, md, lg
    }
});

const config = computed(() => {
    switch (props.status) {
        case 'synced':
            return { icon: '✅', color: 'text-emerald-500', label: 'Synced', bg: 'bg-emerald-500/10' };
        case 'pending':
            return { icon: '🔄', color: 'text-orange-400', label: 'Pending Sync', bg: 'bg-orange-500/10' };
        case 'offline':
            return { icon: '📡', color: 'text-slate-400', label: 'Offline', bg: 'bg-slate-500/10' };
        case 'error':
            return { icon: '❌', color: 'text-red-500', label: 'Sync Error', bg: 'bg-red-500/10' };
        case 'conflict':
            return { icon: '🛡️', color: 'text-blue-400', label: 'Conflict', bg: 'bg-blue-500/10' };
        default:
            return { icon: '❓', color: 'text-slate-500', label: 'Unknown', bg: 'bg-slate-500/10' };
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm': return 'text-[10px] px-1.5 py-0.5';
        case 'lg': return 'text-sm px-3 py-1.5';
        default: return 'text-xs px-2 py-1';
    }
});
</script>

<template>
    <div 
        class="inline-flex items-center gap-1.5 rounded-full font-bold transition-all duration-300 border border-transparent"
        :class="[config.bg, config.color, sizeClasses]"
        :title="config.label"
    >
        <span class="animate-pulse" v-if="status === 'pending'">{{ config.icon }}</span>
        <span v-else>{{ config.icon }}</span>
        <span v-if="size !== 'sm'">{{ config.label }}</span>
    </div>
</template>
