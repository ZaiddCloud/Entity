<script setup>
import { ref, reactive, watch } from 'vue';
import { 
    ClockIcon, TagIcon, CheckIcon, XMarkIcon, 
    PencilSquareIcon, TrashIcon, PlusIcon, ArrowDownOnSquareIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    currentTime: { type: Number, required: true },
    duration: { type: Number, default: 0 },
    initialSegments: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:segments', 'seek', 'save-final']);

const localSegments = ref([...props.initialSegments]);
const editingIndex = ref(null);

const colorPalette = [
    { name: 'أزرق', hex: '#3b82f6' },
    { name: 'أخضر', hex: '#22c55e' },
    { name: 'أحمر', hex: '#ef4444' },
    { name: 'أصفر', hex: '#eab308' },
    { name: 'بنفسجي', hex: '#a855f7' },
    { name: 'وردي', hex: '#ec4899' },
];

const draft = reactive({
    start: 0,
    end: 0,
    label: '',
    color: '#3b82f6',
    text: ''
});

const captureTime = (field) => {
    const time = parseFloat(props.currentTime.toFixed(2));
    if (field === 'start') {
        draft.start = time;
        if (draft.end <= draft.start) draft.end = Math.min(time + 5, props.duration);
    } else {
        if (time > draft.start) draft.end = time;
        else alert('وقت النهاية يجب أن يكون بعد وقت البداية');
    }
};

const saveDraft = () => {
    if (!draft.label) return alert('يرجى كتابة عنوان للمقطع');
    if (draft.end <= draft.start) return alert('تأكد من صحة التوقيت');

    const payload = { ...draft };

    if (editingIndex.value !== null) {
        localSegments.value[editingIndex.value] = payload;
    } else {
        localSegments.value.push(payload);
    }

    localSegments.value.sort((a, b) => a.start - b.start);
    emit('update:segments', localSegments.value);
    resetDraft();
};

const editItem = (index) => {
    const item = localSegments.value[index];
    draft.start = item.start;
    draft.end = item.end;
    draft.label = item.label;
    draft.color = item.color;
    draft.text = item.text || '';
    editingIndex.value = index;
    emit('seek', item.start);
};

const deleteItem = (index) => {
    if (confirm('هل أنت متأكد من حذف هذا المقطع؟')) {
        localSegments.value.splice(index, 1);
        emit('update:segments', localSegments.value);
        if (editingIndex.value === index) resetDraft();
    }
};

const resetDraft = () => {
    draft.start = 0;
    draft.end = 0;
    draft.label = '';
    draft.text = '';
    draft.color = '#3b82f6';
    editingIndex.value = null;
};

const formatTime = (seconds) => {
    if (!seconds || isNaN(seconds)) return "00:00";
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
};

watch(() => props.initialSegments, (newVal) => {
    localSegments.value = [...newVal];
}, { deep: true });
</script>

<template>
    <div class="flex flex-col h-full bg-gray-900 border-r border-gray-800 text-gray-100 font-sans" dir="rtl">
        <div class="p-4 border-b border-gray-800 bg-gray-900/95 backdrop-blur flex justify-between items-center sticky top-0 z-10">
            <h2 class="text-lg font-bold flex items-center gap-2"><TagIcon class="w-5 h-5 text-primary-500" />محرر المقاطع</h2>
            <button @click="$emit('save-final', localSegments)" class="text-xs bg-primary-600 hover:bg-primary-500 text-white px-3 py-1.5 rounded-lg flex items-center gap-1 transition-all shadow-lg hover:shadow-primary-500/20"><ArrowDownOnSquareIcon class="w-4 h-4" />حفظ العمل</button>
        </div>

        <div class="p-4 border-b border-gray-800 bg-gray-800/30 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <div class="flex justify-between items-center"><label class="text-xs text-gray-400">البداية</label><button @click="captureTime('start')" class="text-[10px] bg-gray-700 hover:bg-white hover:text-black px-1.5 py-0.5 rounded transition-colors flex items-center gap-1"><ClockIcon class="w-3 h-3" />التقاط</button></div>
                    <input v-model.number="draft.start" type="number" step="0.1" class="w-full bg-gray-900 border border-gray-700 rounded px-2 py-1.5 text-sm text-left font-mono focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                </div>
                <div class="space-y-1">
                    <div class="flex justify-between items-center"><label class="text-xs text-gray-400">النهاية</label><button @click="captureTime('end')" class="text-[10px] bg-gray-700 hover:bg-white hover:text-black px-1.5 py-0.5 rounded transition-colors flex items-center gap-1"><ClockIcon class="w-3 h-3" />التقاط</button></div>
                    <input v-model.number="draft.end" type="number" step="0.1" class="w-full bg-gray-900 border border-gray-700 rounded px-2 py-1.5 text-sm text-left font-mono focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                </div>
            </div>
            <div class="space-y-1">
                <label class="text-xs text-gray-400">عنوان المقطع</label>
                <input v-model="draft.label" type="text" placeholder="مثال: المقدمة، شرح الحديث..." class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none placeholder-gray-600" @keyup.enter="saveDraft">
            </div>
            <div class="flex gap-2 justify-between">
                <button v-for="color in colorPalette" :key="color.hex" @click="draft.color = color.hex" class="w-6 h-6 rounded-full border-2 transition-transform hover:scale-110" :class="draft.color === color.hex ? 'border-white scale-110 shadow-lg' : 'border-transparent opacity-60 hover:opacity-100'" :style="{ backgroundColor: color.hex }" :title="color.name"></button>
            </div>
            <div class="flex gap-2 pt-2">
                <button @click="saveDraft" class="flex-1 bg-primary-600 hover:bg-primary-500 text-white py-2 rounded-lg text-sm font-medium transition-colors flex justify-center items-center gap-2"><component :is="editingIndex !== null ? CheckIcon : PlusIcon" class="w-4 h-4" />{{ editingIndex !== null ? 'تحديث المقطع' : 'إضافة مقطع' }}</button>
                <button v-if="editingIndex !== null" @click="resetDraft" class="px-3 bg-gray-700 hover:bg-gray-600 rounded-lg text-gray-300 transition-colors" title="إلغاء التعديل"><XMarkIcon class="w-5 h-5" /></button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-2 scrollbar-thin scrollbar-track-gray-900 scrollbar-thumb-gray-700">
            <div v-if="localSegments.length === 0" class="text-center py-8 text-gray-500 text-sm border-2 border-dashed border-gray-800 rounded-xl">لا توجد مقاطع مضافة.<br>استخدم أزرار الالتقاط للبدء.</div>
            <div v-for="(seg, idx) in localSegments" :key="idx" class="group relative bg-gray-800 border border-gray-700 hover:border-gray-500 rounded-lg p-3 transition-all cursor-pointer" :class="{'ring-1 ring-primary-500 bg-gray-800/80': editingIndex === idx, 'opacity-100 border-l-4': true}" :style="{ borderLeftColor: seg.color }" @click="emit('seek', seg.start)">
                <div v-if="currentTime >= seg.start && currentTime <= seg.end" class="absolute inset-0 bg-primary-500/5 pointer-events-none rounded-lg border border-primary-500/20"></div>
                <div class="flex justify-between items-start mb-1 relative z-10">
                    <h4 class="font-medium text-sm text-gray-200 line-clamp-1">{{ seg.label }}</h4>
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity bg-gray-800 rounded px-1 -mt-1">
                        <button @click.stop="editItem(idx)" class="p-1 text-gray-400 hover:text-blue-400 hover:bg-blue-400/10 rounded transition-colors" title="تعديل"><PencilSquareIcon class="w-4 h-4" /></button>
                        <button @click.stop="deleteItem(idx)" class="p-1 text-gray-400 hover:text-red-400 hover:bg-red-400/10 rounded transition-colors" title="حذف"><TrashIcon class="w-4 h-4" /></button>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs font-mono text-gray-400 relative z-10">
                    <span class="bg-gray-900 px-1.5 py-0.5 rounded">{{ formatTime(seg.start) }}</span><span>-</span><span class="bg-gray-900 px-1.5 py-0.5 rounded">{{ formatTime(seg.end) }}</span><span class="text-gray-600 px-1">|</span><span class="text-gray-500">{{ (seg.end - seg.start).toFixed(1) }} ث</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar { width: 6px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background-color: #374151; border-radius: 20px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background-color: #4b5563; }
</style>
