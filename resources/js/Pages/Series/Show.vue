<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// UI Components
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    series: Object,
});

const activeTab = ref('contents');

const tabs = [
    { id: 'overview', name: 'نظرة عامة' },
    { id: 'contents', name: 'المحتويات' },
];
</script>

<template>
  <AuthenticatedLayout :title="series.title">
    <!-- Premium Hero Section -->
    <div class="relative -mt-8 -mx-8 mb-8 overflow-hidden bg-emerald-950 min-h-[400px] flex items-end">
      <!-- Decorative Elements -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl animate-pulse" />
        <div class="absolute top-1/2 -right-24 w-64 h-64 bg-lime-400/10 rounded-full blur-3xl animate-pulse delay-700" />
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20 mix-blend-overlay" />
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pb-12 relative z-10">
        <div class="flex flex-col md:flex-row gap-8 items-center md:items-end">
          <!-- Icon / Visual -->
          <div class="w-48 h-48 rounded-[2.5rem] bg-white dark:bg-black/40 p-1 shadow-2xl relative group">
            <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500 to-lime-400 rounded-[2.5rem] blur opacity-20 group-hover:opacity-40 transition-opacity" />
            <div class="w-full h-full rounded-[2.2rem] bg-gray-50 dark:bg-emerald-900/20 overflow-hidden flex items-center justify-center border border-white/10 relative z-10">
              <svg
                class="w-24 h-24 text-emerald-500/40"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 10h16M4 14h16M4 18h16"
              /></svg>
            </div>
          </div>

          <!-- Info -->
          <div class="flex-1 text-center md:text-right pb-4">
            <div class="flex items-center justify-center md:justify-start gap-2 mb-2">
              <Badge
                variant="gray"
                class="!bg-emerald-500/10 !text-emerald-400 !border-emerald-500/20 !text-[10px] font-black uppercase tracking-wider backdrop-blur-sm"
              >
                سلسلة علمية
              </Badge>
              <span class="px-3 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-black backdrop-blur-sm">
                ترتيب: #{{ series.order_column }}
              </span>
            </div>

            <h1 class="text-3xl md:text-5xl font-black text-white mb-4 tracking-tight leading-tight">
              {{ series.title }}
            </h1>
                        
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
              <Link :href="route('series.edit', series.id)">
                <PrimaryButton class="!bg-lime-400 !text-black hover:!bg-lime-300 !border-0 !text-sm !px-6 !py-3 !rounded-xl !shadow-[0_0_20px_rgba(163,230,53,0.3)] flex items-center gap-2">
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                  /></svg>
                  تعديل السلسلة
                </PrimaryButton>
              </Link>

              <button class="p-3 rounded-xl bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-all backdrop-blur-md">
                <svg
                  class="w-5 h-5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
                /></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
      <!-- Tabs Navigation -->
      <div class="flex border-b border-gray-100 dark:border-white/5 mb-8 overflow-x-auto">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="[
            'px-8 py-4 text-sm font-black transition-all relative whitespace-nowrap',
            activeTab === tab.id
              ? 'text-emerald-600 dark:text-emerald-400'
              : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'
          ]"
          @click="activeTab = tab.id"
        >
          {{ tab.name }}
          <div
            v-if="activeTab === tab.id"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500 shadow-[0_-2px_10px_rgba(16,185,129,0.5)]"
          />
        </button>
      </div>

      <!-- Tab Content: Overview -->
      <div
        v-if="activeTab === 'overview'"
        class="animate-fade-in-up"
      >
        <Card class="!p-8">
          <h3 class="font-black text-xl mb-6 flex items-center gap-2 dark:text-white">
            <span class="w-1 h-6 bg-emerald-500 rounded-full" />
            عن هذه السلسلة
          </h3>
          <p class="text-gray-500 dark:text-gray-400 leading-relaxed text-lg">
            {{ series.description || 'لا يوجد وصف متاح لهذه السلسلة حتى الآن.' }}
          </p>
        </Card>
      </div>

      <!-- Tab Content: Contents -->
      <div
        v-if="activeTab === 'contents'"
        class="space-y-8 animate-fade-in-up"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Books -->
          <Card class="!p-6">
            <h4 class="font-black text-sm mb-4 text-emerald-600 dark:text-emerald-400 flex items-center justify-between">
              الكتب
              <Badge
                color="emerald"
                class="!text-[10px]"
              >
                {{ series.books.length }}
              </Badge>
            </h4>
            <ul class="space-y-3">
              <li
                v-for="book in series.books"
                :key="book.id"
                class="group"
              >
                <Link
                  :href="route('books.show', book.slug)"
                  class="text-sm font-bold text-gray-500 hover:text-emerald-500 transition-colors flex items-center gap-2"
                >
                  <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 group-hover:scale-125 transition-transform" />
                  {{ book.title }}
                </Link>
              </li>
              <li
                v-if="!series.books.length"
                class="text-xs text-gray-400 italic"
              >
                لا توجد كتب مضافة
              </li>
            </ul>
          </Card>

          <!-- Audio -->
          <Card class="!p-6">
            <h4 class="font-black text-sm mb-4 text-blue-600 dark:text-blue-400 flex items-center justify-between">
              الصوتيات
              <Badge
                color="blue"
                class="!text-[10px]"
              >
                {{ series.audio.length }}
              </Badge>
            </h4>
            <ul class="space-y-3">
              <li
                v-for="item in series.audio"
                :key="item.id"
                class="group"
              >
                <Link
                  :href="route('audios.show', item.slug)"
                  class="text-sm font-bold text-gray-500 hover:text-blue-500 transition-colors flex items-center gap-2"
                >
                  <div class="w-1.5 h-1.5 rounded-full bg-blue-400 group-hover:scale-125 transition-transform" />
                  {{ item.title }}
                </Link>
              </li>
              <li
                v-if="!series.audio.length"
                class="text-xs text-gray-400 italic"
              >
                لا توجد صوتيات
              </li>
            </ul>
          </Card>

          <!-- Videos -->
          <Card class="!p-6">
            <h4 class="font-black text-sm mb-4 text-purple-600 dark:text-purple-400 flex items-center justify-between">
              المرئيات
              <Badge
                color="purple"
                class="!text-[10px]"
              >
                {{ series.videos.length }}
              </Badge>
            </h4>
            <ul class="space-y-3">
              <li
                v-for="item in series.videos"
                :key="item.id"
                class="group"
              >
                <Link
                  :href="route('videos.show', item.slug)"
                  class="text-sm font-bold text-gray-500 hover:text-purple-500 transition-colors flex items-center gap-2"
                >
                  <div class="w-1.5 h-1.5 rounded-full bg-purple-400 group-hover:scale-125 transition-transform" />
                  {{ item.title }}
                </Link>
              </li>
              <li
                v-if="!series.videos.length"
                class="text-xs text-gray-400 italic"
              >
                لا توجد مرئيات
              </li>
            </ul>
          </Card>

          <!-- Manuscripts -->
          <Card class="!p-6">
            <h4 class="font-black text-sm mb-4 text-amber-600 dark:text-amber-400 flex items-center justify-between">
              المخطوطات
              <Badge
                color="amber"
                class="!text-[10px]"
              >
                {{ series.manuscripts.length }}
              </Badge>
            </h4>
            <ul class="space-y-3">
              <li
                v-for="item in series.manuscripts"
                :key="item.id"
                class="group"
              >
                <Link
                  :href="route('manuscripts.show', item.slug)"
                  class="text-sm font-bold text-gray-500 hover:text-amber-500 transition-colors flex items-center gap-2"
                >
                  <div class="w-1.5 h-1.5 rounded-full bg-amber-400 group-hover:scale-125 transition-transform" />
                  {{ item.title }}
                </Link>
              </li>
              <li
                v-if="!series.manuscripts.length"
                class="text-xs text-gray-400 italic"
              >
                لا توجد مخطوطات
              </li>
            </ul>
          </Card>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
