<script setup>
import { ref } from 'vue'
import { EXPORT_FORMATS, EXPORT_FORMAT_LABELS } from '../Core/Constants/exportFormats'

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'export'])

const selectedFormat = ref(EXPORT_FORMATS.PDF)
const includeFootnotes = ref(true)
const includeImages = ref(true)

const formats = Object.values(EXPORT_FORMATS).map(format => ({
    value: format,
    label: EXPORT_FORMAT_LABELS[format]
}))

const handleExport = () => {
    emit('export', {
        format: selectedFormat.value,
        options: {
            includeFootnotes: includeFootnotes.value,
            includeImages: includeImages.value
        }
    })
    emit('close')
}
</script>

<template>
  <div
    v-if="isOpen"
    class="modal-overlay"
    @click.self="emit('close')"
  >
    <div
      class="modal-content"
      dir="rtl"
    >
      <div class="modal-header">
        <h3 class="modal-title">
          تصدير المستند
        </h3>
        <button
          class="modal-close"
          @click="emit('close')"
        >
          ✖️
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">صيغة التصدير</label>
          <select
            v-model="selectedFormat"
            class="form-select"
          >
            <option
              v-for="format in formats"
              :key="format.value"
              :value="format.value"
            >
              {{ format.label }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-checkbox">
            <input
              v-model="includeFootnotes"
              type="checkbox"
            >
            <span>تضمين الحواشي</span>
          </label>
        </div>

        <div class="form-group">
          <label class="form-checkbox">
            <input
              v-model="includeImages"
              type="checkbox"
            >
            <span>تضمين الصور</span>
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button
          class="btn btn-secondary"
          @click="emit('close')"
        >
          إلغاء
        </button>
        <button
          class="btn btn-primary"
          @click="handleExport"
        >
          تصدير
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
    max-width: 500px;
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
    transition: color 0.2s;
}

.modal-close:hover {
    color: #111827;
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

.form-select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
}

.form-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.form-checkbox input {
    width: 1rem;
    height: 1rem;
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
    transition: all 0.2s;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    border: none;
}

.btn-primary:hover {
    background: #2563eb;
}
</style>
