<script setup>
import { computed } from 'vue';

const props = defineProps({
    content: Object,
    html: String,
    fontSize: Number,
});

const contentStyle = computed(() => ({
    fontSize: `${props.fontSize}px`,
    lineHeight: '1.8',
}));

</script>

<template>
    <div class="max-w-4xl mx-auto px-6 py-12 md:py-20 animate-fade-in">
        <!-- Tiptap Render Area -->
        <article 
            class="prose prose-lg dark:prose-invert max-w-none reader-content"
            :style="contentStyle"
            v-html="html"
        >
        </article>

        <!-- Skeleton / Empty State if no content -->
        <div v-if="!html" class="flex flex-col items-center justify-center py-20 opacity-20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <p class="text-xl font-bold">لا يوجد محتوى متوفر</p>
        </div>
    </div>
</template>

<style>
.reader-content p {
    margin-bottom: 1.5em;
    text-align: justify;
}

.reader-content h1, .reader-content h2, .reader-content h3 {
    font-weight: bold;
    margin-top: 2em;
    margin-bottom: 1em;
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Custom RTL Typography tweaks for prose */
[dir="rtl"] .prose {
    text-align: right;
}
</style>
