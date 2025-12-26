<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    activity: Object,
});
</script>

<template>
    <AuthenticatedLayout title="تفاصيل النشاط">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    تفاصيل النشاط
                </h2>
                <Link
                    :href="route('activities.index')"
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
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">معلومات النشاط</h3>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">المستخدم</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ activity.user?.name || 'نظام' }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الإجراء</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ activity.action }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">نوع المحتوى</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ activity.subject_type }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">معرف المحتوى</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ activity.subject_id }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">التاريخ</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 text-left" dir="ltr">{{ new Date(activity.created_at).toLocaleString('ar-EG') }}</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">التغييرات</h3>
                                <div v-if="activity.changes" class="bg-white dark:bg-gray-900 shadow overflow-hidden border-b border-gray-200 dark:border-gray-700 sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-right">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الحقل</th>
                                                <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">القيمة السابقة</th>
                                                <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">القيمة الجديدة</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                            <tr v-for="(val, key) in (activity.changes.after || activity.changes)" :key="key">
                                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100">{{ key }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400 line-through decoration-red-500 opacity-75">
                                                    {{ activity.changes.before?.[key] ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-green-600 dark:text-green-400">
                                                    {{ val }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="text-sm text-gray-500 italic">لا توجد تفاصيل إضافية</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
