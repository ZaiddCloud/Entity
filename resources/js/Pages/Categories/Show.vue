<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    category: Object,
});
</script>

<template>
    <AuthenticatedLayout :title="category.name">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    تفاصيل التصنيف: {{ category.name }}
                </h2>
                <div class="flex space-x-2 space-x-reverse">
                    <Link
                        :href="route('categories.edit', category.id)"
                        class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 transition duration-150 ease-in-out"
                    >
                        تعديل
                    </Link>
                    <Link
                        :href="route('categories.index')"
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
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الاسم</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ category.name }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">التصنيف الأب</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ category.parent?.name || 'لا يوجد' }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الوصف</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ category.description || 'لا يوجد وصف' }}</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">التصنيفات الفرعية</h3>
                                <ul class="list-disc list-inside space-y-2">
                                    <li v-for="child in category.children" :key="child.id">
                                        <Link :href="route('categories.show', child.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">{{ child.name }}</Link>
                                    </li>
                                    <li v-if="!category.children.length" class="text-sm text-gray-400 italic">لا يوجد تصنيفات فرعية</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-gray-200 dark:border-gray-700 pt-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">المحتويات المرتبطة</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-2">الكتب</h4>
                                    <p class="text-2xl">{{ category.books.length }}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-2">الصوتيات</h4>
                                    <p class="text-2xl">{{ category.audio.length }}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-2">المرئيات</h4>
                                    <p class="text-2xl">{{ category.videos.length }}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-2">المخطوطات</h4>
                                    <p class="text-2xl">{{ category.manuscripts.length }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
