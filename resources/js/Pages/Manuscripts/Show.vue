<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    manuscript: Object,
});
</script>

<template>
    <AuthenticatedLayout :title="manuscript.title">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    تفاصيل المخطوطة: {{ manuscript.title }}
                </h2>
                <div class="flex space-x-2 space-x-reverse">
                    <Link
                        :href="route('manuscripts.edit', manuscript.slug)"
                        class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 transition duration-150 ease-in-out"
                    >
                        تعديل
                    </Link>
                    <Link
                        :href="route('manuscripts.index')"
                        class="px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 transition duration-150 ease-in-out"
                    >
                        العودة للقائمة
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">المعلومات الأساسية</h3>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">العنوان</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ manuscript.title }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Slug</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ manuscript.slug }}</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div>
                                <div v-if="manuscript.cover_path" class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">الغلاف</h3>
                                    <img :src="'/storage/' + manuscript.cover_path" alt="Cover" class="w-full max-w-md h-auto rounded-lg shadow-md object-cover">
                                </div>

                                <div v-if="manuscript.file_path" class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">الملف</h3>
                                    <a :href="'/storage/' + manuscript.file_path" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        تحميل / قراءة PDF
                                    </a>
                                </div>
                                
                                <div v-if="manuscript.description" class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">الوصف</h3>
                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ manuscript.description }}</p>
                                </div>

                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">التصنيفات والوسوم</h3>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">الوسوم</dt>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="tag in manuscript.tags" :key="tag.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ tag.name }}
                                        </span>
                                        <span v-if="!manuscript.tags.length" class="text-sm text-gray-400 italic">لا يوجد وسوم</span>
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">التصنيفات</dt>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="category in manuscript.categories" :key="category.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ category.name }}
                                        </span>
                                        <span v-if="!manuscript.categories.length" class="text-sm text-gray-400 italic">لا يوجد تصنيفات</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-gray-200 dark:border-gray-700 pt-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">التعليقات</h3>
                            <div class="space-y-4">
                                <div v-for="comment in manuscript.comments" :key="comment.id" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-semibold text-sm text-indigo-600 dark:text-indigo-400">{{ comment.user?.name || 'مستخدم' }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ new Date(comment.created_at).toLocaleDateString('ar-EG') }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ comment.content }}</p>
                                </div>
                                <p v-if="!manuscript.comments.length" class="text-sm text-gray-400 italic">لا يوجد تعليقات بعد</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
