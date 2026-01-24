<template>
  <AuthenticatedLayout :title="'وسم: ' + tag.name">
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
                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
              /></svg>
            </div>
            <div>
              <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-4xl font-black tracking-tight">
                  {{ tag.name }}
                </h1>
                <Badge
                  color="amber"
                  class="bg-amber-500/30 text-amber-100 border-amber-400/30"
                >
                  {{ tag.type || 'وسم عام' }}
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
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  /></svg>
                  <span>إجمالي المحتوى المرتبط: {{ totalContent }}</span>
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
                    d="M7 20l4-16m2 16l4-16"
                  /></svg>
                  <span>Slug: {{ tag.slug }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <Link :href="route('tags.edit', tag.id)">
              <PrimaryButton class="bg-white/10 hover:bg-white/20 text-white border-white/20 backdrop-blur-md">
                تعديل بيانات الوسم
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
              {{ tag.books_count }}
            </div>
          </div>
        </Card>
        <Card>
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
              صوتيات
            </div>
            <div class="text-3xl font-black text-gray-900 dark:text-white">
              {{ tag.audio_count }}
            </div>
          </div>
        </Card>
        <Card>
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
              مرئيات
            </div>
            <div class="text-3xl font-black text-gray-900 dark:text-white">
              {{ tag.videos_count }}
            </div>
          </div>
        </Card>
        <Card>
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
              مخطوطات
            </div>
            <div class="text-3xl font-black text-gray-900 dark:text-white">
              {{ tag.manuscripts_count }}
            </div>
          </div>
        </Card>
      </div>

      <Card>
        <div class="text-center py-12">
          <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-white/5 flex items-center justify-center mx-auto mb-4 text-gray-300">
            <svg
              class="w-8 h-8"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            ><path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
            /></svg>
          </div>
          <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">
            استعراض المحتوى المرتبط
          </h3>
          <p class="text-gray-400 text-sm max-w-md mx-auto">
            سيتم إضافة قائمة مفصلة بالمحتويات المرتبطة بهذا الوسم في التحديثات القادمة لتمكين التصفح المتكامل.
          </p>
        </div>
      </Card>
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
    tag: Object
});

const totalContent = computed(() => {
    return (props.tag.books_count || 0) +
           (props.tag.audio_count || 0) +
           (props.tag.videos_count || 0) +
           (props.tag.manuscripts_count || 0);
});
</script>
