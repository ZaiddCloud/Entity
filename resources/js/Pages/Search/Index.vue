<template>
    <AuthenticatedLayout title="نتائج البحث">
        <template #header>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <div>
                    <h2 class="font-black text-2xl dark:text-white leading-tight">نتائج البحث عن: "{{ term }}"</h2>
                    <p class="text-xs text-gray-400 font-bold mt-1">عرض كافة النتائج المطابقة لطلباتك عبر كافة أقسام المكتبة</p>
                </div>
            </div>
        </template>

        <div class="space-y-12">
            <div v-if="hasResults" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Books Section -->
                <div v-if="results.books?.length" class="space-y-4">
                    <h3 class="flex items-center gap-2 text-sm font-black text-gray-400 uppercase tracking-widest px-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        الكتب ({{ results.books.length }})
                    </h3>
                    <div class="space-y-3">
                        <Link v-for="item in results.books" :key="item.id" :href="route('books.show', item.id)" class="block bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-2xl p-4 hover:border-indigo-500/50 hover:shadow-lg transition-all">
                            <div class="text-sm font-black dark:text-white">{{ item.title }}</div>
                            <div class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">ID: {{ item.id.substring(0, 8) }}...</div>
                        </Link>
                    </div>
                </div>

                <!-- Authors Section -->
                <div v-if="results.authors?.length" class="space-y-4">
                    <h3 class="flex items-center gap-2 text-sm font-black text-gray-400 uppercase tracking-widest px-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        المؤلفون ({{ results.authors.length }})
                    </h3>
                    <div class="space-y-3">
                        <Link v-for="item in results.authors" :key="item.id" :href="route('authors.show', item.id)" class="block bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-2xl p-4 hover:border-teal-500/50 hover:shadow-lg transition-all">
                            <div class="text-sm font-black dark:text-white">{{ item.name }}</div>
                            <div class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">ID: {{ item.id.substring(0, 8) }}...</div>
                        </Link>
                    </div>
                </div>

                <!-- Media (Audio/Video) Section -->
                <div v-if="results.audios?.length || results.videos?.length" class="space-y-4">
                    <h3 class="flex items-center gap-2 text-sm font-black text-gray-400 uppercase tracking-widest px-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        الوسائط المتعددة ({{ (results.audios?.length || 0) + (results.videos?.length || 0) }})
                    </h3>
                    <div class="space-y-3">
                        <Link v-for="item in results.audios" :key="'audio-'+item.id" :href="route('audios.show', item.id)" class="block bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-2xl p-4 hover:border-sky-500/50 hover:shadow-lg transition-all">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-black dark:text-white">{{ item.title }}</div>
                                <span class="text-[8px] font-black px-1.5 py-0.5 bg-sky-50 dark:bg-sky-500/10 text-sky-600 rounded">صوت</span>
                            </div>
                        </Link>
                        <Link v-for="item in results.videos" :key="'video-'+item.id" :href="route('videos.show', item.id)" class="block bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-2xl p-4 hover:border-purple-500/50 hover:shadow-lg transition-all">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-black dark:text-white">{{ item.title }}</div>
                                <span class="text-[8px] font-black px-1.5 py-0.5 bg-purple-50 dark:bg-purple-500/10 text-purple-600 rounded">مرئي</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-20 px-4 text-center">
                <div class="w-24 h-24 rounded-[2rem] bg-gray-50 dark:bg-white/5 flex items-center justify-center text-gray-300 mb-6">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-xl font-black dark:text-white mb-2">عذراً، لم نجد ما تبحث عنه</h3>
                <p class="text-xs text-gray-400 font-bold max-w-sm">حاول البحث باستخدام كلمات مفتاحية أخرى أو التأكد من سلامة النص المدخل</p>
                <Link href="/dashboard" class="mt-8 px-6 py-2 bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 rounded-xl text-[10px] font-black transition-all uppercase tracking-widest">العودة للرئيسية</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    results: Object,
    term: String,
});

const hasResults = computed(() => {
    return Object.values(props.results).some(arr => arr.length > 0);
});
</script>
