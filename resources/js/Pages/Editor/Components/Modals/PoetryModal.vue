<script setup>
import { ref } from 'vue'

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'insert'])

const sadr = ref('')
const ajuz = ref('')
const poet = ref('')
const source = ref('')

const handleInsert = () => {
    if (!sadr.value || !ajuz.value) return

    emit('insert', {
        type: 'poetry',
        data: {
            sadr: sadr.value,
            ajuz: ajuz.value,
            poet: poet.value,
            source: source.value
        }
    })

    // Reset
    sadr.value = ''
    ajuz.value = ''
    poet.value = ''
    source.value = ''
    emit('close')
}
</script>

<template>
    <div v-if="isOpen" class="modal-overlay" @click.self="emit('close')">
        <div class="modal-content" dir="rtl">
            <div class="modal-header">
                <h3 class="modal-title">📖 إدراج بيت شعر</h3>
                <button class="modal-close" @click="emit('close')">✖️</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">الصدر *</label>
                    <input 
                        v-model="sadr" 
                        type="text" 
                        class="form-input"
                        placeholder="أدخل صدر البيت..."
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">العجز *</label>
                    <input 
                        v-model="ajuz" 
                        type="text" 
                        class="form-input"
                        placeholder="أدخل عجز البيت..."
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">الشاعر</label>
                    <input 
                        v-model="poet" 
                        type="text" 
                        class="form-input"
                        placeholder="اسم الشاعر (اختياري)"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">المصدر</label>
                    <input 
                        v-model="source" 
                        type="text" 
                        class="form-input"
                        placeholder="مصدر البيت (اختياري)"
                    >
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" @click="emit('close')">إلغاء</button>
                <button 
                    class="btn btn-primary" 
                    :disabled="!sadr || !ajuz"
                    @click="handleInsert"
                >
                    إدراج
                </button>
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
    color: #111827;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: #6b7280;
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
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
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
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    border: none;
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
