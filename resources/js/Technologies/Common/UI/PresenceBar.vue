<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { usePresence } from '@/Core/Sync/usePresence';

const props = defineProps({
    entityType: {
        type: String,
        required: true
    },
    entitySlug: {
        type: String,
        required: true
    },
    variant: {
        type: String,
        default: 'light' // 'light' | 'dark'
    }
});

const { activeUsers, count, isLoading, join, leave } = usePresence();

// Auto-join on mount
onMounted(() => {
    join(props.entityType, props.entitySlug);
});

// Auto-leave on unmount
onUnmounted(() => {
    leave();
});

// Display max 3 avatars
const displayedUsers = computed(() => activeUsers.value.slice(0, 3));
const remainingCount = computed(() => Math.max(0, count.value - 3));

// Get initials from name
const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

// Get color based on user ID (consistent colors)
const getAvatarColor = (userId) => {
    const colors = [
        'bg-blue-500',
        'bg-green-500',
        'bg-purple-500',
        'bg-pink-500',
        'bg-indigo-500',
        'bg-yellow-500',
        'bg-red-500',
        'bg-teal-500'
    ];
    const hash = userId.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    return colors[hash % colors.length];
};
</script>

<template>
    <div v-if="count > 0 || isLoading" 
        :class="[
            'flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all',
            variant === 'dark' 
                ? 'bg-white/5 border border-white/10 shadow-none' 
                : 'bg-white border border-gray-100 shadow-sm'
        ]"
    >
        <div v-if="isLoading" 
            :class="[
                'text-xs animate-pulse',
                variant === 'dark' ? 'text-gray-500' : 'text-gray-400'
            ]"
        >
            جاري الاتصال...
        </div>
        <template v-else>
            <!-- User Avatars -->
            <div class="flex -space-x-2">
                <div 
                    v-for="user in displayedUsers" 
                    :key="user.id"
                    :class="[
                        'w-7 h-7 rounded-full text-white flex items-center justify-center text-[10px] font-bold border-2 transition-transform hover:scale-110 cursor-pointer',
                        variant === 'dark' ? 'border-[#1e1e1e]' : 'border-white',
                        getAvatarColor(user.id)
                    ]"
                    :title="user.name"
                >
                    {{ getInitials(user.name) }}
                </div>
            </div>

            <!-- Count Text -->
            <div class="flex items-center gap-1.5">
                <span :class="['text-[11px] font-medium', variant === 'dark' ? 'text-gray-400' : 'text-gray-700']">
                    <span v-if="count === 1">أنت فقط</span>
                    <span v-else-if="count === 2">مستخدم آخر</span>
                    <span v-else>{{ count - 1 }} مستخدمين</span>
                </span>

                <!-- Remaining Count Badge -->
                <span v-if="remainingCount > 0" 
                    :class="[
                        'px-1 py-0.5 text-[9px] font-bold rounded',
                        variant === 'dark' ? 'text-gray-400 bg-white/10' : 'text-gray-600 bg-gray-100'
                    ]"
                >
                    +{{ remainingCount }}
                </span>
            </div>

            <div class="relative flex items-center">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <div class="absolute w-2 h-2 bg-green-400 rounded-full animate-ping"></div>
            </div>
        </template>
    </div>
</template>

<style scoped>
@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}

.animate-ping {
    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>
