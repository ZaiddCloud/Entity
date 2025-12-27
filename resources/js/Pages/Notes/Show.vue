<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    note: Object,
});

const deleteNote = () => {
    if (confirm('هل أنت متأكد من حذف هذه الملاحظة؟')) {
        router.delete(route('notes.destroy', props.note.id));
    }
};
</script>

<template>
    <AuthenticatedLayout title="عرض ملاحظة">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    عرض الملاحظة
                </h2>
                <div class="flex gap-4">
                    <Link
                        :href="route('notes.edit', note.id)"
                        class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                    >
                        تعديل
                    </Link>
                    <button
                        @click="deleteNote"
                        class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700"
                    >
                        حذف
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6">
                            <h3 class="text-lg font-bold mb-2">المحتوى</h3>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ note.content }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">المستخدم</p>
                                <p class="font-medium">{{ note.user?.name || 'غير معروف' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">تاريخ الإنشاء</p>
                                <p class="font-medium">{{ new Date(note.created_at).toLocaleString('ar-EG') }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <Link :href="route('notes.index')" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                ← العودة إلى القائمة
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
