<template>
  <AuthenticatedLayout :title="'لغة: ' + language.name">
    <!-- Premium Hero Section -->
    <template #header>
      <div class="relative overflow-hidden bg-emerald-700 rounded-[2.5rem] p-12 text-white shadow-2xl shadow-emerald-900/40">
        <!-- Abstract Background Elements -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl" />
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-lime-400/10 rounded-full blur-3xl" />

        <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
          <div class="flex items-center gap-6">
            <div class="w-20 h-20 rounded-3xl bg-white/10 backdrop-blur-xl flex items-center justify-center text-3xl font-black border border-white/20 shadow-inner uppercase">
              {{ language.code || '??' }}
            </div>
            <div>
              <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-4xl font-black tracking-tight">
                  {{ language.name }}
                </h1>
                <Badge
                  color="emerald"
                  class="bg-emerald-500/30 text-emerald-100 border-emerald-400/30 font-mono uppercase"
                >
                  {{ language.code }}
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
                    d="M3 5h12M9 3v2m1.048 9.531a11.115 11.115 0 01-1.048-3.531m6.241 3a9.904 9.904 0 01-6.241 3m0 0a9.904 9.904 0 01-6.241-3m6.241 3v2m0-6V7m0 0H7m3 0h3"
                  /></svg>
                  <span>إحصائيات اللغة</span>
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
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                  /></svg>
                  <span>إجمالي المحتوى المترجم: {{ totalContent }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <Link :href="route('languages.edit', language.id)">
              <PrimaryButton class="bg-white/10 hover:bg-white/20 text-white border-white/20 backdrop-blur-md">
                تعديل بيانات اللغة
              </PrimaryButton>
            </Link>
          </div>
        </div>
      </div>
    </template>

    <div class="space-y-12 py-8">
      <!-- Stats Grid -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <Card class="bg-emerald-50/50 dark:bg-emerald-500/5 border-emerald-100 dark:border-emerald-500/10">
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-2">
              كتب
            </div>
            <div class="text-3xl font-black text-emerald-900 dark:text-white">
              {{ language.books_count }}
            </div>
          </div>
        </Card>
        <Card>
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
              صوتيات
            </div>
            <div class="text-3xl font-black text-gray-900 dark:text-white">
              {{ language.audios_count }}
            </div>
          </div>
        </Card>
        <Card>
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
              مرئيات
            </div>
            <div class="text-3xl font-black text-gray-900 dark:text-white">
              {{ language.videos_count }}
            </div>
          </div>
        </Card>
        <Card>
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
              مخطوطات
            </div>
            <div class="text-3xl font-black text-gray-900 dark:text-white">
              {{ language.manuscripts_count }}
            </div>
          </div>
        </Card>
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
    language: Object
});

const totalContent = computed(() => {
    return (props.language.books_count || 0) +
           (props.language.audios_count || 0) +
           (props.language.videos_count || 0) +
           (props.language.manuscripts_count || 0);
});
</script>
