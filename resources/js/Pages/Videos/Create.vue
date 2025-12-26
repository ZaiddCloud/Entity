<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    description: '',
    cover: null,
    file: null,
});

const submit = () => {
    form.post(route('videos.store'));
};
</script>

<template>
    <AuthenticatedLayout title="إضافة فيديو جديد">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                إضافة فيديو جديد
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">العنوان</label>
                                <input
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    v-model="form.title"
                                    required
                                    autofocus
                                />
                                <div v-if="form.errors.title" class="mt-2 text-sm text-red-600">{{ form.errors.title }}</div>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">الوصف</label>
                                <textarea
                                    id="description"
                                    rows="4"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    v-model="form.description"
                                ></textarea>
                                <div v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</div>
                            </div>

                            <div>
                                <label for="cover" class="block text-sm font-medium text-gray-700 dark:text-gray-300">صورة الغلاف (اختياري)</label>
                                <input
                                    id="cover"
                                    type="file"
                                    class="mt-1 block w-full text-sm text-gray-500 hover:file:bg-indigo-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 transition"
                                    @input="form.cover = $event.target.files[0]"
                                    accept="image/*"
                                />
                                <div v-if="form.errors.cover" class="mt-2 text-sm text-red-600">{{ form.errors.cover }}</div>
                            </div>

                            <div>
                                <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ملف الفيديو</label>
                                <input
                                    id="file"
                                    type="file"
                                    class="mt-1 block w-full text-sm text-gray-500 hover:file:bg-indigo-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 transition"
                                    @input="form.file = $event.target.files[0]"
                                    accept="video/*"
                                />
                                <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full mt-2">
                                    {{ form.progress.percentage }}%
                                </progress>
                                <div v-if="form.errors.file" class="mt-2 text-sm text-red-600">{{ form.errors.file }}</div>
                            </div>

                            <div class="flex items-center justify-end">
                                <Link :href="route('videos.index')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline decoration-none mr-4">
                                    إلغاء
                                </Link>
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                                    :disabled="form.processing"
                                >
                                    حفظ
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
