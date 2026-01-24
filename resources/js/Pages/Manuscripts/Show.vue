<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    manuscript: Object,
    first_content_slug: String,
    siblings: {
        type: Array,
        default: () => []
    },
});

const activeTab = ref('overview');

const tabs = [
    { id: 'overview', name: 'نبذة عامة' },
    { id: 'content', name: 'الملف والمحتوى' },
    { id: 'details', name: 'البيانات التفصيلية' },
];
</script>

<template>
  <Head v-if="manuscript" :title="manuscript.title" />
  <Head v-else title="مخطوطة غير موجودة" />

  <AuthenticatedLayout v-if="manuscript" :title="manuscript.title">
    <!-- Hide Default Header -->
    <template #header>
      <div class="hidden" />
    </template>



    
    <div class="-mt-8 -mx-8 mb-8">
      <!-- Hero Section -->
      <div class="relative w-full overflow-hidden bg-emerald-950 min-h-[400px] flex items-end pb-12">
        <!-- Background Pattern -->
        <div
          class="absolute inset-0 opacity-10"
          style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23a3e635\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"
        />
                
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-emerald-950 via-emerald-950/80 to-transparent" />

        <div class="relative z-10 w-full max-w-7xl mx-auto px-8">
          <div class="flex flex-col md:flex-row items-center md:items-end gap-8">
            <!-- Cover Image / Placeholder -->
            <div class="w-32 h-48 md:w-48 md:h-72 rounded-2xl bg-gray-800 border-4 border-emerald-500/30 shadow-2xl flex-shrink-0 overflow-hidden relative group">
              <img 
                v-if="manuscript.cover_path" 
                :src="'/storage/' + manuscript.cover_path" 
                :alt="manuscript.title"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
              >
              <div
                v-else
                class="w-full h-full flex flex-col items-center justify-center text-emerald-500/50 bg-gradient-to-br from-gray-900 to-black"
              >
                <svg
                  class="w-16 h-16 mb-2 opacity-50"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1"
                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                /></svg>
                <span class="text-xs font-bold uppercase tracking-widest">بدون غلاف</span>
              </div>
            </div>

            <!-- Info -->
            <div class="flex-1 text-center md:text-right pb-4">
              <div class="flex items-center justify-center md:justify-start gap-2 mb-2">
                <span class="px-3 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-black uppercase tracking-wider backdrop-blur-sm">
                  {{ manuscript.formatted_serial_number }}
                </span>
                <span
                  v-if="manuscript.century"
                  class="px-3 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-black backdrop-blur-sm"
                >
                  {{ manuscript.century }}
                </span>
              </div>

              <h1 class="text-3xl md:text-5xl font-black text-white mb-4 tracking-tight leading-tight">
                {{ manuscript.title }}
              </h1>
                            
              <p class="text-lg text-emerald-200/80 font-medium mb-6 leading-relaxed max-w-2xl mx-auto md:mx-0">
                {{ manuscript.description ? manuscript.description.substring(0, 150) + (manuscript.description.length > 150 ? '...' : '') : 'لا يوجد وصف متاح لهذه المخطوطة.' }}
              </p>

              <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                <Link :href="route('dev.manuscripter', { manuscript: manuscript.slug })">
                  <PrimaryButton class="!bg-lime-400 !text-black hover:!bg-lime-300 !border-0 !text-sm !px-6 !py-3 !rounded-xl !shadow-[0_0_20px_rgba(163,230,53,0.3)] flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    استعراض المخطوطة (غرفة المعاينة)
                  </PrimaryButton>
                </Link>

                <Link
                  :href="route('studio.show', { type: 'manuscript', slug: first_content_slug })"
                >
                  <button class="px-6 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-white font-bold text-sm border border-white/10 backdrop-blur-sm transition-all flex items-center gap-2 group">
                    <span class="w-8 h-8 rounded-lg bg-lime-500/20 text-lime-400 flex items-center justify-center group-hover:bg-lime-500 group-hover:text-black transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </span>
                    <span>تحرير في الأستوديو</span>
                  </button>
                </Link>
                
                <Link :href="route('manuscripts.edit', manuscript.slug)">
                  <button class="px-6 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-white font-bold text-sm border border-white/10 backdrop-blur-sm transition-all flex items-center gap-2">
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
                    بيانات المخطوطة
                  </button>
                </Link>
              </div>
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
        class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up"
      >
        <div class="lg:col-span-2 space-y-8">
          <!-- Basic Info -->
          <Card class="!p-8">
            <h3 class="font-black text-xl mb-6 flex items-center gap-2 dark:text-white">
              <span class="w-1 h-6 bg-emerald-500 rounded-full" />
              بيانات المخطوطة
            </h3>
                        
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div
                v-if="manuscript.authors?.length"
                class="p-4 bg-gray-50 dark:bg-white/5 rounded-2xl"
              >
                <span class="block text-xs text-gray-400 font-bold mb-1">المؤلف / الناسخ</span>
                <div class="font-bold text-gray-800 dark:text-gray-200">
                  {{ manuscript.authors?.map(a => a?.name || 'غير معروف').join('، ') }}
                </div>
              </div>
                            
              <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-2xl">
                <span class="block text-xs text-gray-400 font-bold mb-1">المصدر / المكتبة</span>
                <div class="font-bold text-gray-800 dark:text-gray-200">
                  {{ manuscript.versions?.[0]?.publisher?.name || 'غير محدد' }}
                </div>
              </div>
            </div>

            <div class="mt-8">
              <h4 class="font-bold text-sm text-gray-500 dark:text-gray-400 mb-4">
                التصنيفات والوسوم
              </h4>
              <div class="flex flex-wrap gap-2">
                <Badge
                  v-for="cat in manuscript.categories || []"
                  :key="cat.id"
                  color="emerald"
                >
                  {{ cat.name }}
                </Badge>
                <Badge
                  v-for="tag in manuscript.tags || []"
                  :key="tag.id"
                  color="gray"
                >
                  {{ tag.name }}
                </Badge>
                <span
                  v-if="!manuscript.categories?.length && !manuscript.tags?.length"
                  class="text-sm text-gray-400 italic"
                >لا يوجد</span>
              </div>
            </div>
          </Card>

          <!-- Description Full -->
          <Card
            v-if="manuscript.description"
            class="!p-8"
          >
            <h3 class="font-black text-lg mb-4 text-gray-900 dark:text-white">
              الوصف الكامل
            </h3>
            <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-loose">
              {{ manuscript.description }}
            </div>
          </Card>
        </div>
                
        <div class="space-y-6">
          <!-- Quick Stats -->
          <Card class="!p-6 !bg-gradient-to-br !from-gray-900 !to-black !border-white/5">
            <h3 class="font-black text-sm mb-6 text-gray-400 uppercase tracking-widest">
              إحصائيات
            </h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                <span class="font-bold text-sm text-gray-300">القرن</span>
                <span class="font-black text-amber-400">{{ manuscript.century_label || manuscript.century || 'غير محدد' }}</span>
              </div>
              <div v-if="manuscript.copy_date" class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                <span class="font-bold text-sm text-gray-300">تاريخ النسخ</span>
                <span class="font-black text-emerald-400">{{ manuscript.copy_date }}</span>
              </div>
              <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                <span class="font-bold text-sm text-gray-300">سنة النشر</span>
                <span class="font-black text-emerald-400">{{ manuscript.versions?.[0]?.published_year || '-' }}</span>
              </div>
              <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                <span class="font-bold text-sm text-gray-300">عدد الأوراق</span>
                <span class="font-black text-white">{{ manuscript.versions?.[0]?.pages || manuscript.pages || '0' }}</span>
              </div>
            </div>
          </Card>

          <!-- Other Copies / Siblings -->
          <Card v-if="siblings && siblings.length > 0" class="!p-6 !border-emerald-500/20">
            <h3 class="font-black text-sm mb-4 text-emerald-600 dark:text-emerald-400 uppercase tracking-widest flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              نسخ أخرى ({{ siblings.length }})
            </h3>
            <div class="space-y-2">
              <Link 
                v-for="sibling in siblings" 
                :key="sibling.id"
                :href="route('manuscripts.show', sibling.slug)"
                class="block p-3 bg-gray-50 dark:bg-white/5 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-xl transition-all border border-transparent hover:border-emerald-500/30 group"
              >
                <div class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                  {{ sibling.title }}
                </div>
                <div v-if="sibling.catalog_number" class="text-xs text-gray-500 mt-1">
                  {{ sibling.catalog_number }}
                </div>
              </Link>
            </div>
          </Card>

          <!-- Last Activity -->
          <Card class="!p-6">
            <h3 class="font-black text-sm mb-4 text-gray-900 dark:text-white">
              آخر نشاط
            </h3>
            <div class="text-xs text-gray-500">
              تم الإنشاء: {{ new Date(manuscript.created_at).toLocaleDateString('ar-EG') }}
            </div>
          </Card>
        </div>
      </div>

      <!-- Tab Content: Files & Content -->
      <div
        v-if="activeTab === 'content'"
        class="space-y-8 animate-fade-in-up"
      >
        <Card class="!p-8">
          <div class="flex items-center justify-between mb-6">
            <h3 class="font-black text-xl flex items-center gap-2 dark:text-white">
              <svg
                class="w-6 h-6 text-emerald-500"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              /></svg>
              الملفات المرفقة
            </h3>
          </div>
                
          <div
            v-if="manuscript.versions?.[0]?.file_path || manuscript.file_path"
            class="p-6 bg-gray-50 dark:bg-white/5 rounded-3xl border border-gray-100 dark:border-white/5 flex items-center justify-between group hover:border-emerald-500/30 transition-colors"
          >
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-500/20 flex items-center justify-center text-red-500">
                <svg
                  class="w-6 h-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                /></svg>
              </div>
              <div>
                <h4 class="font-bold text-gray-900 dark:text-white">
                  نسخة المخطوطة الكلية
                </h4>
                <p class="text-xs text-gray-500">
                  ملف PDF عالي الجودة
                </p>
              </div>
            </div>
            <a
              :href="'/storage/' + (manuscript.versions?.[0]?.file_path || manuscript.file_path)"
              target="_blank"
            >
              <PrimaryButton class="flex items-center gap-2">
                <svg
                  class="w-4 h-4"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                /></svg>
                تحميل
              </PrimaryButton>
            </a>
          </div>
          <div
            v-else
            class="text-center py-12 text-gray-400"
          >
            لا توجد ملفات مرفقة لهذه المخطوطة.
          </div>
        </Card>

        <Card
          v-if="first_content_slug"
          class="!p-8"
        >
          <div class="flex items-center justify-between mb-6">
            <h3 class="font-black text-xl flex items-center gap-2 dark:text-white">
              <svg
                class="w-6 h-6 text-emerald-500"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
              /></svg>
              المحتوى النصي
            </h3>
          </div>
                    
          <div class="p-8 bg-gray-50 dark:bg-white/5 rounded-3xl border border-gray-100 dark:border-white/5 text-center">
            <p class="text-gray-500 mb-6">
              يمكنك استعراض وتحرير المحتوى النصي للمخطوطة، وإضافة الحواشي والتعليقات العلمية عبر المحرر المتطور.
            </p>
            <Link :href="`/editor/manuscript/${first_content_slug}`">
              <PrimaryButton class="!px-8 !py-4 !text-base shadow-xl shadow-emerald-500/20">
                فتح محرر النصوص
              </PrimaryButton>
            </Link>
          </div>
        </Card>
      </div>
            
      <!-- Tab Content: Details -->
      <div
        v-if="activeTab === 'details'"
        class="space-y-8 animate-fade-in-up"
      >
        <Card class="!p-8">
          <h3 class="font-black text-xl mb-6 dark:text-white">
            البيانات التفصيلية
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12">
            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-white/5">
              <span class="text-gray-500 font-medium">الرقم التسلسلي</span>
              <span class="font-bold text-gray-900 dark:text-white font-mono">{{ manuscript.formatted_serial_number }}</span>
            </div>
            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-white/5">
              <span class="text-gray-500 font-medium">العنوان</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ manuscript.title }}</span>
            </div>
            <div v-if="manuscript.original_title" class="flex justify-between py-3 border-b border-gray-100 dark:border-white/5">
              <span class="text-gray-500 font-medium">العنوان المثبت</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ manuscript.original_title }}</span>
            </div>
            <div v-if="manuscript.catalog_number" class="flex justify-between py-3 border-b border-gray-100 dark:border-white/5">
              <span class="text-gray-500 font-medium">رقم المخطوط</span>
              <span class="font-bold text-gray-900 dark:text-white font-mono">{{ manuscript.catalog_number }}</span>
            </div>
            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-white/5">
              <span class="text-gray-500 font-medium">القرن</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ manuscript.century_label || manuscript.century || '-' }}</span>
            </div>
            <div v-if="manuscript.copy_date" class="flex justify-between py-3 border-b border-gray-100 dark:border-white/5">
              <span class="text-gray-500 font-medium">تاريخ النسخ</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ manuscript.copy_date }}</span>
            </div>
          </div>
        </Card>

        <!-- Physical Description -->
        <Card class="!p-8">
          <h3 class="font-black text-xl mb-6 dark:text-white flex items-center gap-2">
            <span class="w-1 h-6 bg-amber-500 rounded-full" />
            الوصف الفيزيائي
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12">
            <div v-if="manuscript.scribe" class="flex justify-between py-3 border-b border-gray-100 dark:border-white/5">
              <span class="text-gray-500 font-medium">الناسخ</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ manuscript.scribe }}</span>
            </div>
            <div v-if="manuscript.madhab" class="flex justify-between py-3 border-b border-gray-100 dark:border-white/5">
              <span class="text-gray-500 font-medium">المذهب</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ manuscript.madhab }}</span>
            </div>
            <div v-if="manuscript.script_type" class="flex justify-between py-3 border-b border-gray-100 dark:border-white/5">
              <span class="text-gray-500 font-medium">نوع الخط</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ manuscript.script_type }}</span>
            </div>
            <div v-if="manuscript.lines_per_page" class="flex justify-between py-3 border-b border-gray-100 dark:border-white/5">
              <span class="text-gray-500 font-medium">مسطرة الصفحة</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ manuscript.lines_per_page }} سطر</span>
            </div>
            <div v-if="manuscript.dimensions" class="flex justify-between py-3 border-b border-gray-100 dark:border-white/5">
              <span class="text-gray-500 font-medium">المقاس</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ manuscript.dimensions }}</span>
            </div>
            <div v-if="manuscript.parts" class="flex justify-between py-3 border-b border-gray-100 dark:border-white/5">
              <span class="text-gray-500 font-medium">عدد الأجزاء</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ manuscript.parts }}</span>
            </div>
          </div>

          <!-- Inscriptions and Notes -->
          <div v-if="manuscript.inscriptions || manuscript.notes" class="mt-8 space-y-6">
            <div v-if="manuscript.inscriptions" class="p-6 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-white/5">
              <h4 class="font-bold text-sm text-gray-700 dark:text-gray-300 mb-3">القيود والبلاغات</h4>
              <p class="text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-wrap">{{ manuscript.inscriptions }}</p>
            </div>
            <div v-if="manuscript.notes" class="p-6 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-white/5">
              <h4 class="font-bold text-sm text-gray-700 dark:text-gray-300 mb-3">ملاحظات</h4>
              <p class="text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-wrap">{{ manuscript.notes }}</p>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
