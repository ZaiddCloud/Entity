<script setup>
defineProps({
    isPinned: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['pin', 'minimize', 'maximize', 'close'])
</script>

<template>
    <div class="window-controls flex items-center gap-2">
        <!-- زر التثبيت -->
        <button 
            class="control-btn pin-btn"
            :class="{ 'pinned': isPinned }"
            @click="emit('pin')"
            title="تثبيت شريط الأدوات"
        >
            📌
        </button>

        <div class="w-px h-4 bg-gray-300"></div>

        <!-- أزرار التحكم بالنافذة -->
        <button 
            class="control-btn minimize-btn"
            @click="emit('minimize')"
            title="تصغير"
        >
            ➖
        </button>

        <button 
            class="control-btn maximize-btn"
            @click="emit('maximize')"
            title="تكبير"
        >
            ⬜
        </button>

        <button 
            class="control-btn close-btn"
            @click="emit('close')"
            title="إغلاق"
        >
            ✖️
        </button>
    </div>
</template>

<style scoped>
.window-controls {
    -webkit-app-region: no-drag;
}

.control-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: none;
    background: transparent;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 12px;
}

.control-btn:hover {
    background: #f3f4f6;
}

.pin-btn.pinned {
    background: #dbeafe;
    color: #2563eb;
}

.close-btn:hover {
    background: #fee2e2;
    color: #dc2626;
}

.minimize-btn:hover,
.maximize-btn:hover {
    background: #e0e7ff;
}
</style>
