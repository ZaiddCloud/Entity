<template>
    <div class="overflow-hidden bg-white dark:bg-[#0a0a0a] rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-sm">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-white/5">
                <slot />
            </table>
        </div>
        <div v-if="$slots.pagination" class="px-8 py-6 bg-gray-50/50 dark:bg-white/2 border-t border-gray-100 dark:border-white/5">
            <slot name="pagination" />
        </div>
    </div>
</template>
