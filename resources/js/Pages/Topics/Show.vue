<template>
  <AuthenticatedLayout :title="'موضوع: ' + topic.name">
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
                d="M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2v-6a2 2 0 012-2h-2m-2-4l-4 4m0 0l-4-4m4 4V3"
              /></svg>
            </div>
            <div>
              <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-4xl font-black tracking-tight">
                  {{ topic.name }}
                </h1>
                <Badge
                  color="emerald"
                  class="bg-emerald-500/30 text-emerald-100 border-emerald-400/30"
                >
                  موضوع بحثي
                </Badge>
              </div>
              <div class="mt-4 flex flex-wrap items-center gap-6 text-emerald-100/80 text-sm font-bold uppercase tracking-widest">
                <div
                  v-if="topic.parent"
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
                  <span>الموضوع الرئيسي: {{ topic.parent.name }}</span>
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
                  <span>إجمالي المحتوى: {{ totalContent }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <Link :href="route('topics.edit', topic.id)">
              <PrimaryButton class="bg-white/10 hover:bg-white/20 text-white border-white/20 backdrop-blur-md">
                تعديل بيانات الموضوع
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
              {{ topic.books_count }}
            </div>
          </div>
        </Card>
        <Card>
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
              صوتيات
            </div>
            <div class="text-3xl font-black text-gray-900 dark:text-white">
              {{ topic.audios_count }}
            </div>
          </div>
        </Card>
        <Card>
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
              مرئيات
            </div>
            <div class="text-3xl font-black text-gray-900 dark:text-white">
              {{ topic.videos_count }}
            </div>
          </div>
        </Card>
        <Card>
          <div class="flex flex-col items-center text-center p-2">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
              مخطوطات
            </div>
            <div class="text-3xl font-black text-gray-900 dark:text-white">
              {{ topic.manuscripts_count }}
            </div>
          </div>
        </Card>
      </div>

      <!-- Sub-topics if any -->
      <div
        v-if="topic.children?.length > 0"
        class="space-y-6"
      >
        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">
          المواضيع الفرعية ({{ topic.children.length }})
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Link
            v-for="child in topic.children"
            :key="child.id"
            :href="route('topics.show', child.id)"
          >
            <Card class="hover:border-emerald-500 group transition-all">
              <div class="flex items-center justify-between">
                <span class="font-black text-gray-900 dark:text-white group-hover:text-emerald-600">{{ child.name }}</span>
                <svg
                  class="w-4 h-4 text-gray-300 group-hover:text-emerald-500"
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
    topic: Object
});

const totalContent = computed(() => {
    return (props.topic.books_count || 0) +
           (props.topic.audios_count || 0) +
           (props.topic.videos_count || 0) +
           (props.topic.manuscripts_count || 0);
});
</script>
