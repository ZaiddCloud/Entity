<script setup>
import { computed } from 'vue'

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['update:modelValue'])

const segments = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
})

const removeSegment = (index) => {
    const s = [...segments.value]
    s.splice(index, 1)
    segments.value = s
}
</script>

<template>
    <div class="p-8 font-arabic">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800 underline decoration-blue-200 decoration-4">تفريغ المقاطع الصوتية</h2>
        </div>

        <div class="space-y-4">
            <div v-for="(segment, index) in segments" :key="segment.id" class="p-4 bg-gray-50 border border-gray-200 rounded-xl hover:shadow-md transition-shadow">
                <div class="flex items-center gap-4 mb-3">
                    <input v-model="segment.startTime" type="text" placeholder="البداية" class="w-20 px-2 py-1 border rounded text-center font-mono text-sm" />
                    <span>←</span>
                    <input v-model="segment.endTime" type="text" placeholder="النهاية" class="w-20 px-2 py-1 border rounded text-center font-mono text-sm" />
                    <input v-model="segment.label" type="text" class="flex-1 px-3 py-1 border rounded font-bold" placeholder="عنوان المقطع" />
                    <button @click="removeSegment(index)" class="text-red-400 hover:text-red-600">🗑️</button>
                </div>
                <textarea 
                    v-model="segment.text" 
                    placeholder="تفريغ المحتوى هنا..."
                    class="w-full h-24 p-3 border rounded-lg outline-none focus:ring-2 focus:ring-red-100 transition-all resize-none"
                ></textarea>
            </div>
        </div>

        <div v-if="!segments.length" class="text-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
            <p class="text-gray-400">لا توجد مقاطع مضافة بعد. ابدأ بإضافة أول مقطع!</p>
        </div>
    </div>
</template>
