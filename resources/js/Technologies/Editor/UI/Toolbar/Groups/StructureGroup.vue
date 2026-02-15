<script setup>
import { ref } from 'vue'
import ToolbarButton from '../Components/ToolbarButton.vue'
import { useTiptapStore } from '@/Technologies/Editor/Core/TiptapStore'

const store = useTiptapStore()
const isOpen = ref(false)

const setHeading = (level) => {
    store.executeCommand('heading', level)
    isOpen.value = false
}

const setParagraph = () => {
    store.executeCommand('setParagraph')
    isOpen.value = false
}
</script>

<template>
  <div class="relative">
    <ToolbarButton 
      label="هيكلية" 
      icon="📑"
      :active="isOpen"
      title="تنسيق العناوين والفقرات" 
      @click="isOpen = !isOpen"
    />
        
    <!-- Dropdown Menu -->
    <div
      v-if="isOpen"
      class="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 min-w-[150px] z-50 flex flex-col gap-1"
      @click.away="isOpen = false"
    >
      <button
        class="px-3 py-2 text-right hover:bg-gray-50 text-sm w-full"
        :class="{'bg-blue-50 text-blue-600': store.isActive('paragraph')}"
        @click="setParagraph"
      >
        فقرة عادية
      </button>
      <div class="h-px bg-gray-100 my-0.5" />
      <button
        class="px-3 py-2 text-right hover:bg-gray-50 text-lg font-bold w-full"
        :class="{'bg-blue-50 text-blue-600': store.isActive('heading', { level: 1 })}"
        @click="setHeading(1)"
      >
        عنوان رئيسي 1
      </button>
      <button
        class="px-3 py-2 text-right hover:bg-gray-50 text-base font-bold w-full"
        :class="{'bg-blue-50 text-blue-600': store.isActive('heading', { level: 2 })}"
        @click="setHeading(2)"
      >
        عنوان فرعي 2
      </button>
      <button
        class="px-3 py-2 text-right hover:bg-gray-50 text-sm font-bold w-full"
        :class="{'bg-blue-50 text-blue-600': store.isActive('heading', { level: 3 })}"
        @click="setHeading(3)"
      >
        عنوان صغير 3
      </button>
      <button
        class="px-3 py-2 text-right hover:bg-gray-50 text-xs font-bold w-full"
        :class="{'bg-blue-50 text-blue-600': store.isActive('heading', { level: 4 })}"
        @click="setHeading(4)"
      >
        عنوان مقطع 4 (دستوري)
      </button>
      <button
        class="px-3 py-2 text-right hover:bg-gray-50 text-xs font-bold w-full"
        :class="{'bg-blue-50 text-blue-600': store.isActive('heading', { level: 5 })}"
        @click="setHeading(5)"
      >
        عنوان فرعي 5
      </button>
      <button
        class="px-3 py-2 text-right hover:bg-gray-50 text-xs font-bold w-full"
        :class="{'bg-blue-50 text-blue-600': store.isActive('heading', { level: 6 })}"
        @click="setHeading(6)"
      >
        عنوان فرعي 6
      </button>
    </div>
        
    <!-- Backdrop to close -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40"
      @click="isOpen = false"
    />
  </div>
</template>
