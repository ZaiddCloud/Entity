<template>
  <AuthenticatedLayout title="تفاصيل الحذف">
    <!-- Premium Hero Section -->
    <template #header>
      <div class="relative overflow-hidden bg-rose-700 rounded-[2.5rem] p-12 text-white shadow-2xl shadow-rose-900/40">
        <!-- Abstract Background Elements -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-rose-500/20 rounded-full blur-3xl" />
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-rose-400/10 rounded-full blur-3xl" />

        <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
          <div class="flex items-center gap-6">
            <div class="w-20 h-20 rounded-3xl bg-white/10 backdrop-blur-xl flex items-center justify-center text-4xl border border-white/20 shadow-inner">
              <svg
                class="w-10 h-10 text-rose-300"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
              /></svg>
            </div>
            <div>
              <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-4xl font-black tracking-tight">
                  سجل عملية حذف
                </h1>
                <Badge
                  variant="danger"
                  class="!px-4 !py-1 text-xs"
                >
                  عملية نهائية
                </Badge>
              </div>
              <div class="mt-4 flex flex-wrap items-center gap-6 text-rose-100/80 text-sm font-bold uppercase tracking-widest">
                <div class="flex items-center gap-2">
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  /></svg>
                  <span>المسؤول: {{ deletion.user?.name || 'نظام تلقائي' }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                  /></svg>
                  <span dir="ltr">{{ new Date(deletion.created_at).toLocaleString('ar-EG') }}</span>
                </div>
              </div>
            </div>
          </div>

          <Link :href="route('deletions.index')">
            <PrimaryButton class="bg-white/10 hover:bg-white/20 text-white border-white/20 backdrop-blur-md">
              العودة للسجل
            </PrimaryButton>
          </Link>
        </div>
      </div>
    </template>

    <div class="space-y-12 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Metadata Card -->
        <div class="lg:col-span-1">
          <Card class="h-full">
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
              <span class="w-1 h-4 bg-rose-500 rounded-full" />
              معلومات الكيان الممسوح
            </h3>
            <dl class="space-y-6">
              <div>
                <dt class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
                  نوع الكيان
                </dt>
                <dd class="text-base font-black text-gray-900 dark:text-white">
                  {{ deletion.entity_type.split('\\').pop() }}
                </dd>
              </div>
              <div>
                <dt class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
                  المعرف السابق (ID)
                </dt>
                <dd class="text-sm font-mono text-gray-500 font-bold">
                  {{ deletion.entity_id }}
                </dd>
              </div>
              <div>
                <dt class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
                  سبب الحذف المصرح
                </dt>
                <dd class="text-sm font-bold text-rose-600 dark:text-rose-400 italic">
                  {{ deletion.reason || 'لم يتم ذكر سبب محدد لهذه العملية.' }}
                </dd>
              </div>
            </dl>
          </Card>
        </div>

        <!-- Snapshot Data -->
        <div class="lg:col-span-2">
          <Card class="h-full">
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
              <span class="w-1 h-4 bg-gray-500 rounded-full" />
              نسخة احتياطية للبيانات المفقودة
            </h3>
                        
            <div
              v-if="deletion.data"
              class="overflow-hidden rounded-2xl border border-gray-100 dark:border-white/5"
            >
              <table class="min-w-full divide-y divide-gray-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                  <tr>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                      الحقل
                    </th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                      القيمة المسترجعة
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                  <tr
                    v-for="(val, key) in deletion.data"
                    :key="key"
                    class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                  >
                    <td class="px-6 py-4 font-black text-gray-900 dark:text-white">
                      {{ key }}
                    </td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400 break-all">
                      {{ formatValue(val) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
                        
            <div
              v-else
              class="flex flex-col items-center justify-center py-20 text-center opacity-40"
            >
              <svg
                class="w-16 h-16 text-gray-300 mb-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              /></svg>
              <p class="text-sm font-black uppercase tracking-widest">
                لا توجد بيانات محفوظة لهذا الكيان
              </p>
            </div>
          </Card>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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
