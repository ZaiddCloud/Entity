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
    }
});

const { activeUsers, count, join, leave } = usePresence();

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
    <div v-if="count > 0" class="flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-lg shadow-sm">
        <!-- User Avatars -->
        <div class="flex -space-x-2">
            <div 
                v-for="user in displayedUsers" 
                :key="user.id"
                :class="[
                    'w-8 h-8 rounded-full text-white flex items-center justify-center text-xs font-medium border-2 border-white transition-transform hover:scale-110 cursor-pointer',
                    getAvatarColor(user.id)
                ]"
                :title="user.name"
            >
                {{ getInitials(user.name) }}
            </div>
        </div>

        <!-- Count Text -->
        <div class="flex items-center gap-1.5">
            <span class="text-sm font-medium text-gray-700">
                <span v-if="count === 1">أنت فقط</span>
                <span v-else-if="count === 2">مستخدم آخر</span>
                <span v-else>{{ count - 1 }} مستخدمين</span>
            </span>

            <!-- Remaining Count Badge -->
            <span v-if="remainingCount > 0" class="px-1.5 py-0.5 text-xs font-medium text-gray-600 bg-gray-100 rounded">
                +{{ remainingCount }}
            </span>
        </div>

        <!-- Live Indicator -->
        <div class="relative flex items-center">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <div class="absolute w-2 h-2 bg-green-400 rounded-full animate-ping"></div>
        </div>
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
