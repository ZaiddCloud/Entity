<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    activity: Object,
});

const translateActivity = (type) => {
    const map = {
        'created': 'إضافة جديد',
        'updated': 'تحديث بيانات',
        'deleted': 'حذف نهائي',
        'viewed': 'مشاهدة',
        'restored': 'استعادة'
    };
    return map[type] || type;
};

const formatValue = (val) => {
    if (val === null || val === undefined) return '-';
    if (typeof val === 'boolean') return val ? 'نعم' : 'لا';
    if (typeof val === 'object') return JSON.stringify(val);
    return val;
};
</script>

<template>
    <AuthenticatedLayout title="تفاصيل النشاط">
        <template #header>
            <div class="flex justify-between items-center" dir="rtl">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    تفاصيل النشاط: {{ translateActivity(activity.activity_type) }}
                </h2>
                <Link
                    :href="route('activities.index')"
                    class="px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 transition duration-150 ease-in-out"
                >
                    العودة للسجل
                </Link>
            </div>
        </template>

        <div class="py-12" dir="rtl">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl">
                    <div class="p-8 text-gray-900 dark:text-gray-100">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-1 border-l border-gray-100 dark:border-gray-700 pl-8">
                                <h3 class="text-lg font-bold text-purple-600 dark:text-purple-400 mb-6">معلومات الأساسية</h3>
                                <dl class="space-y-6">
                                    <div>
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">المستخدم</dt>
                                        <dd class="mt-1 text-sm font-bold text-gray-900 dark:text-white">{{ activity.user?.name || 'نظام' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">نوع الإجراء</dt>
                                        <dd class="mt-1">
                                            <span class="px-2 py-1 rounded bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold">
                                                {{ translateActivity(activity.activity_type) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">المحتوى المستهدف</dt>
                                        <dd class="mt-1 text-sm font-bold">{{ activity.entity_type.split('\\').pop() }}</dd>
                                    </div>
                                    <div v-if="activity.entity">
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">عنوان المحتوى</dt>
                                        <dd class="mt-1 text-sm font-bold text-purple-500">{{ activity.entity.title }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">التاريخ</dt>
                                        <dd class="mt-1 text-sm text-gray-500 dark:text-gray-400" dir="ltr">{{ new Date(activity.created_at).toLocaleString('ar-EG') }}</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">تفاصيل التغييرات</h3>
                                <div v-if="activity.changes" class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-800">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-right">
                                        <thead class="bg-gray-100 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">الحقل</th>
                                                <th scope="col" class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">القيمة السابقة</th>
                                                <th scope="col" class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">القيمة الجديدة</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                            <tr v-for="(val, key) in (activity.changes.after || activity.changes)" :key="key" class="hover:bg-white dark:hover:bg-gray-900 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700 dark:text-gray-300">{{ key }}</td>
                                                <td class="px-6 py-4 text-gray-400 line-through decoration-red-500/50 opacity-60">
                                                    {{ formatValue(activity.changes.before?.[key]) }}
                                                </td>
                                                <td class="px-6 py-4 text-green-600 dark:text-green-400 font-medium">
                                                    {{ formatValue(val) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="flex flex-col items-center justify-center py-12 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-gray-500 italic">لا توجد بيانات تغيير مسجلة لهذا النشاط (قد يكون مجرد مشاهدة)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
