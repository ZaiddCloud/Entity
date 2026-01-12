<template>
  <AuthenticatedLayout title="تفاصيل النشاط">
    <!-- Premium Hero Section -->
    <template #header>
      <div class="relative overflow-hidden bg-emerald-700 rounded-[2.5rem] p-12 text-white shadow-2xl shadow-emerald-900/40">
        <!-- Abstract Background Elements -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl" />
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-lime-400/10 rounded-full blur-3xl" />

        <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
          <div class="flex items-center gap-6">
            <div class="w-20 h-20 rounded-3xl bg-white/10 backdrop-blur-xl flex items-center justify-center text-4xl border border-white/20 shadow-inner">
              <svg
                class="w-10 h-10 text-lime-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
              /></svg>
            </div>
            <div>
              <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-4xl font-black tracking-tight">
                  {{ translateActivity(activity.activity_type) }}
                </h1>
                <Badge 
                  :variant="activity.activity_type === 'created' ? 'success' : (activity.activity_type === 'updated' ? 'warning' : 'danger')"
                  class="!px-4 !py-1 text-xs"
                >
                  {{ activity.activity_type }}
                </Badge>
              </div>
              <div class="mt-4 flex flex-wrap items-center gap-6 text-emerald-100/80 text-sm font-bold uppercase tracking-widest">
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
                  <span>المستخدم: {{ activity.user?.name || 'نظام تلقائي' }}</span>
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
                  <span dir="ltr">{{ new Date(activity.created_at).toLocaleString('ar-EG') }}</span>
                </div>
              </div>
            </div>
          </div>

          <Link :href="route('activities.index')">
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
              <span class="w-1 h-4 bg-emerald-500 rounded-full" />
              معلومات النشاط التقنية
            </h3>
            <dl class="space-y-6">
              <div>
                <dt class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
                  نوع المحتوى
                </dt>
                <dd class="text-base font-black text-gray-900 dark:text-white">
                  {{ activity.entity_type.split('\\').pop() }}
                </dd>
              </div>
              <div v-if="activity.entity">
                <dt class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
                  عنوان العنصر
                </dt>
                <dd class="text-base font-black text-emerald-600 dark:text-emerald-400 italic">
                  {{ activity.entity.title || activity.entity.name || 'لا يوجد عنوان' }}
                </dd>
              </div>
              <div>
                <dt class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
                  وصف العملية
                </dt>
                <dd class="text-sm font-bold text-gray-600 dark:text-gray-300">
                  {{ activity.description }}
                </dd>
              </div>
            </dl>
          </Card>
        </div>

        <!-- Changes Table -->
        <div class="lg:col-span-2">
          <Card class="h-full">
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
              <span class="w-1 h-4 bg-lime-500 rounded-full" />
              تفاصيل التغييرات في البيانات
            </h3>
                        
            <div
              v-if="activity.changes"
              class="overflow-hidden rounded-2xl border border-gray-100 dark:border-white/5"
            >
              <table class="min-w-full divide-y divide-gray-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                  <tr>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                      الحقل
                    </th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                      القيمة السابقة
                    </th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                      القيمة الجديدة
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                  <tr
                    v-for="(val, key) in (activity.changes.after || activity.changes)"
                    :key="key"
                    class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                  >
                    <td class="px-6 py-4 font-black text-gray-900 dark:text-white">
                      {{ key }}
                    </td>
                    <td class="px-6 py-4 text-gray-400 line-through decoration-rose-500/30 opacity-60">
                      {{ formatValue(activity.changes.before?.[key]) }}
                    </td>
                    <td class="px-6 py-4 text-emerald-600 dark:text-emerald-400 font-bold">
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
                لا توجد بيانات تغيير مسجلة لهذا النشاط
              </p>
            </div>
          </Card>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    activity: Object,
});

const translateActivity = (type) => {
    const map = {
        'created': 'إضافة عنصر جديد',
        'updated': 'تحديث بيانات',
        'deleted': 'حذف من النظام',
        'viewed': 'مشاهدة محتوى',
        'restored': 'استعادة عنصر'
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
