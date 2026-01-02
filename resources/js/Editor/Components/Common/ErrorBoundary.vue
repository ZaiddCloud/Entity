<script setup>
import { onErrorCaptured, ref } from 'vue'

const error = ref(null)

onErrorCaptured((err) => {
    error.value = err
    console.error('Component Error:', err)
    return false
})
</script>

<template>
    <div v-if="error" class="error-boundary">
        <div class="error-boundary__content">
            <h3 class="error-boundary__title">⚠️ حدث خطأ</h3>
            <p class="error-boundary__message">{{ error.message }}</p>
            <button 
                class="error-boundary__button"
                @click="error = null"
            >
                إعادة المحاولة
            </button>
        </div>
    </div>
    <slot v-else />
</template>

<style scoped>
.error-boundary {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    padding: 2rem;
}

.error-boundary__content {
    text-align: center;
    max-width: 400px;
}

.error-boundary__title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #dc2626;
    margin-bottom: 0.5rem;
}

.error-boundary__message {
    color: #6b7280;
    margin-bottom: 1rem;
}

.error-boundary__button {
    padding: 0.5rem 1rem;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
}

.error-boundary__button:hover {
    background: #2563eb;
}
</style>
