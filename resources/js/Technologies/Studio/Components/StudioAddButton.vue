<script setup>
import { ref, computed } from 'vue'
import { Plus, ChevronDown, Check } from 'lucide-vue-next'
import { useStudioContentProcess } from '../Composables/useStudioContentProcess'

/**
 * Step 3: StudioAddButton (The Smart Trigger) 🖱️
 */
const props = defineProps({
    type: { type: String, required: true }, // book, manuscript, audio, video
    slug: { type: String, required: true },
    visualMap: { type: Object, default: () => ({}) },
    contextData: { type: Object, default: () => ({ currentTime: 0, currentFolio: 0, lastMarker: 0 }) }
})

const emit = defineEmits(['insert-node'])

const orchestrator = useStudioContentProcess()
const isOpen = ref(false)
const selectedType = ref(null)
const nodeTitle = ref('')

const allowedTypes = computed(() => {
    return Object.keys(props.visualMap).map(key => ({
        id: key,
        ...props.visualMap[key]
    }))
})

const toggleDropdown = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        selectedType.value = null
        nodeTitle.value = ''
    }
}

const selectType = (type) => {
    selectedType.value = type
    
    // Auto-complete logic
    if (props.type === 'audio' || props.type === 'video') {
        nodeTitle.value = `${type.label} at ${Math.floor(props.contextData.currentTime)}s`
    } else if (props.type === 'manuscript' && (type.id === 'folio' || type.id === 'page')) {
        nodeTitle.value = `${type.label} ${props.contextData.currentFolio + 1}`
    } else {
        nodeTitle.value = `New ${type.label}`
    }
}

const handleSubmit = () => {
    if (!selectedType.value || !nodeTitle.value) return
    
    const time = (props.type === 'audio' || props.type === 'video') ? props.contextData.currentTime : null
    
    emit('insert-node', {
        type: selectedType.value.id,
        title: nodeTitle.value,
        time: time
    })
    
    isOpen.value = false
}
</script>

<template>
    <div class="relative inline-block text-right rtl">
        <!-- Main Trigger -->
        <button 
            @click="toggleDropdown"
            dusk="studio-add-button"
            class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-md text-xs font-bold transition-all shadow-lg shadow-emerald-900/20 active:scale-95"
            title="إضافة عنصر هيكلي"
        >
            <Plus class="w-3.5 h-3.5" />
            <span>إضافة</span>
            <ChevronDown class="w-3 h-3 opacity-70" />
        </button>

        <!-- Dropdown Menu -->
        <div 
            v-if="isOpen"
            dusk="studio-add-dropdown"
            class="absolute right-0 mt-2 w-56 bg-zinc-900 border border-zinc-800 rounded-lg shadow-2xl z-[100] overflow-hidden"
        >
            <div v-if="!selectedType" class="py-1">
                <div class="px-3 py-2 text-[10px] uppercase tracking-wider text-zinc-500 font-bold">اختر نوع العنصر</div>
                <button
                    v-for="type in allowedTypes"
                    :key="type.id"
                    :dusk="`type-option-${type.id}`"
                    @click="selectType(type)"
                    class="w-full h-9 px-3 flex items-center justify-between hover:bg-zinc-800 text-zinc-300 text-xs transition-colors"
                >
                    <span>{{ type.label }}</span>
                    <span class="text-[9px] opacity-40 px-1.5 py-0.5 border border-zinc-700 rounded">{{ type.tag }}</span>
                </button>
            </div>

            <!-- Title Input (Step 3 Flow) -->
            <div v-else class="p-3 bg-zinc-800/50">
                <div class="text-[10px] text-emerald-400 font-bold mb-2">إضافة {{ selectedType.label }}</div>
                <input 
                    v-model="nodeTitle"
                    dusk="node-title-input"
                    type="text"
                    class="w-full bg-zinc-900 border border-zinc-700 rounded px-2 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500 mb-3"
                    placeholder="العنوان..."
                    autoFocus
                    @keyup.enter="handleSubmit"
                />
                <div class="flex gap-2">
                    <button 
                        @click="handleSubmit"
                        dusk="studio-add-submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-bold py-1.5 rounded transition-colors"
                    >
                        تأكيد الإضافة
                    </button>
                    <button 
                        @click="selectedType = null"
                        class="bg-zinc-700 hover:bg-zinc-600 text-white text-[10px] font-bold px-3 py-1.5 rounded transition-colors"
                    >
                        رجوع
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.rtl { direction: rtl; }
</style>
