<script setup>
import { ref } from 'vue'

const props = defineProps({
    label: String,
    icon: String,
    items: {
        type: Array,
        required: true
    },
    defaultValue: String
})

const emit = defineEmits(['command'])

const isOpen = ref(false)
const selectedLabel = ref(props.defaultValue || props.items[0]?.label)

const selectItem = (item) => {
    selectedLabel.value = item.label
    emit('command', item.command, item.args || item.value)
    isOpen.value = false
}

const toggleDropdown = () => {
    isOpen.value = !isOpen.value
}
</script>

<template>
    <div class="toolbar-dropdown" @click="toggleDropdown">
        <button class="toolbar-dropdown__button">
            <span v-if="icon" class="mr-1">{{ icon }}</span>
            <span class="text-sm">{{ selectedLabel }}</span>
            <span class="mr-1">▼</span>
        </button>

        <div v-if="isOpen" class="toolbar-dropdown__menu">
            <div
                v-for="(item, index) in items"
                :key="index"
                class="toolbar-dropdown__item"
                @click.stop="selectItem(item)"
            >
                {{ item.label }}
            </div>
        </div>
    </div>
</template>

<style scoped>
.toolbar-dropdown {
    position: relative;
}

.toolbar-dropdown__button {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 13px;
}

.toolbar-dropdown__button:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.toolbar-dropdown__menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 4px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    min-width: 150px;
    z-index: 1000;
    overflow: hidden;
}

.toolbar-dropdown__item {
    padding: 8px 16px;
    cursor: pointer;
    transition: background 0.15s ease;
    font-size: 13px;
}

.toolbar-dropdown__item:hover {
    background: #f3f4f6;
}
</style>
