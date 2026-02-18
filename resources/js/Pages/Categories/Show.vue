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

        <!-- Right Column / Sub-categories or Info -->
        <div class="space-y-6">
          <div v-if="category.children?.length > 0">
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2 mb-4">
              <span class="w-1 h-4 bg-lime-500 rounded-full" />
              التصنيفات الفرعية ({{ category.children.length }})
            </h3>
            <div class="space-y-2">
              <Link
                v-for="child in category.children"
                :key="child.id"
                :href="route('categories.show', child.id)"
              >
                <Card class="!p-3 hover:border-emerald-500 group transition-all cursor-pointer">
                  <div class="flex items-center justify-between">
                    <span class="font-black text-gray-800 dark:text-white group-hover:text-emerald-600 text-sm">{{ child.name }}</span>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-emerald-500 transition-transform group-hover:translate-x-[-4px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                  </div>
                </Card>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Full Width Content Sections -->
      <div class="space-y-12 pt-4">
        <!-- Manuscripts Section -->
        <div v-if="category.manuscripts?.length > 0">
          <div class="flex items-center justify-between mb-8">
            <h3 class="text-xl font-black flex items-center gap-3">
              <span class="w-1.5 h-8 bg-amber-500 rounded-full" />
              المخطوطات المرتبطة
            </h3>
            <Badge color="amber">{{ category.manuscripts.length }} مخطوط</Badge>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link
              v-for="item in category.manuscripts"
              :key="item.id"
              :href="route('manuscripts.show', item.slug)"
              class="group bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2rem] p-6 hover:border-amber-500/30 hover:shadow-2xl hover:shadow-amber-500/5 transition-all duration-500 flex items-center gap-6"
            >
              <div class="w-16 h-20 bg-amber-50 dark:bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-black text-gray-900 dark:text-white text-lg truncate group-hover:text-amber-500 transition-colors">{{ item.title }}</h4>
                <div class="flex items-center gap-3 mt-2">
                  <span class="text-[10px] font-black text-amber-500/70 bg-amber-50 dark:bg-amber-500/5 px-2 py-1 rounded-lg" v-if="item.catalog_number">رقم: {{ item.catalog_number }}</span>
                  <span class="text-[10px] font-black text-gray-400" v-if="item.parts">{{ item.parts }} أجزاء</span>
                </div>
              </div>
            </Link>
          </div>
        </div>

        <!-- Books Section -->
        <div v-if="category.books?.length > 0">
          <div class="flex items-center justify-between mb-8">
            <h3 class="text-xl font-black flex items-center gap-3">
              <span class="w-1.5 h-8 bg-blue-500 rounded-full" />
              الكتب المرتبطة
            </h3>
            <Badge color="blue">{{ category.books.length }} كتاب</Badge>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link
              v-for="item in category.books"
              :key="item.id"
              :href="route('books.show', item.slug)"
              class="group bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2rem] p-6 hover:border-blue-500/30 hover:shadow-2xl hover:shadow-blue-500/5 transition-all duration-500 flex items-center gap-6"
            >
              <div class="w-16 h-20 bg-blue-50 dark:bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-black text-gray-900 dark:text-white text-lg truncate group-hover:text-blue-500 transition-colors">{{ item.title }}</h4>
                <div class="mt-2">
                   <span class="text-[10px] font-black text-blue-500/70 bg-blue-50 dark:bg-blue-500/5 px-2 py-1 rounded-lg" v-if="item.isbn">ISBN: {{ item.isbn }}</span>
                </div>
              </div>
            </Link>
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
