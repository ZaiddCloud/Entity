<template>
    <div class="mb-1">
        <div 
            @click="handleClick"
            class="flex items-center group cursor-pointer py-2 px-3 rounded-xl transition-all duration-200 select-none relative"
            :class="[
                selectedId === item.id ? 'bg-amber-50 text-amber-900 border border-amber-200/50 shadow-sm' : 'hover:bg-slate-100 text-slate-600',
                isOpen ? 'mb-1' : ''
            ]"
            :style="{ paddingRight: (level * 16 + 12) + 'px' }"
        >
            <!-- Indentation Line for children -->
            <div v-if="level > 0" class="absolute right-4 top-0 bottom-0 w-px bg-slate-200/50"></div>

            <!-- Toggle Icon -->
            <div 
                v-if="hasChildren" 
                class="ml-2 w-5 h-5 flex items-center justify-center rounded-md transition-transform duration-300"
                :class="{ 'rotate-90': isOpen }"
                @click.stop="isOpen = !isOpen"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" />
                </svg>
            </div>
            <div v-else class="ml-2 w-5"></div>

            <!-- Type Icon -->
            <span class="ml-2 opacity-50 text-[10px] font-bold uppercase tracking-widest hidden group-hover:inline-block">
                {{ item.type[0] }}
            </span>

            <!-- Real Link -->
            <Link 
                :href="route('books.reader', [$page.props.book.slug, item.id])"
                class="text-sm font-medium flex-1 truncate" 
                :class="{ 'font-bold': hasChildren || selectedId === item.id }"
                preserve-scroll
                @click="handleClick"
            >
                {{ item.title }}
            </Link>

            <!-- Status Dots -->
            <div v-if="selectedId === item.id" class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div>
        </div>

        <!-- Recursive Children -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform -translate-y-2 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform -translate-y-2 opacity-0"
        >
            <div v-if="isOpen && hasChildren" class="overflow-hidden">
                <TreeItem 
                    v-for="child in children" 
                    :key="child.id" 
                    :item="child" 
                    :all-items="allItems"
                    :selected-id="selectedId"
                    :level="level + 1"
                    @select="$emit('select', $event)"
                />
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    item: Object,
    allItems: Array,
    selectedId: String,
    level: {
        type: Number,
        default: 0
    }
});

const page = usePage();
const emit = defineEmits(['select']);

const isOpen = ref(false);

const children = computed(() => {
    return props.allItems.filter(i => i.parent_id === props.item.id);
});

const hasChildren = computed(() => children.value.length > 0);

const handleClick = (e) => {
    // Prevent double execution if Link was clicked (Link has @click.stop="handleClick")
    // But actually, we want the toggle to happen.
    if (hasChildren.value) {
        isOpen.value = !isOpen.value;
    }
    
    // If clicking the row but not the link itself, trigger navigation manually
    if (!e.target.closest('a')) {
        router.visit(route('books.reader', [page.props.book.slug, props.item.id]), {
            preserveScroll: true
        });
    }

    emit('select', props.item);
};
</script>
