<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    tag: Object,
});
</script>

<template>
    <AuthenticatedLayout :title="tag.name">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    تفاصيل الوسم: {{ tag.name }}
                </h2>
                <div class="flex space-x-2 space-x-reverse">
                    <Link
                        :href="route('tags.edit', tag.id)"
                        class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 transition duration-150 ease-in-out"
                    >
                        تعديل
                    </Link>
                    <Link
                        :href="route('tags.index')"
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
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ tag.name }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">النوع</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ tag.type || 'عام' }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Slug</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ tag.slug }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-gray-200 dark:border-gray-700 pt-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">المحتويات المرتبطة</h3>
                            <div class="space-y-8">
                                <div v-if="tag.books.length">
                                    <h4 class="font-semibold mb-2">الكتب</h4>
                                    <ul class="list-disc list-inside">
                                        <li v-for="book in tag.books" :key="book.id">
                                            <Link :href="route('books.show', book.slug)" class="text-indigo-600 hover:text-indigo-900">{{ book.title }}</Link>
                                        </li>
                                    </ul>
                                </div>
                                <div v-if="tag.audio.length">
                                    <h4 class="font-semibold mb-2">الصوتيات</h4>
                                    <ul class="list-disc list-inside">
                                        <li v-for="audio in tag.audio" :key="audio.id">
                                            <Link :href="route('audios.show', audio.slug)" class="text-indigo-600 hover:text-indigo-900">{{ audio.title }}</Link>
                                        </li>
                                    </ul>
                                </div>
                                <div v-if="tag.videos.length">
                                    <h4 class="font-semibold mb-2">المرئيات</h4>
                                    <ul class="list-disc list-inside">
                                        <li v-for="video in tag.videos" :key="video.id">
                                            <Link :href="route('videos.show', video.slug)" class="text-indigo-600 hover:text-indigo-900">{{ video.title }}</Link>
                                        </li>
                                    </ul>
                                </div>
                                <p v-if="!tag.books.length && !tag.audio.length && !tag.videos.length" class="text-sm text-gray-400 italic">لا يوجد محتويات مرتبطة</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
