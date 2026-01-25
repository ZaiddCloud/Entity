<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    video: Object,
    first_content_slug: String,
    siblings: Array,
});

const activeTab = ref('overview');

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
  <AuthenticatedLayout :title="video.title">
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h2 class="font-black text-2xl text-gray-800 dark:text-white leading-tight flex items-center gap-2">
          <span class="w-2 h-8 bg-emerald-500 rounded-full inline-block" />
          تفاصيل المرئية
        </h2>
        <div class="flex gap-2">
          <Link
            :href="route('videos.index')"
            class="px-4 py-2 bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 rounded-xl font-bold text-xs hover:bg-gray-200 dark:hover:bg-white/10 transition-colors"
          >
            العودة للقائمة
          </Link>
        </div>
      </div>
    </template>

    <!-- Premium Hero Section -->
    <div class="relative -mt-8 -mx-8 mb-8 bg-emerald-950 overflow-hidden">
      <!-- Background Effects -->
      <div class="absolute inset-0 bg-[url('/images/grid.svg')] opacity-10" />
      <div class="absolute top-0 right-0 w-2/3 h-full bg-gradient-to-l from-emerald-900/50 to-transparent" />
      <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-lime-500/20 rounded-full blur-3xl" />

      <div class="relative max-w-7xl mx-auto px-6 lg:px-8 py-12">
        <div class="flex flex-col md:flex-row gap-8 items-start">
          <!-- Cover Image -->
          <div class="w-full md:w-64 flex-shrink-0 group perspective-1000">
            <div class="relative aspect-[3/4] rounded-2xl overflow-hidden shadow-2xl ring-4 ring-white/10 group-hover:ring-lime-400/50 transition-all duration-500 transform group-hover:rotate-y-6">
              <img 
                v-if="video.cover_path" 
                :src="'/storage/' + video.cover_path" 
                :alt="video.title"
                class="w-full h-full object-cover"
              >
              <div
                v-else
                class="w-full h-full bg-emerald-900 flex items-center justify-center"
              >
                <svg
                  class="w-20 h-20 text-emerald-700"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                  />
                </svg>
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-60" />
                            
              <!-- Duration Badge on Cover -->
              <div class="absolute top-4 left-4">
                <Badge class="bg-black/50 backdrop-blur-md text-white border-0">
                  {{ video.duration ? video.duration + ' دقيقة' : '-' }}
                </Badge>
              </div>
            </div>
          </div>

          <!-- Info -->
          <div class="flex-1 text-white pt-2">
            <div class="flex items-center gap-3 mb-4">
              <Badge class="bg-lime-400/20 text-lime-400 border-lime-400/20 backdrop-blur-sm">
                مرئية
              </Badge>
              <span class="font-mono text-emerald-400 opacity-60 tracking-wider text-sm">#{{ video.formatted_serial_number }}</span>
            </div>
                        
            <h1 class="text-4xl md:text-5xl font-black mb-6 leading-tight tracking-tight">
              {{ video.title }}
            </h1>

            <div class="flex flex-wrap gap-6 text-sm text-emerald-100/80 mb-6 border-l-4 border-lime-500 pl-6">
              <div>
                <span class="block text-[10px] uppercase tracking-wider text-emerald-400 mb-1">المركز الإعلامي / القناة</span>
                <div class="font-bold text-white text-lg flex items-center gap-2">
                  {{ video.versions?.[0]?.publisher?.name || 'غير محدد' }}
                  <svg
                    v-if="video.versions?.[0]?.publisher?.name"
                    class="w-4 h-4 text-lime-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  /></svg>
                </div>
              </div>
              <div v-if="video.authors?.length">
                <span class="block text-[10px] uppercase tracking-wider text-emerald-400 mb-1">المعد / المقدم</span>
                <div class="font-bold text-white text-lg">
                  {{ (video.authors || []).map(a => a.name).join('، ') }}
                </div>
              </div>
            </div>

            <!-- Version Switcher -->
            <div v-if="siblings?.length" class="mb-8 flex flex-wrap items-center gap-3">
              <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500">متاح أيضاً بإنتاج:</span>
              <div class="flex flex-wrap gap-2">
                <Link 
                  v-for="sibling in siblings" 
                  :key="sibling.id"
                  :href="route('videos.show', sibling.slug)"
                  class="px-4 py-1.5 bg-emerald-900/40 hover:bg-lime-500 hover:text-emerald-950 border border-emerald-500/30 rounded-full text-[10px] font-bold text-emerald-100 transition-all flex items-center gap-2"
                >
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  {{ sibling.versions?.[0]?.publisher?.name || sibling.title }}
                </Link>
              </div>
            </div>

            <div class="flex flex-wrap gap-3">
              <Link :href="route('reader.show', { type: 'video', slug: video.slug })">
                <PrimaryButton class="!bg-lime-400 hover:!bg-lime-300 !text-emerald-950 !border-0 shadow-[0_0_20px_rgba(132,204,22,0.4)] flex items-center gap-2 px-8 py-3 ring-2 ring-lime-400 ring-offset-2 ring-offset-emerald-950">
                  <svg class="w-5 h-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  بدء المشاهدة والقراءة
                </PrimaryButton>
              </Link>

              <Link :href="route('dev.player', { type: 'video', slug: video.slug })">
                <PrimaryButton class="!bg-lime-500 hover:!bg-lime-400 !text-emerald-950 !border-0 shadow-[0_0_20px_rgba(132,204,22,0.4)] flex items-center gap-2 px-8 py-3">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  مشغل الفيديو الغامر
                </PrimaryButton>
              </Link>

              <Link :href="route('videos.edit', video.slug)">
                <button class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold text-sm transition-all backdrop-blur-sm border border-white/10 flex items-center gap-2">
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  /></svg>
                  تعديل المرئية
                </button>
              </Link>

              <Link 
                :href="route('studio.show', { type: 'video', slug: first_content_slug || video.slug })"
              >
                <PrimaryButton class="!bg-lime-500 hover:!bg-lime-400 !text-emerald-950 !border-0 shadow-[0_0_20px_rgba(132,204,22,0.4)] flex items-center gap-2 px-8 py-3">
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
                  تحرير في الأستوديو
                </PrimaryButton>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="max-w-7xl mx-auto px-6 lg:px-8 mt-8">
        <div class="flex gap-8 border-b border-white/10">
          <button 
            class="pb-4 text-sm font-bold transition-all relative"
            :class="activeTab === 'overview' ? 'text-lime-400' : 'text-emerald-300/60 hover:text-emerald-200'"
            @click="activeTab = 'overview'"
          >
            نظرة عامة
            <span
              v-if="activeTab === 'overview'"
              class="absolute bottom-0 right-0 w-full h-0.5 bg-lime-400 shadow-[0_0_10px_rgba(132,204,22,0.5)]"
            />
          </button>
          <button 
            class="pb-4 text-sm font-bold transition-all relative"
            :class="activeTab === 'content' ? 'text-lime-400' : 'text-emerald-300/60 hover:text-emerald-200'"
            @click="activeTab = 'content'"
          >
            المادة المرئية
            <span
              v-if="activeTab === 'content'"
              class="absolute bottom-0 right-0 w-full h-0.5 bg-lime-400 shadow-[0_0_10px_rgba(132,204,22,0.5)]"
            />
          </button>
          <button 
            class="pb-4 text-sm font-bold transition-all relative"
            :class="activeTab === 'details' ? 'text-lime-400' : 'text-emerald-300/60 hover:text-emerald-200'"
            @click="activeTab = 'details'"
          >
            التفاصيل التقنية
            <span
              v-if="activeTab === 'details'"
              class="absolute bottom-0 right-0 w-full h-0.5 bg-lime-400 shadow-[0_0_10px_rgba(132,204,22,0.5)]"
            />
          </button>
        </div>
      </div>
    </div>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <!-- Overview Tab -->
        <div
          v-if="activeTab === 'overview'"
          class="space-y-8 animate-fade-in-up"
        >
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-8">
              <!-- Description -->
              <Card class="!p-8">
                <h3 class="text-xl font-black text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                  <svg
                    class="w-6 h-6 text-emerald-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h7"
                  /></svg>
                  نبذة عن المحتوى
                </h3>
                <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                  {{ video.description || 'لا يوجد وصف متاح.' }}
                </div>
              </Card>

              <!-- Comments Preview -->
              <div class="mt-8">
                <h3 class="text-lg font-black text-gray-900 dark:text-gray-200 mb-4 px-2">
                  أحدث التعليقات
                </h3>
                <div
                  v-if="video.comments?.length"
                  class="space-y-4"
                >
                  <Card
                    v-for="comment in video.comments.slice(0, 3)"
                    :key="comment.id"
                    class="!p-4"
                  >
                    <div class="flex items-start gap-4">
                      <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                        {{ comment.user?.name?.charAt(0) || 'U' }}
                      </div>
                      <div>
                        <div class="flex items-center gap-2 mb-1">
                          <span class="font-bold text-sm text-gray-900 dark:text-white">{{ comment.user?.name || 'مستخدم' }}</span>
                          <span class="text-[10px] text-gray-400">{{ formatDate(comment.created_at) }}</span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                          {{ comment.content }}
                        </p>
                      </div>
                    </div>
                  </Card>
                </div>
                <div
                  v-else
                  class="text-center py-12 bg-gray-50 dark:bg-white/5 rounded-3xl border border-dashed border-gray-200 dark:border-white/10"
                >
                  <p class="text-gray-400 text-sm font-bold">
                    لا توجد تعليقات حتى الآن
                  </p>
                </div>
              </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
              <!-- Quick Stats -->
              <div class="grid grid-cols-2 gap-4">
                <Card class="!p-4 text-center group hover:border-emerald-500/30 transition-colors">
                  <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mb-1 font-mono group-hover:scale-110 transition-transform">
                    {{ video.duration || '0' }}
                  </div>
                  <div class="text-[10px] uppercase font-bold text-gray-400">
                    المدة (د)
                  </div>
                </Card>
                <Card class="!p-4 text-center group hover:border-emerald-500/30 transition-colors">
                  <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mb-1 font-mono group-hover:scale-110 transition-transform">
                    {{ video.versions?.[0]?.published_year || '-' }}
                  </div>
                  <div class="text-[10px] uppercase font-bold text-gray-400">
                    سنة الإنتاج
                  </div>
                </Card>
              </div>

              <!-- Taxonomy -->
              <Card class="space-y-6">
                <div>
                  <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">
                    التصنيفات
                  </h4>
                  <div class="flex flex-wrap gap-2">
                    <Badge
                      v-for="cat in video.categories"
                      :key="cat.id"
                      color="emerald"
                    >
                      {{ cat.name }}
                    </Badge>
                    <span
                      v-if="!video.categories?.length"
                      class="text-xs text-gray-400"
                    >-</span>
                  </div>
                </div>
                                
                <div>
                  <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">
                    الوسوم
                  </h4>
                  <div class="flex flex-wrap gap-2">
                    <Badge
                      v-for="tag in video.tags"
                      :key="tag.id"
                      color="gray"
                    >
                      #{{ tag.name }}
                    </Badge>
                    <span
                      v-if="!video.tags?.length"
                      class="text-xs text-gray-400"
                    >-</span>
                  </div>
                </div>
              </Card>

              <!-- Latest Activity -->
              <Card>
                <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">
                  آخر نشاط
                </h4>
                <div class="space-y-4">
                  <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-emerald-500" />
                    <div class="flex-1">
                      <div class="text-xs font-bold text-gray-500">
                        تم الإنشاء
                      </div>
                      <div class="text-sm font-bold text-gray-900 dark:text-white">
                        {{ formatDate(video.created_at) }}
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-blue-500" />
                    <div class="flex-1">
                      <div class="text-xs font-bold text-gray-500">
                        آخر تعديل
                      </div>
                      <div class="text-sm font-bold text-gray-900 dark:text-white">
                        {{ formatDate(video.updated_at) }}
                      </div>
                    </div>
                  </div>
                </div>
              </Card>
            </div>
          </div>
        </div>

        <!-- Content Tab -->
        <div
          v-else-if="activeTab === 'content'"
          class="space-y-6 animate-fade-in-up"
        >
          <Card class="!p-8">
            <h3 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center gap-2">
              <svg
                class="w-6 h-6 text-red-500"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
              /><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              /></svg>
              مشغل الفيديو
            </h3>
                        
            <div
              v-if="video.versions?.[0]?.file_path || video.file_path"
              class="rounded-2xl overflow-hidden bg-black aspect-video relative group"
            >
              <video
                controls
                class="w-full h-full"
              >
                <source
                  :src="'/storage/' + (video.versions?.[0]?.file_path || video.file_path)"
                  type="video/mp4"
                >
                متصفحك لا يدعم تشغيل الفيديو.
              </video>
            </div>
            <div
              v-else
              class="text-center py-20 bg-gray-50 dark:bg-white/5 rounded-3xl border border-dashed border-gray-200 dark:border-white/10"
            >
              <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-white/10 rounded-full flex items-center justify-center mb-4 text-gray-400">
                <svg
                  class="w-8 h-8"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                /></svg>
              </div>
              <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                لا يوجد ملف فيديو
              </h3>
              <p class="text-gray-500 text-sm">
                لم يتم رفع ملف فيديو لهذه المرئية بعد.
              </p>
            </div>
          </Card>

          <Card class="!p-8">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                <svg
                  class="w-6 h-6 text-blue-500"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                /></svg>
                المحتوى النصي (MongoDB)
              </h3>
              <Link 
                v-if="first_content_slug"
                :href="route('studio.show', { type: 'video', slug: first_content_slug })"
              >
                <PrimaryButton class="!bg-blue-600 hover:!bg-blue-500">
                  فتح في المحرر
                </PrimaryButton>
              </Link>
            </div>
            <p class="text-gray-500 text-sm leading-relaxed">
              يتوفر محتوى هذه المرئية (المشاهد والنصوص) في محرر المحتوى المتقدم. يمكنك تحرير النصوص وإضافة المشاهد التفصيلية من خلال المحرر.
            </p>
          </Card>
        </div>

        <!-- Details Tab -->
        <div
          v-else-if="activeTab === 'details'"
          class="space-y-6 animate-fade-in-up"
        >
          <Card class="!p-0 overflow-hidden">
            <table class="w-full text-sm text-left">
              <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                  <td class="p-6 font-bold text-gray-500 w-1/4">
                    الرقم التسلسلي
                  </td>
                  <td class="p-6 font-mono font-bold text-gray-900 dark:text-white dir-ltr text-right">
                    {{ video.formatted_serial_number }}
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                  <td class="p-6 font-bold text-gray-500">
                    معرف قاعدة البيانات
                  </td>
                  <td class="p-6 font-mono text-gray-400 dir-ltr text-right">
                    {{ video.id }}
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                  <td class="p-6 font-bold text-gray-500">
                    Slug
                  </td>
                  <td class="p-6 font-mono text-gray-400 dir-ltr text-right">
                    {{ video.slug }}
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                  <td class="p-6 font-bold text-gray-500">
                    تاريخ الإنشاء
                  </td>
                  <td class="p-6 text-gray-900 dark:text-white text-right">
                    {{ formatDate(video.created_at) }}
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                  <td class="p-6 font-bold text-gray-500">
                    آخر تحديث
                  </td>
                  <td class="p-6 text-gray-900 dark:text-white text-right">
                    {{ formatDate(video.updated_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </Card>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
