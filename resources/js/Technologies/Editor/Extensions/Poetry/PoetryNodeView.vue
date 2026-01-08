<script setup>
import { NodeViewWrapper } from '@tiptap/vue-3'

const props = defineProps({
    node: Object,
    updateAttributes: Function,
    deleteNode: Function
})
</script>

<template>
    <NodeViewWrapper class="poetry-node">
        <div class="poetry-container">
            <div class="poetry-verse">
                <div class="poetry-sadr">{{ node.attrs.sadr }}</div>
                <div class="poetry-separator">***</div>
                <div class="poetry-ajuz">{{ node.attrs.ajuz }}</div>
            </div>
            <div v-if="node.attrs.poet || node.attrs.source" class="poetry-meta">
                <span v-if="node.attrs.poet" class="poetry-poet">{{ node.attrs.poet }}</span>
                <span v-if="node.attrs.source" class="poetry-source">{{ node.attrs.source }}</span>
            </div>
        </div>
    </NodeViewWrapper>
</template>

<style scoped>
.poetry-node {
    margin: 2rem 0;
}

.poetry-container {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-right: 4px solid #f59e0b;
    border-radius: 8px;
    padding: 1.5rem;
    font-family: 'Amiri', serif;
}

.poetry-verse {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    font-size: 1.25rem;
    line-height: 2;
    color: #78350f;
}

.poetry-sadr,
.poetry-ajuz {
    flex: 1;
    text-align: center;
}

.poetry-separator {
    color: #f59e0b;
    font-weight: bold;
}

.poetry-meta {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #fbbf24;
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
    color: #92400e;
}

.poetry-poet {
    font-weight: 600;
}

.poetry-source {
    font-style: italic;
}
</style>
