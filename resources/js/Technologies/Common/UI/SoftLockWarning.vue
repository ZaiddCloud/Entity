<script setup>
import { computed } from 'vue';

const props = defineProps({
    lockedBy: {
        type: Object,
        default: null
    },
    show: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['dismiss']);

const userName = computed(() => props.lockedBy?.user_name || 'مستخدم آخر');
</script>

<template>
    <div v-if="show && lockedBy" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4 rounded-r-lg shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="mr-3 flex-1">
                <h3 class="text-sm font-medium text-yellow-800">
                    ⚠️ هذا القسم قيد التحرير
                </h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>
                        <strong>{{ userName }}</strong> يحرر هذا القسم حالياً. 
                        قد تحدث تعارضات إذا قمت بالتحرير في نفس الوقت.
                    </p>
                </div>
                <div class="mt-3">
                    <button 
                        @click="emit('dismiss')" 
                        class="text-xs font-medium text-yellow-800 hover:text-yellow-900 underline"
                    >
                        فهمت، سأحرر بحذر
                    </button>
                </div>
            </div>
            <div class="flex-shrink-0 mr-auto">
                <button 
                    @click="emit('dismiss')" 
                    class="inline-flex text-yellow-400 hover:text-yellow-500"
                >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
