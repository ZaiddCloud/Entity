<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    audio: Object,
    first_content_slug: String,
    siblings: Array,
});

const activeTab = ref('overview');

const tabs = [
    { id: 'overview', name: 'نظرة عامة' },
    { id: 'content', name: 'المحتوى' },
    { id: 'details', name: 'التفاصيل' },
];

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
  <AuthenticatedLayout :title="audio.title">
    <!-- Hero Section -->
    <div class="relative bg-emerald-950 -mt-8 -mx-8 mb-8 overflow-hidden shadow-2xl">
      <!-- Background Elements -->
      <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] mix-blend-overlay"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500 rounded-full blur-3xl opacity-20 translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-lime-500 rounded-full blur-3xl opacity-20 -translate-x-1/2 translate-y-1/2"></div>
      </div>
            
      <div class="absolute inset-0 bg-gradient-to-t from-emerald-950 via-transparent to-transparent"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 items-start">
          <!-- Audio Cover/Thumbnail -->
          <div class="w-full lg:w-auto flex-shrink-0 flex justify-center lg:justify-start">
            <div class="relative group">
              <div class="absolute -inset-1 bg-gradient-to-tr from-lime-400 to-emerald-600 rounded-2xl blur opacity-30 group-hover:opacity-60 transition duration-500"></div>
              <div class="relative w-48 h-48 lg:w-64 lg:h-64 rounded-2xl overflow-hidden shadow-2xl bg-emerald-900 border-4 border-emerald-900/50 flex items-center justify-center">
                <img 
                  v-if="audio.cover_path" 
                  :src="'/storage/' + audio.cover_path" 
                  :alt="audio.title"
                  class="w-full h-full object-cover transform duration-700 group-hover:scale-110"
                />
                <div
                  v-else
                  class="text-emerald-700/50 flex flex-col items-center"
                >
                  <svg
                    class="w-20 h-20 mb-2"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
                  /></svg>
                  <span class="text-xs font-black uppercase tracking-wider">لا يوجد غلاف</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Main Info -->
          <div class="flex-1 text-center lg:text-right space-y-6">
            <div class="space-y-2">
              <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 text-xs font-bold uppercase tracking-wider text-emerald-400/80 mb-2">
                <span class="bg-emerald-900/50 px-3 py-1 rounded-full border border-emerald-500/20 backdrop-blur-sm">
                  {{ audio.formatted_serial_number }}
                </span>
                <span
                  v-if="audio.versions?.[0]?.published_year"
                  class="bg-emerald-900/50 px-3 py-1 rounded-full border border-emerald-500/20 backdrop-blur-sm"
                >
                  سنة {{ audio.versions?.[0]?.published_year }}
                </span>
              </div>

              <h1 class="text-3xl lg:text-5xl font-black text-white leading-tight drop-shadow-lg">
                {{ audio.title }}
              </h1>
                            
              <p
                v-if="audio.description"
                class="text-lg text-emerald-100/70 max-w-3xl mx-auto lg:mx-0 leading-relaxed line-clamp-2"
              >
                {{ audio.description }}
              </p>

              <!-- Version Switcher -->
              <div v-if="siblings?.length" class="flex items-center justify-center lg:justify-start gap-4 py-2 border-y border-white/5">
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 rounded-full bg-lime-500 animate-pulse"></div>
                  <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400">تسجيلات أخرى متاحة في هذا العمل:</span>
                </div>
                <div class="flex flex-wrap gap-2">
                  <Link 
                    v-for="sibling in siblings" 
                    :key="sibling.id"
                    :href="route('audios.show', sibling.slug)"
                    class="px-4 py-1.5 bg-white/5 hover:bg-emerald-500 hover:text-emerald-950 border border-white/10 rounded-full text-[10px] font-bold text-emerald-100 transition-all"
                  >
                    {{ sibling.title.substring(0, 30) }}{{ sibling.title.length > 30 ? '...' : '' }}
                  </Link>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4">
              <div class="flex flex-wrap gap-3">
              <Link :href="route('reader.show', { type: 'audio', slug: audio.slug })">
                <button class="bg-lime-400 hover:bg-lime-300 text-emerald-950 px-8 py-3 rounded-xl font-black text-sm transition shadow-lg shadow-lime-400/20 hover:scale-105 active:scale-95 ring-2 ring-lime-400 ring-offset-2 ring-offset-emerald-950 flex items-center gap-2">
                  <svg class="w-5 h-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                  </svg>
                  بدء الاستماع والقراءة
                </button>
              </Link>

              <Link 
                :href="route('dev.player', { type: 'audio', slug: audio.slug })"
              >
                <PrimaryButton class="!px-8 !py-4 !text-base !bg-lime-500 hover:!bg-lime-400 !text-emerald-950 shadow-[0_0_20px_rgba(132,204,22,0.3)] hover:shadow-[0_0_30px_rgba(132,204,22,0.5)] border-none flex items-center gap-2">
                  <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>مشغل الصوت الغامر</span>
                </PrimaryButton>
              </Link>
              <Link 
                :href="route('studio.show', { type: 'audio', slug: audio.slug })"
              >
                <PrimaryButton class="!px-8 !py-4 !text-base !bg-lime-500 hover:!bg-lime-400 !text-emerald-950 shadow-[0_0_20px_rgba(132,204,22,0.3)] hover:shadow-[0_0_30px_rgba(132,204,22,0.5)] border-none">
                  <div class="flex items-center gap-3">
                    <svg
                      class="w-6 h-6"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    ><path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                    /></svg>
                    <span>تحرير في الأستوديو</span>
                  </div>
                </PrimaryButton>
              </Link>
              
              <Link :href="route('audios.edit', audio.slug)">
                <button class="px-6 py-4 bg-emerald-900/50 hover:bg-emerald-900 text-emerald-100 rounded-xl font-bold backdrop-blur-sm border border-emerald-500/20 transition-all flex items-center gap-2">
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
                  تعديل
                </button>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10 pb-20">
      <!-- Tabs Navigation -->
      <div class="flex space-x-1 bg-white/5 p-1 rounded-2xl mb-8 backdrop-blur-md overflow-x-auto border border-white/10 shadow-lg w-fit mx-auto lg:mx-0">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="[
            'px-8 py-3 rounded-xl text-sm font-black transition-all duration-300 ml-2 whitespace-nowrap',
            activeTab === tab.id
              ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/25 scale-105'
              : 'text-emerald-100 hover:bg-white/5 hover:text-white'
          ]"
          @click="activeTab = tab.id"
        >
          {{ tab.name }}
        </button>
      </div>

      <!-- Tab Content -->
      <div class="space-y-8">
        <!-- Overview Tab -->
        <div
          v-show="activeTab === 'overview'"
          class="space-y-8"
        >
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Info Column -->
            <div class="lg:col-span-2 space-y-8">
              <!-- Basic Info -->
              <Card class="!p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                  <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">
                      المؤلف / القارئ
                    </h3>
                    <div class="flex flex-wrap gap-2">
                      <div
                        v-if="audio.authors?.length"
                        class="flex flex-wrap gap-2"
                      >
                        <Link 
                          v-for="author in (audio.authors || [])" 
                          :key="author.id" 
                          :href="route('authors.show', author.slug)"
                          class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-50 dark:bg-white/5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors group"
                        >
                          <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400">{{ author.name }}</span>
                        </Link>
                      </div>
                      <span
                        v-else
                        class="text-sm text-gray-400 italic"
                      >غير محدد</span>
                    </div>
                  </div>
                  <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">
                      الناشر / المنتج
                    </h3>
                    <div
                      v-if="audio.versions?.[0]?.publisher"
                      class="flex items-center gap-3"
                    >
                      <span class="text-base font-bold text-gray-800 dark:text-white">{{ audio.versions?.[0]?.publisher?.name }}</span>
                    </div>
                    <span
                      v-else
                      class="text-sm text-gray-400 italic"
                    >غير محدد</span>
                  </div>
                </div>
                <div class="mt-8 pt-8 border-t border-gray-100 dark:border-white/5 grid grid-cols-1 md:grid-cols-2 gap-8">
                  <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">
                      التصنيفات
                    </h3>
                    <div class="flex flex-wrap gap-2">
                      <Badge
                        v-for="cat in audio.categories"
                        :key="cat.id"
                        color="emerald"
                        size="md"
                      >
                        {{ cat.name }}
                      </Badge>
                      <span
                        v-if="!audio.categories?.length"
                        class="text-sm text-gray-400 italic"
                      >لا يوجد تصنيفات</span>
                    </div>
                  </div>
                  <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">
                      الوسوم
                    </h3>
                    <div class="flex flex-wrap gap-2">
                      <Badge
                        v-for="tag in audio.tags"
                        :key="tag.id"
                        color="gray"
                        size="sm"
                      >
                        #{{ tag.name }}
                      </Badge>
                      <span
                        v-if="!audio.tags?.length"
                        class="text-sm text-gray-400 italic"
                      >لا يوجد وسوم</span>
                    </div>
                  </div>
                </div>
              </Card>

              <!-- Full Description -->
              <Card class="!p-8">
                <h3 class="text-lg font-black text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                  <svg
                    class="w-5 h-5 text-emerald-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h7"
                  /></svg>
                  الوصف الكامل
                </h3>
                <div class="prose prose-lg dark:prose-invert max-w-none prose-p:text-gray-600 dark:prose-p:text-gray-300">
                  <p
                    v-if="audio.description"
                    class="whitespace-pre-line"
                  >
                    {{ audio.description }}
                  </p>
                  <p
                    v-else
                    class="italic text-gray-400 text-sm"
                  >
                    لا يوجد وصف متاح لهذا التسجيل.
                  </p>
                </div>
              </Card>
            </div>

            <!-- Right Stats Column -->
            <div class="lg:col-span-1 space-y-8">
              <div class="grid grid-cols-2 lg:grid-cols-1 gap-4">
                <Card class="!p-6 !bg-emerald-500/5 group hover:!bg-emerald-500/10 transition-colors border-emerald-500/10">
                  <div class="text-emerald-500 mb-2">
                    <svg
                      class="w-8 h-8"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    ><path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    /></svg>
                  </div>
                  <div class="text-3xl font-black text-gray-900 dark:text-white mb-1 font-mono">
                    {{ audio.duration || '0' }}
                  </div>
                  <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                    المدة (ثواني)
                  </div>
                </Card>

                <Card class="!p-6 group hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                  <div class="text-gray-400 mb-2 group-hover:text-amber-500 transition-colors">
                    <svg
                      class="w-8 h-8"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    ><path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    /></svg>
                  </div>
                  <div class="text-3xl font-black text-gray-900 dark:text-white mb-1 font-mono">
                    {{ audio.versions?.[0]?.published_year || '-' }}
                  </div>
                  <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                    سنة الإصدار
                  </div>
                </Card>
              </div>

              <Card class="!p-6">
                <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">
                  آخر نشاط
                </h4>
                <div class="space-y-4">
                  <div class="flex items-center gap-3 text-sm">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-gray-500">تاريخ الإضافة:</span>
                    <span class="font-mono font-bold text-gray-700 dark:text-gray-300 ltr:ml-auto rtl:mr-auto">{{ formatDate(audio.created_at) }}</span>
                  </div>
                </div>
              </Card>
            </div>
          </div>
        </div>

        <!-- Content Tab -->
        <div
          v-show="activeTab === 'content'"
          class="space-y-8"
        >
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <Card class="!p-8 h-full flex flex-col justify-center items-center text-center">
              <div class="w-20 h-20 rounded-full bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-500 mb-6 group-hover:scale-110 transition-transform">
                <svg
                  class="w-10 h-10"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1.5"
                  d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
                /></svg>
              </div>
              <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">
                الاستماع للتسجيل
              </h3>
              <p class="text-gray-500 text-sm max-w-sm mb-8">
                استمع للملف الصوتي مباشرة
              </p>
                            
              <audio 
                v-if="audio.versions?.[0]?.file_path || audio.file_path" 
                controls 
                class="w-full rounded-2xl shadow-sm bg-gray-50 dark:bg-black/20"
              >
                <source
                  :src="'/storage/' + (audio.versions?.[0]?.file_path || audio.file_path)"
                  type="audio/mpeg"
                >
                متصفحك لا يدعم تشغيل الصوت.
              </audio>
              <div
                v-else
                class="py-4 text-amber-500 font-bold bg-amber-50 dark:bg-amber-500/10 px-6 rounded-xl text-sm"
              >
                لا يوجد ملف صوتي مرفق
              </div>
            </Card>

            <Card class="!p-8 h-full flex flex-col justify-center items-center text-center">
              <div class="w-20 h-20 rounded-full bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-500 mb-6 group-hover:scale-110 transition-transform">
                <svg
                  class="w-10 h-10"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1.5"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                /></svg>
              </div>
              <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">
                المحتوى النصي
              </h3>
              <p class="text-gray-500 text-sm max-w-sm mb-8">
                تحرير النصوص، التفريغ، والهوامش عبر المحرر المتقدم
              </p>
              <Link 
                v-if="first_content_slug"
                :href="route('studio.show', { type: 'audio', slug: audio.slug })"
              >
                <PrimaryButton>
                  فتح المحرر المتقدم
                </PrimaryButton>
              </Link>
              <div
                v-else
                class="py-2 text-gray-400 text-sm"
              >
                لا يوجد محتوى نصي مرتبط
              </div>
            </Card>
          </div>
        </div>

        <!-- Details Tab -->
        <div
          v-show="activeTab === 'details'"
          class="space-y-8"
        >
          <Card class="!p-0 overflow-hidden">
            <div class="p-8 border-b border-gray-100 dark:border-white/5">
              <h3 class="text-lg font-black text-gray-900 dark:text-white">
                تفاصيل إضافية
              </h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-white/5">
              <div class="grid grid-cols-1 md:grid-cols-3 p-6 hover:bg-gray-50/50 dark:hover:bg-white/2 transition-colors">
                <div class="text-sm font-bold text-gray-500">
                  الرقم التسلسلي
                </div>
                <div class="md:col-span-2 text-sm font-mono font-bold text-gray-900 dark:text-white">
                  {{ audio.formatted_serial_number }}
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-3 p-6 hover:bg-gray-50/50 dark:hover:bg-white/2 transition-colors">
                <div class="text-sm font-bold text-gray-500">
                  العنوان الأصلي
                </div>
                <div class="md:col-span-2 text-sm text-gray-900 dark:text-white">
                  {{ audio.title }}
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-3 p-6 hover:bg-gray-50/50 dark:hover:bg-white/2 transition-colors">
                <div class="text-sm font-bold text-gray-500">
                  تاريخ الإنشاء
                </div>
                <div class="md:col-span-2 text-sm font-mono text-gray-900 dark:text-white">
                  {{ new Date(audio.created_at).toLocaleString('ar-EG') }}
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-3 p-6 hover:bg-gray-50/50 dark:hover:bg-white/2 transition-colors">
                <div class="text-sm font-bold text-gray-500">
                  آخر تحديث
                </div>
                <div class="md:col-span-2 text-sm font-mono text-gray-900 dark:text-white">
                  {{ new Date(audio.updated_at).toLocaleString('ar-EG') }}
                </div>
              </div>
            </div>
          </Card>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
