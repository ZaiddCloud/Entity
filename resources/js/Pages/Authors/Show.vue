<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    author: Object,
});
</script>

<template>
    <Head :title="author.name" />

    <AuthenticatedLayout :title="author.name">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-black text-2xl dark:text-white leading-tight">{{ author.name }}</h2>
                    <p class="text-xs text-gray-400 font-bold mt-1">
                        {{ author.birth_year ? 'ولد عام ' + author.birth_year : '' }}
                        {{ (author.birth_year && author.death_year) ? ' - ' : '' }}
                        {{ author.death_year ? 'توفى عام ' + author.death_year : (author.birth_year ? ' (على قيد الحياة)' : '') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('authors.edit', author.id)"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold text-xs transition-all shadow-lg shadow-indigo-500/20"
                    >
                        تعديل البيانات
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Info Card -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white dark:bg-[#0a0a0a] overflow-hidden shadow-sm sm:rounded-[2.5rem] border border-gray-100 dark:border-white/5 p-8">
                        <h3 class="font-black text-lg mb-4 dark:text-white">نبذة تعريفية</h3>
                        <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed text-sm">
                            {{ author.bio || 'لا توجد نبذة تعريفية متاحة حالياً.' }}
                        </div>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-[#0a0a0a] overflow-hidden shadow-sm sm:rounded-[2.5rem] border border-gray-100 dark:border-white/5 p-6">
                        <h3 class="font-black text-sm mb-6 text-gray-400 uppercase tracking-widest">إحصائيات الإنتاج</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-white/5 rounded-2xl">
                                <span class="font-bold text-sm dark:text-gray-300">الكتب</span>
                                <span class="font-black text-indigo-600 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1 rounded-lg text-xs">{{ author.books_count || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-white/5 rounded-2xl">
                                <span class="font-bold text-sm dark:text-gray-300">الصوتيات</span>
                                <span class="font-black text-sky-600 bg-sky-50 dark:bg-sky-500/10 px-3 py-1 rounded-lg text-xs">{{ author.audios_count || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-white/5 rounded-2xl">
                                <span class="font-bold text-sm dark:text-gray-300">المرئيات</span>
                                <span class="font-black text-purple-600 bg-purple-50 dark:bg-purple-500/10 px-3 py-1 rounded-lg text-xs">{{ author.videos_count || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-white/5 rounded-2xl">
                                <span class="font-bold text-sm dark:text-gray-300">المخطوطات</span>
                                <span class="font-black text-amber-600 bg-amber-50 dark:bg-amber-500/10 px-3 py-1 rounded-lg text-xs">{{ author.manuscripts_count || 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
