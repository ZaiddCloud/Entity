<script setup>
import { ref } from 'vue'
import { FOOTNOTE_TYPES, FOOTNOTE_LABELS } from '../../Constants/footnoteTypes'

const props = defineProps({
    isOpen: Boolean,
    footnote: Object
})

const emit = defineEmits(['close', 'save'])

const footnoteType = ref(props.footnote?.type || FOOTNOTE_TYPES.COMMENT)
const content = ref(props.footnote?.content || '')
const reference = ref(props.footnote?.reference || '')

const types = Object.values(FOOTNOTE_TYPES).map(type => ({
    value: type,
    label: FOOTNOTE_LABELS[type]
}))

const handleSave = () => {
    emit('save', {
        type: footnoteType.value,
        content: content.value,
        reference: reference.value
    })
    emit('close')
}
</script>

<template>
    <div v-if="isOpen" class="modal-overlay" @click.self="emit('close')">
        <div class="modal-content" dir="rtl">
            <div class="modal-header">
                <h3 class="modal-title">📌 تحرير حاشية</h3>
                <button class="modal-close" @click="emit('close')">✖️</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">نوع الحاشية</label>
                    <select v-model="footnoteType" class="form-select">
                        <option v-for="type in types" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">المحتوى</label>
                    <textarea 
                        v-model="content" 
                        class="form-textarea"
                        rows="6"
                        placeholder="أدخل محتوى الحاشية..."
                    ></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">المرجع</label>
                    <input 
                        v-model="reference" 
                        type="text" 
                        class="form-input"
                        placeholder="المرجع (اختياري)"
                    >
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" @click="emit('close')">إلغاء</button>
                <button class="btn btn-primary" @click="handleSave">حفظ</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
}

.modal-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-select,
.form-input,
.form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-family: 'Amiri', serif;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
}

.btn-secondary {
    background: #f3f4f6;
    border: 1px solid #d1d5db;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    border: none;
}
</style>
