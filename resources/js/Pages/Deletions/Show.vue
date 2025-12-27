<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    deletion: Object,
});

const formatValue = (val) => {
    if (val === null || val === undefined) return '-';
    if (typeof val === 'boolean') return val ? 'نعم' : 'لا';
    if (typeof val === 'object') return JSON.stringify(val);
    return val;
};
</script>

<template>
    <AuthenticatedLayout title="تفاصيل الحذف">
        <template #header>
            <div class="flex justify-between items-center" dir="rtl">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    تفاصيل الحذف
                </h2>
                <div class="flex gap-4">
                    <Link
                        :href="route('deletions.index')"
                        class="px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 transition duration-150 ease-in-out"
                    >
                        العودة للسجل
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12" dir="rtl">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl">
                    <div class="p-8 text-gray-900 dark:text-gray-100">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-1 border-l border-gray-100 dark:border-gray-700 pl-8">
                                <h3 class="text-lg font-bold text-red-600 dark:text-red-400 mb-6">سجل الحذف</h3>
                                <dl class="space-y-6">
                                    <div>
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">المستخدم الذي حذف</dt>
                                        <dd class="mt-1 text-sm font-bold text-gray-900 dark:text-white">{{ deletion.user?.name || 'نظام' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">نوع المحتوى</dt>
                                        <dd class="mt-1 text-sm font-bold">{{ deletion.entity_type.split('\\').pop() }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">المعرف السابق</dt>
                                        <dd class="mt-1 text-xs font-mono text-gray-400">{{ deletion.entity_id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">تاريخ الحذف</dt>
                                        <dd class="mt-1 text-sm text-gray-500 dark:text-gray-400" dir="ltr">{{ new Date(deletion.created_at).toLocaleString('ar-EG') }}</dd>
                                    </div>
                                    <div class="pt-4" v-if="deletion.reason">
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">سبب الحذف</dt>
                                        <dd class="mt-1 text-sm italic text-gray-600 dark:text-gray-400">{{ deletion.reason }}</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">البيانات التي تم الاحتفاظ بها</h3>
                                <div v-if="deletion.data" class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-800">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-right">
                                        <thead class="bg-gray-100 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">الحقل</th>
                                                <th scope="col" class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">القيمة</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                            <tr v-for="(val, key) in deletion.data" :key="key" class="hover:bg-white dark:hover:bg-gray-900 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700 dark:text-gray-300">{{ key }}</td>
                                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400 break-all">
                                                    {{ formatValue(val) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="flex flex-col items-center justify-center py-12 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <p class="text-gray-500 italic">لا توجد بيانات محفوظة للمحتوى المحذوف</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
