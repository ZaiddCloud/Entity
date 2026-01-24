<script setup>
import { computed } from 'vue'

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['update:modelValue'])

const scenes = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
})

const removeScene = (index) => {
    const s = [...scenes.value]
    s.splice(index, 1)
    scenes.value = s
}
</script>

<template>
  <div class="p-8 font-arabic">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-gray-800 underline decoration-indigo-200 decoration-4">
        فهرسة المشاهد المرئية
      </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="(scene, index) in scenes"
        :key="scene.id"
        class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-all border-r-4 border-r-indigo-500"
      >
        <div class="flex items-center gap-2 mb-2">
          <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded">#{{ index + 1 }}</span>
          <input
            v-model="scene.timestamp"
            type="text"
            class="w-16 text-center font-mono text-xs border-b border-gray-100 outline-none"
            placeholder="00:00"
          >
          <button
            class="mr-auto text-gray-300 hover:text-red-500 transition-colors"
            @click="removeScene(index)"
          >
            ✕
          </button>
        </div>
        <input
          v-model="scene.title"
          type="text"
          class="w-full font-bold text-sm mb-2 border-none outline-none focus:ring-0"
          placeholder="عنوان المشهد..."
        >
        <textarea 
          v-model="scene.description" 
          placeholder="وصف المشهد أو الملاحظات..."
          class="w-full h-20 text-xs p-2 bg-gray-50 border-none rounded outline-none resize-none"
        />
      </div>
    </div>

    <div
      v-if="!scenes.length"
      class="text-center py-20 opacity-40"
    >
      <div class="mb-4 text-4xl">
        🎬
      </div>
      <p class="text-sm font-bold">
        لا توجد مشاهد مؤرشفة. ابدأ بالتوثيق الآن.
      </p>
    </div>
  </div>
</template>
