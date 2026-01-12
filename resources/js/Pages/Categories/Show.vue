<template>
  <AuthenticatedLayout :title="'تصنيف: ' + category.name">
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
                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2zm5-3a2 2 0 100 4 2 2 0 000-4z"
              /></svg>
            </div>
            <div>
              <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-4xl font-black tracking-tight">
                  {{ category.name }}
                </h1>
                <Badge
                  color="emerald"
                  class="bg-emerald-500/30 text-emerald-100 border-emerald-400/30"
                >
                  تصنيف هيكلي
                </Badge>
              </div>
              <div class="mt-4 flex flex-wrap items-center gap-6 text-emerald-100/80 text-sm font-bold uppercase tracking-widest">
                <div
                  v-if="category.parent"
                  class="flex items-center gap-2"
                >
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  /></svg>
                  <span>التصنيف الأعلى: {{ category.parent.name }}</span>
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
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  /></svg>
                  <span>إجمالي المحتوى: {{ totalContent }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <Link :href="route('categories.edit', category.id)">
              <PrimaryButton class="bg-white/10 hover:bg-white/20 text-white border-white/20 backdrop-blur-md">
                تعديل بيانات التصنيف
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
              {{ category.books_count }}
            </div>
          </div>
        </Card>
        <Card>
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
              صوتيات
            </div>
            <div class="text-3xl font-black text-gray-900 dark:text-white">
              {{ category.audio_count }}
            </div>
          </div>
        </Card>
        <Card>
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
              مرئيات
            </div>
            <div class="text-3xl font-black text-gray-900 dark:text-white">
              {{ category.videos_count }}
            </div>
          </div>
        </Card>
        <Card>
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
              مخطوطات
            </div>
            <div class="text-3xl font-black text-gray-900 dark:text-white">
              {{ category.manuscripts_count }}
            </div>
          </div>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Description -->
        <div class="lg:col-span-2">
          <Card class="h-full">
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
              <span class="w-1 h-4 bg-emerald-500 rounded-full" />
              الوصف والتعريف
            </h3>
            <p class="text-gray-600 dark:text-gray-300 leading-relaxed italic">
              {{ category.description || 'لا يوجد وصف متاح لهذا التصنيف حالياً.' }}
            </p>
          </Card>
        </div>

        <!-- Sub-categories -->
        <div v-if="category.children?.length > 0">
          <div class="space-y-4">
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
              <span class="w-1 h-4 bg-lime-500 rounded-full" />
              التصنيفات الفرعية ({{ category.children.length }})
            </h3>
            <div class="space-y-3">
              <Link
                v-for="child in category.children"
                :key="child.id"
                :href="route('categories.show', child.id)"
              >
                <Card class="!p-4 hover:border-emerald-500 group transition-all cursor-pointer">
                  <div class="flex items-center justify-between">
                    <span class="font-black text-gray-900 dark:text-white group-hover:text-emerald-600">{{ child.name }}</span>
                    <svg
                      class="w-4 h-4 text-gray-300 group-hover:text-emerald-500 transition-transform group-hover:translate-x-[-4px]"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    ><path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 5l7 7-7 7"
                    /></svg>
                  </div>
                </Card>
              </Link>
            </div>
          </div>
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
    category: Object,
});

const totalContent = computed(() => {
    return (props.category.books_count || 0) +
           (props.category.audio_count || 0) +
           (props.category.videos_count || 0) +
           (props.category.manuscripts_count || 0);
});
</script>
