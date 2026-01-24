<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
  command: {
    type: Function,
    required: true,
  },
})

const selectedIndex = ref(0)
const containerRef = ref(null)

const selectItem = (index) => {
  const item = props.items[index]
  if (item) {
    props.command(item)
  }
}

const upHandler = () => {
    selectedIndex.value = ((selectedIndex.value + props.items.length) - 1) % props.items.length
}

const downHandler = () => {
    selectedIndex.value = (selectedIndex.value + 1) % props.items.length
}

const enterHandler = () => {
    selectItem(selectedIndex.value)
}

defineExpose({
    onKeyDown: ({ event }) => {
        if (event.key === 'ArrowUp') {
            upHandler()
            return true
        }
        if (event.key === 'ArrowDown') {
            downHandler()
            return true
        }
        if (event.key === 'Enter') {
            enterHandler()
            return true
        }
        return false
    }
})

watch(() => props.items, () => {
  selectedIndex.value = 0
})
</script>

<template>
  <div
    ref="containerRef"
    class="items"
  >
    <button
      v-for="(item, index) in items"
      :key="index"
      class="item"
      :class="{ 'is-selected': index === selectedIndex }"
      @click="selectItem(index)"
    >
      <i
        v-if="item.icon"
        :class="item.icon"
        class="icon"
      />
      <div class="label">
        {{ item.title }}
      </div>
    </button>
  </div>
</template>

<style scoped lang="scss">
.items {
  padding: 0.5rem;
  background: #FFF;
  border-radius: 0.5rem;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05), 0px 10px 20px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  font-size: 0.9rem;
  min-width: 12rem;
}

.item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  text-align: right;
  background: transparent;
  border: none;
  border-radius: 0.4rem;
  padding: 0.4rem 0.6rem;
  cursor: pointer;
  color: #374151;
  font-family: inherit;

  &.is-selected {
    background: #F3F4F6;
    color: #111827;
  }

  .icon {
    font-size: 1.1rem;
    color: #6B7280;
    
    &.is-selected {
        color: #111827;
    }
  }
  
  .label {
      flex: 1;
  }
}
</style>
