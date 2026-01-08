<script setup>
import { ref, nextTick } from 'vue'
import ToolbarButton from '../Components/ToolbarButton.vue'
import { useTiptapStore } from '@/Technologies/Editor/Core/TiptapStore'

const store = useTiptapStore()
const showInput = ref(false)
const inputType = ref('') // 'link' or 'image'
const urlValue = ref('')
const inputRef = ref(null)

const openInput = (type) => {
    inputType.value = type
    showInput.value = true
    urlValue.value = ''
    nextTick(() => {
        inputRef.value?.focus()
    })
}

const confirmInput = () => {
    if (urlValue.value) {
        if (inputType.value === 'link') {
            store.executeCommand('setLink', urlValue.value)
        } else {
            store.executeCommand('setImage', urlValue.value)
        }
    }
    showInput.value = false
}

const cancelInput = () => {
    showInput.value = false
}
</script>

<template>
    <div class="flex items-center gap-0.5 relative">
        <ToolbarButton 
            icon="ri-link" 
            title="إدراج رابط" 
            :active="store.isActive('link')" 
            @click="openInput('link')" 
        />
        <ToolbarButton 
            icon="ri-image-add-line" 
            title="إدراج صورة" 
            @click="openInput('image')" 
        />
        <ToolbarButton 
            icon="ri-table-line" 
            title="إدراج جدول" 
            @click="store.executeCommand('insertTable')" 
        />

        <!-- Simple Popover for Input -->
        <div v-if="showInput" 
             class="absolute top-full left-0 mt-1 p-2 bg-white rounded shadow-xl border border-gray-200 z-50 flex gap-2 min-w-[250px]"
             @keydown.esc="cancelInput"
             @keydown.enter="confirmInput"
        >
            <input 
                ref="inputRef"
                v-model="urlValue"
                type="text" 
                class="flex-1 text-xs border border-gray-300 rounded px-2 py-1 outline-none focus:border-blue-500"
                :placeholder="inputType === 'link' ? 'https://example.com' : 'https://image.url'"
            />
            <button @click="confirmInput" class="text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700">✓</button>
            <button @click="cancelInput" class="text-xs text-gray-500 px-2 py-1 hover:text-red-500">×</button>
        </div>
    </div>
</template>
