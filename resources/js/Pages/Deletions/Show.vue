<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    deletion: Object,
});
</script>

<template>
    <AuthenticatedLayout title="تفاصيل الحذف">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    تفاصيل الحذف
                </h2>
                <Link
                    :href="route('deletions.index')"
                    class="px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 transition duration-150 ease-in-out"
                >
                    العودة للسجل
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">معلومات الحذف</h3>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">المستخدم الذي حذف</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ deletion.user?.name || 'نظام' }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">نوع المحتوى</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ deletion.subject_type }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">معرف المحتوى</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ deletion.subject_id }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">تاريخ الحذف</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 text-left" dir="ltr">{{ new Date(deletion.created_at).toLocaleString('ar-EG') }}</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">بيانات المحتوى المحذوف</h3>
                                <div v-if="deletion.data" class="bg-white dark:bg-gray-900 shadow overflow-hidden border-b border-gray-200 dark:border-gray-700 sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-right">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الحقل</th>
                                                <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">القيمة</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                            <tr v-for="(val, key) in deletion.data" :key="key">
                                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100">{{ key }}</td>
                                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 break-all">
                                                    {{ val }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="text-sm text-gray-500 italic">لا توجد بيانات محفوظة للمحتوى المحذوف</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
