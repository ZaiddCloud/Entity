<script setup>
import { NodeViewWrapper } from '@tiptap/vue-3'

const props = defineProps({
    node: Object,
    updateAttributes: Function,
    deleteNode: Function
})
</script>

<template>
    <NodeViewWrapper class="quranic-verse-node">
        <div class="quranic-container">
            <div class="quranic-icon">📿</div>
            <div class="quranic-content">
                <div class="quranic-text">{{ node.attrs.text }}</div>
                <div v-if="node.attrs.surah || node.attrs.ayah" class="quranic-reference">
                    <span v-if="node.attrs.surah">{{ node.attrs.surah }}</span>
                    <span v-if="node.attrs.ayah"> - آية {{ node.attrs.ayah }}</span>
                </div>
            </div>
        </div>
    </NodeViewWrapper>
</template>

<style scoped>
.quranic-verse-node {
    margin: 2rem 0;
}

.quranic-container {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-right: 4px solid #3b82f6;
    border-radius: 8px;
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.quranic-icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.quranic-content {
    flex: 1;
}

.quranic-text {
    font-family: 'Amiri', serif;
    font-size: 1.5rem;
    line-height: 2.5;
    color: #1e3a8a;
    text-align: center;
    margin-bottom: 1rem;
}

.quranic-reference {
    text-align: center;
    font-size: 0.875rem;
    color: #1e40af;
    font-weight: 600;
    padding-top: 0.75rem;
    border-top: 1px solid #93c5fd;
}
</style>
