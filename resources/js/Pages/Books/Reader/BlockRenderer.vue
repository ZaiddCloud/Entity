<template>
    <div class="block-container group relative">
        <!-- Render Paragraph -->
        <div v-if="block.type === 'paragraph'" class="content-text">
            <p class="text-lg md:text-xl leading-[2] text-slate-700 font-serif whitespace-pre-wrap">
                <template v-for="(part, index) in parsedBody" :key="index">
                    <span v-if="part.type === 'text'">{{ part.value }}</span>
                    <sup 
                        v-else-if="part.type === 'footnote'"
                        class="mx-0.5 px-1 bg-amber-50 text-amber-700 rounded cursor-help font-bold text-xs hover:bg-amber-200 transition-colors relative group/note"
                    >
                        {{ part.marker }}
                        <!-- Tooltip/Popup for footnote -->
                        <span class="invisible group-hover/note:visible absolute bottom-full right-0 mb-2 w-64 bg-white border border-slate-200 shadow-2xl rounded-xl p-4 text-sm text-slate-600 z-50 animate-in fade-in slide-in-from-bottom-2">
                             <span class="block font-bold text-amber-700 mb-2 border-b border-amber-100 pb-1">هامش:</span>
                             {{ getFootnote(part.marker) }}
                        </span>
                    </sup>
                </template>
            </p>
        </div>

        <!-- Render Verse (Poetry) -->
        <div v-else-if="block.type === 'verse'" class="max-w-xl mx-auto py-4 border-y border-slate-100/50 bg-slate-50/30 rounded-lg">
            <div class="flex flex-col md:flex-row justify-between items-center text-center gap-4 px-8 group-hover:scale-[1.01] transition-transform duration-500">
                <span class="text-xl md:text-2xl font-serif text-slate-800 leading-relaxed">{{ block.first_part }}</span>
                <div class="w-8 h-px bg-slate-300 hidden md:block"></div>
                <span class="text-xl md:text-2xl font-serif text-slate-800 leading-relaxed">{{ block.second_part }}</span>
            </div>
        </div>

        <!-- Annotations (Side Comments) -->
        <div v-if="comments.length > 0" class="mt-4 flex flex-col gap-2">
            <div 
                v-for="(note, idx) in comments" 
                :key="idx"
                class="bg-blue-50/50 border-r-2 border-blue-400 p-3 text-sm text-blue-800 rounded-l-lg hover:bg-blue-50 transition-colors"
            >
                <div class="flex items-center gap-2 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                    <span class="font-bold text-xs uppercase">{{ note.author || 'تعليق' }}</span>
                </div>
                {{ note.content }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    block: Object
});

const comments = computed(() => {
    return (props.block.annotations || []).filter(a => a.type === 'comment' || a.type === 'commentary');
});

const getFootnote = (marker) => {
    const note = (props.block.annotations || []).find(a => a.type === 'footnote' && a.marker === marker);
    return note ? note.content : '...';
};

// Very simple parser for markers in text like [1], [2]
const parsedBody = computed(() => {
    const body = props.block.body || '';
    if (!body) return [];
    
    const parts = [];
    const regex = /\[(\d+)\]/g;
    let lastIndex = 0;
    let match;

    while ((match = regex.exec(body)) !== null) {
        // Text before match
        if (match.index > lastIndex) {
            parts.push({ type: 'text', value: body.substring(lastIndex, match.index) });
        }
        // The marker match
        parts.push({ type: 'footnote', marker: `[${match[1]}]` });
        lastIndex = regex.lastIndex;
    }

    // Remaining text
    if (lastIndex < body.length) {
        parts.push({ type: 'text', value: body.substring(lastIndex) });
    }

    return parts;
});
</script>

<style scoped>
.block-container {
    transition: transform 0.3s ease;
}
.content-text p {
    text-shadow: 0 1px 1px rgba(255,255,255,0.8);
}
</style>
