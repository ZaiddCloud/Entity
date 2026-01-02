<script setup>
import ToolbarButton from './ToolbarButton.vue'
import ToolbarDropdown from './ToolbarDropdown.vue'

defineProps({
    title: {
        type: String,
        required: true
    },
    items: {
        type: Array,
        required: true
    }
})

const emit = defineEmits(['command'])

const handleCommand = (command, value = null) => {
    emit('command', command, value)
}
</script>

<template>
    <div class="toolbar-section">
        <div class="text-[10px] text-gray-500 mb-1 px-1">{{ title }}</div>
        <div class="flex items-center gap-1">
            <template v-for="(item, index) in items" :key="index">
                <!-- Separator -->
                <div v-if="item.type === 'separator'" class="w-px h-6 bg-gray-200 mx-1"></div>
                
                <!-- Dropdown -->
                <ToolbarDropdown
                    v-else-if="item.type === 'dropdown'"
                    :label="item.label"
                    :icon="item.icon"
                    :items="item.items"
                    :default-value="item.default"
                    @command="handleCommand"
                />
                
                <!-- Button -->
                <ToolbarButton
                    v-else
                    :icon="item.icon"
                    :label="item.label"
                    :shortcut="item.shortcut"
                    :active="item.active"
                    @click="handleCommand(item.command, item.args)"
                />
            </template>
        </div>
    </div>
</template>

<style scoped>
.toolbar-section {
    display: flex;
    flex-direction: column;
}
</style>
