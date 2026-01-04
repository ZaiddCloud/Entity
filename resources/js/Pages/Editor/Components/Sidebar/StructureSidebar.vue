<script setup>
import { computed } from 'vue'

const props = defineProps({
    hierarchy: {
        type: Array,
        default: () => []
    },
    currentId: {
        type: String,
        default: null
    }
})

const emit = defineEmits(['navigate'])

// Helper to flatten or format hierarchy if needed. 
// Assuming hierarchy is a flat list with 'parent_id' for now, or a nested tree.
// EntityContentService returns a flat list ordered by 'order'.
// We can just render a flat list for v1, or build a tree if we have time.
// For now, a clean list is sufficient and robust.

const items = computed(() => {
    return props.hierarchy.map((item, index) => ({
        ...item,
        label: item.title || `Element ${index + 1}`,
        isActive: item._id === props.currentId,
        icon: getIcon(item.type)
    }))
})

const getIcon = (type) => {
    switch (type) {
        case 'chapter': return '📂'
        case 'section': return '📑'
        case 'page': return '📄'
        case 'scene': return '🎬'
        case 'segment': return '🎵'
        default: return '📄'
    }
}
</script>

<template>
    <div class="h-full flex flex-col bg-gray-50">
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-gray-200 bg-white/50 backdrop-blur sticky top-0 z-10">
            <h3 class="font-bold text-gray-700 text-xs uppercase tracking-wider">هيكلية المحتوى</h3>
            <p class="text-[10px] text-gray-400 mt-1">{{ items.length }} عنصر</p>
        </div>

        <!-- List -->
        <div class="flex-1 overflow-y-auto p-2 custom-scrollbar">
            <div v-if="items.length === 0" class="text-center py-10 text-gray-400 text-xs">
                لا توجد عناصر فرعية
            </div>
            
            <div 
                v-for="item in items" 
                :key="item._id"
                @click="emit('navigate', item)"
                class="group flex items-center gap-3 p-2.5 rounded-lg mb-1 cursor-pointer transition-all duration-200 border border-transparent"
                :class="[
                    item.isActive 
                        ? 'bg-white border-blue-200 shadow-sm' 
                        : 'hover:bg-white hover:border-gray-200 text-gray-600'
                ]"
            >
                <!-- Number/Icon -->
                <div 
                    class="w-6 h-6 flex items-center justify-center rounded text-[10px] font-mono transition-colors"
                     :class="item.isActive ? 'bg-blue-100 text-blue-700' : 'bg-gray-200 text-gray-500 group-hover:bg-gray-100'"
                >
                    {{ item.order || '#' }}
                </div>

                <!-- Title -->
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium truncate" :class="{'text-blue-700': item.isActive}">
                        {{ item.label }}
                    </p>
                    <p v-if="item.type" class="text-[9px] text-gray-400 opacity-80">{{ item.type }}</p>
                </div>

                <!-- Active Indicator -->
                <div v-if="item.isActive" class="w-1.5 h-1.5 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.6)]"></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
</style>
