<script setup>
import { ref } from 'vue'

const activeTab = ref('structure')

const tabs = [
    { id: 'structure', label: 'الهيكل', icon: '📋' },
    { id: 'footnotes', label: 'الحواشي', icon: '📌' },
    { id: 'media', label: 'الوسائط', icon: '🖼️' },
    { id: 'history', label: 'التاريخ', icon: '🕐' }
]
</script>

<template>
    <aside class="editor-sidebar w-64 bg-white border-l border-gray-200 flex flex-col">
        <!-- Tabs -->
        <div class="flex border-b border-gray-200">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                class="flex-1 py-3 text-sm font-medium transition-colors"
                :class="activeTab === tab.id 
                    ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50' 
                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                @click="activeTab = tab.id"
            >
                <span class="mr-1">{{ tab.icon }}</span>
                {{ tab.label }}
            </button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4">
            <div v-if="activeTab === 'structure'" class="space-y-2">
                <p class="text-sm text-gray-500">شجرة هيكل الكتاب</p>
                <div class="text-xs text-gray-400">قريباً...</div>
            </div>

            <div v-if="activeTab === 'footnotes'" class="space-y-2">
                <p class="text-sm text-gray-500">الحواشي العلمية</p>
                <div class="text-xs text-gray-400">قريباً...</div>
            </div>

            <div v-if="activeTab === 'media'" class="space-y-2">
                <p class="text-sm text-gray-500">مكتبة الوسائط</p>
                <div class="text-xs text-gray-400">قريباً...</div>
            </div>

            <div v-if="activeTab === 'history'" class="space-y-2">
                <p class="text-sm text-gray-500">تاريخ المراجعات</p>
                <div class="text-xs text-gray-400">قريباً...</div>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.editor-sidebar {
    direction: rtl;
}
</style>
