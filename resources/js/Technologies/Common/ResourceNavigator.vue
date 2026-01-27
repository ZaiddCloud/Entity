<script setup>
import { ref, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import debounce from 'lodash/debounce'

const props = defineProps({
    type: {
        type: String,
        required: true // 'manuscript', 'audio', 'video'
    },
    currentId: {
        type: [String, Number],
        default: null
    }
})

const isOpen = ref(false)
const isLoading = ref(false)
const items = ref([])
const searchQuery = ref('')
const dropdownRef = ref(null)

// Map editor type to resource route name
const resourceRoutes = {
    'manuscript': 'manuscripts',
    'audio': 'audios',
    'video': 'videos'
}

const fetchItems = debounce(async () => {
    isLoading.value = true
    try {
        const resourceName = resourceRoutes[props.type]
        console.log('Fetching resources for:', resourceName)
        
        const response = await axios.get(route(`${resourceName}.index`), {
            params: {
                search: searchQuery.value,
                per_page: 20
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json' 
            }
        })
        
        // Handle Inertia response structure
        // Inertia response: { component: '...', props: { manuscripts: { data: [...] }, ... } }
        let data = response.data
        if (data.props && data.props[resourceName]) {
            items.value = data.props[resourceName].data || data.props[resourceName]
        } else if (data.data) {
             // Standard API pagination
            items.value = data.data
        } else {
            // Direct array
            items.value = Array.isArray(data) ? data : []
        }
        console.log('Fetched items:', items.value.length)

    } catch (error) {
        console.error('Failed to fetch resources', error)
    } finally {
        isLoading.value = false
    }
}, 300)

const toggleDropdown = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value && items.value.length === 0) {
        fetchItems()
    }
}

const selectItem = (item) => {
    isOpen.value = false
    // Navigate to the editor for this item
    // Route: studio.show { type, slug }
    router.visit(route('studio.show', { type: props.type, slug: item.slug }))
}

// Close on click outside
onMounted(() => {
    document.addEventListener('click', (e) => {
        if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
            isOpen.value = false
        }
    })
})

watch(searchQuery, () => {
    fetchItems()
})
</script>

<template>
  <div
    ref="dropdownRef"
    class="relative"
  >
    <!-- Trigger Button -->
    <button 
      class="flex items-center gap-2 px-3 py-1.5 rounded-md hover:bg-gray-100 transition-colors text-gray-600 hover:text-gray-900"
      :class="{ 'bg-gray-100 text-gray-900': isOpen }"
      @click="toggleDropdown"
    >
      <i class="fas fa-th-list text-xs" />
      <span class="text-[10px] font-bold uppercase tracking-wider">الكل</span>
    </button>

    <!-- Dropdown Menu -->
    <div 
      v-if="isOpen"
      class="absolute top-full right-0 mt-2 w-72 bg-white rounded-lg shadow-xl border border-gray-200 z-50 overflow-hidden"
    >
      <!-- Search Header -->
      <div class="p-2 border-b border-gray-100 bg-gray-50">
        <div class="relative">
          <i class="fas fa-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs" />
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="بحث..." 
            class="w-full pl-3 pr-9 py-1.5 text-xs border border-gray-200 rounded-md focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none bg-white font-ui"
            autofocus
          >
        </div>
      </div>

      <!-- List -->
      <div class="max-h-64 overflow-y-auto custom-scrollbar">
        <div
          v-if="isLoading"
          class="p-4 text-center text-gray-400"
        >
          <i class="fas fa-circle-notch fa-spin" />
        </div>
                
        <div
          v-else-if="items.length === 0"
          class="p-4 text-center text-gray-400 text-xs"
        >
          لا توجد نتائج
        </div>

        <div
          v-else
          class="py-1"
        >
          <button 
            v-for="item in items" 
            :key="item.id"
            class="w-full text-right px-4 py-2 text-xs hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-2 group border-b border-gray-50 last:border-0"
            :class="{ 'bg-blue-50/50 text-blue-600': item.id === currentId }"
            @click="selectItem(item)"
          >
            <span class="w-1 h-full absolute right-0 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity" />
            <i
              class="fas text-gray-400 group-hover:text-blue-500 transition-colors"
              :class="type === 'manuscript' ? 'fa-book' : 'fa-film'"
            />
            <span class="truncate font-medium">{{ item.title || item.name || 'بدون عنوان' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.font-ui {
    font-family: 'Inter', 'Noto Sans Arabic', sans-serif;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
