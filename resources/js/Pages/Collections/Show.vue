<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    collection: Object,
});
</script>

<template>
    <AuthenticatedLayout :title="collection.name">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    تفاصيل المجموعة: {{ collection.name }}
                </h2>
                <div class="flex space-x-2 space-x-reverse">
                    <Link
                        :href="route('collections.edit', collection.id)"
                        class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 transition duration-150 ease-in-out"
                    >
                        تعديل
                    </Link>
                    <Link
                        :href="route('collections.index')"
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
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">المعلومات الأساسية</h3>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-3">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الاسم</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ collection.name }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">المالك</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ collection.user?.name || '-' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الخصوصية</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        <span v-if="collection.is_public">عامة</span>
                                        <span v-else>خاصة</span>
                                    </dd>
                                </div>
                                <div class="sm:col-span-3">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الوصف</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ collection.description || 'لا يوجد وصف' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 text-center">المحتويات</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-4 border-b pb-2">الكتب</h4>
                                    <ul class="space-y-2">
                                        <li v-for="book in collection.books" :key="book.id">
                                            <Link :href="route('books.show', book.id)" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">{{ book.title }}</Link>
                                        </li>
                                        <li v-if="!collection.books.length" class="text-xs text-gray-400 italic">لا يوجد</li>
                                    </ul>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-4 border-b pb-2">الصوتيات</h4>
                                    <ul class="space-y-2">
                                        <li v-for="audio in collection.audio" :key="audio.id">
                                            <Link :href="route('audio.show', audio.id)" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">{{ audio.title }}</Link>
                                        </li>
                                        <li v-if="!collection.audio.length" class="text-xs text-gray-400 italic">لا يوجد</li>
                                    </ul>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-4 border-b pb-2">المرئيات</h4>
                                    <ul class="space-y-2">
                                        <li v-for="video in collection.videos" :key="video.id">
                                            <Link :href="route('videos.show', video.id)" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">{{ video.title }}</Link>
                                        </li>
                                        <li v-if="!collection.videos.length" class="text-xs text-gray-400 italic">لا يوجد</li>
                                    </ul>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-4 border-b pb-2">المخطوطات</h4>
                                    <ul class="space-y-2">
                                        <li v-for="manuscript in collection.manuscripts" :key="manuscript.id">
                                            <Link :href="route('manuscripts.show', manuscript.id)" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">{{ manuscript.title }}</Link>
                                        </li>
                                        <li v-if="!collection.manuscripts.length" class="text-xs text-gray-400 italic">لا يوجد</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
