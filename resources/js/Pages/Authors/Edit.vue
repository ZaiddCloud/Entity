<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    author: Object,
});

const form = useForm({
    name: props.author.name,
    bio: props.author.bio,
    birth_year: props.author.birth_year,
    death_year: props.author.death_year,
});

const submit = () => {
    form.put(route('authors.update', props.author.id));
};
</script>

<template>
    <Head title="تعديل بيانات المؤلف" />

    <AuthenticatedLayout title="تعديل بيانات المؤلف">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-black text-2xl dark:text-white leading-tight">تعديل بيانات المؤلف</h2>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#0a0a0a] overflow-hidden shadow-sm sm:rounded-[2.5rem] border border-gray-100 dark:border-white/5">
                <div class="p-8 sm:p-12">
                    <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
                        <div>
                            <label for="name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">اسم المؤلف</label>
                            <input
                                id="name"
                                name="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-2xl shadow-sm"
                                required
                                autofocus
                            />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label for="birth_year" class="block font-medium text-sm text-gray-700 dark:text-gray-300">سنة الميلاد</label>
                                <input
                                    id="birth_year"
                                    name="birth_year"
                                    v-model="form.birth_year"
                                    type="number"
                                    class="mt-1 block w-full border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-2xl shadow-sm"
                                />
                                <div v-if="form.errors.birth_year" class="text-red-500 text-sm mt-1">{{ form.errors.birth_year }}</div>
                            </div>

                            <div>
                                <label for="death_year" class="block font-medium text-sm text-gray-700 dark:text-gray-300">سنة الوفاة (اختياري)</label>
                                <input
                                    id="death_year"
                                    name="death_year"
                                    v-model="form.death_year"
                                    type="number"
                                    class="mt-1 block w-full border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-2xl shadow-sm"
                                />
                                <div v-if="form.errors.death_year" class="text-red-500 text-sm mt-1">{{ form.errors.death_year }}</div>
                            </div>
                        </div>

                        <div>
                            <label for="bio" class="block font-medium text-sm text-gray-700 dark:text-gray-300">نبذة عن المؤلف</label>
                            <textarea
                                id="bio"
                                name="bio"
                                v-model="form.bio"
                                class="mt-1 block w-full border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-2xl shadow-sm min-h-[150px]"
                            ></textarea>
                            <div v-if="form.errors.bio" class="text-red-500 text-sm mt-1">{{ form.errors.bio }}</div>
                        </div>

                        <div class="flex items-center gap-4 pt-4">
                            <button
                                type="submit"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                حفظ التغييرات
                            </button>
                            <Link :href="route('authors.index')" class="text-sm font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                إلغاء
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
