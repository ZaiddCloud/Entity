<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import { ref } from 'vue';

const props = defineProps({
    book: Object,
    first_content_slug: String,
});

const activeTab = ref('overview');

const tabs = [
    { id: 'overview', label: 'نظرة عامة' },
    { id: 'details', label: 'التفاصيل التقنية' },
    { id: 'reviews', label: 'المناقشات' },
];
</script>

<template>
  <AuthenticatedLayout :title="book.title">
    <!-- Custom Hero Section replacing standard header -->
    <template #header>
      <div class="hidden" />
    </template>

    <div class="relative -mt-8 -mx-8 mb-8">
      <!-- Hero Background with Blur -->
      <div class="absolute inset-0 h-[500px] overflow-hidden">
        <div class="absolute inset-0 bg-emerald-950/90 z-10" />
        <img 
          v-if="book.versions?.[0]?.cover_path || book.cover_path"
          :src="'/storage/' + (book.versions?.[0]?.cover_path || book.cover_path)" 
          class="w-full h-full object-cover filter blur-3xl opacity-40 scale-110 grayscale-[0.2]"
        >
        <div
          v-else
          class="w-full h-full bg-gradient-to-br from-emerald-950 to-black"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-gray-50 dark:from-[#050505] to-transparent z-20" />
      </div>

      <!-- Hero Content -->
      <div class="relative z-30 px-8 pt-12">
        <div class="max-w-7xl mx-auto">
          <div class="flex flex-col md:flex-row gap-10 items-start">
            <!-- Book Cover (3D Effect) -->
            <div class="group relative w-64 shrink-0 mx-auto md:mx-0 perspective-1000">
              <div class="relative w-64 aspect-[2/3] rounded-xl shadow-2xl transition-transform duration-500 transform group-hover:rotate-y-6 group-hover:rotate-x-6 preserve-3d">
                <img 
                  v-if="book.versions?.[0]?.cover_path || book.cover_path"
                  :src="'/storage/' + (book.versions?.[0]?.cover_path || book.cover_path)"
                  class="w-full h-full object-cover rounded-xl shadow-black/50 ring-1 ring-lime-400/30"
                >
                <div
                  v-else
                  class="w-full h-full flex items-center justify-center bg-emerald-900 text-emerald-400 rounded-xl border border-lime-400/20"
                >
                  <span class="text-xs font-serif italic">No Cover</span>
                </div>
                                
                <!-- Shine Effect -->
                <div class="absolute inset-0 bg-gradient-to-tr from-lime-400/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity rounded-xl pointer-events-none" />
              </div>
            </div>

            <!-- Info -->
            <div class="flex-1 text-center md:text-right pt-4">
              <div class="flex items-center justify-center md:justify-start gap-3 mb-6">
                <Badge
                  variant="gray"
                  class="!bg-emerald-500/10 !text-lime-400 !border-lime-400/20 !text-xs !px-3 !py-1 backdrop-blur-md shadow-[0_0_10px_rgba(132,204,22,0.2)]"
                >
                  كتاب
                </Badge>
                <span class="text-emerald-200/60 font-mono text-sm tracking-widest">#{{ book.formatted_serial_number }}</span>
              </div>

              <h1 class="text-4xl md:text-6xl font-black text-white mb-6 leading-tight drop-shadow-2xl font-sans tracking-tight">
                {{ book.title }}
              </h1>

              <div class="flex flex-wrap items-center justify-center md:justify-start gap-6 text-emerald-100/70 mb-10 border-b border-emerald-500/10 pb-10">
                <div class="flex items-center gap-2 group">
                  <span class="p-1 rounded-full bg-emerald-500/10 group-hover:bg-lime-400/20 transition-colors">
                    <svg
                      class="h-4 w-4 text-lime-400"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    ><path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    /></svg>
                  </span>
                  <span class="font-medium text-emerald-50">
                    {{ book.authors?.map(a => a.name).join('، ') || 'مؤلف غير محدد' }}
                  </span>
                </div>
                <span class="hidden md:inline text-emerald-500/30 text-xl font-light">|</span>
                <div class="flex items-center gap-2 group">
                  <span class="p-1 rounded-full bg-emerald-500/10 group-hover:bg-lime-400/20 transition-colors">
                    <svg
                      class="h-4 w-4 text-lime-400"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    ><path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    /></svg>
                  </span>
                  <span class="font-medium text-emerald-50">{{ book.versions?.[0]?.published_year || '----' }}</span>
                </div>
                <span class="hidden md:inline text-emerald-500/30 text-xl font-light">|</span>
                <div class="flex items-center gap-2 group">
                  <span class="p-1 rounded-full bg-emerald-500/10 group-hover:bg-lime-400/20 transition-colors">
                    <svg
                      class="h-4 w-4 text-lime-400"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    ><path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                    /></svg>
                  </span>
                  <span class="font-medium text-emerald-50">{{ book.versions?.[0]?.pages || '-' }} صفحة</span>
                </div>
              </div>

              <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                <Link
                  :href="route('reader.show', { type: 'book', slug: book.slug })"
                  class="inline-flex items-center justify-center px-8 py-4 bg-lime-400 text-emerald-950 rounded-xl font-black text-sm hover:bg-lime-300 transition shadow-lg shadow-lime-400/20 hover:scale-105 active:scale-95 ring-2 ring-lime-400 ring-offset-2 ring-offset-emerald-900"
                >
                  <svg
                    class="w-5 h-5 ml-2 text-emerald-900"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                  /></svg>
                  بدء القراءة الآن
                </Link>

                <Link
                  v-if="first_content_slug"
                  :href="route('studio.show', { type: 'book', slug: first_content_slug })"
                  class="inline-flex items-center justify-center px-8 py-4 bg-emerald-900/50 backdrop-blur-md text-emerald-50 border border-emerald-500/30 rounded-xl font-bold text-sm hover:bg-emerald-800/50 transition hover:scale-105 active:scale-95"
                >
                  محرر المحتوى
                </Link>
                                
                <Link
                  :href="route('books.edit', book.slug)"
                  class="inline-flex items-center justify-center px-6 py-4 bg-transparent text-emerald-200/60 border border-emerald-500/20 rounded-xl font-bold text-sm hover:bg-emerald-500/5 hover:text-emerald-100 transition"
                >
                  <svg
                    class="w-5 h-5 ml-2"
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
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-40">
      <!-- Tabs Navigation -->
      <div class="flex gap-2 mb-8 overflow-x-auto pb-2 scrollbar-hide border-b border-gray-100 dark:border-white/5">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          :class="['px-6 py-3 rounded-t-lg font-bold text-sm transition-all whitespace-nowrap relative', 
                   activeTab === tab.id 
                     ? 'text-emerald-900 dark:text-lime-400 after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-emerald-600 dark:after:bg-lime-400' 
                     : 'text-gray-500 dark:text-emerald-600/60 hover:text-emerald-700 dark:hover:text-emerald-300'
          ]"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="min-h-[400px]">
        <!-- Overview Tab -->
        <div
          v-if="activeTab === 'overview'"
          class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in"
        >
          <div class="lg:col-span-2 space-y-8">
            <Card>
              <h3 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <svg
                  class="w-5 h-5 text-lime-500"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                /></svg>
                نبذة عن الكتاب
              </h3>
              <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-loose text-justify font-normal">
                <p class="whitespace-pre-line">
                  {{ book.description || 'لا يوجد وصف متاح لهذا الكتاب حالياً.' }}
                </p>
              </div>
            </Card>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Card class="bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-900/10 dark:to-transparent border-emerald-100 dark:border-emerald-500/10">
                <h3 class="text-xs font-bold text-emerald-800 dark:text-emerald-400 uppercase tracking-widest mb-4">
                  التصنيفات
                </h3>
                <div class="flex flex-wrap gap-2">
                  <Badge
                    v-for="cat in book.categories"
                    :key="cat.id"
                    variant="success"
                    class="!bg-white dark:!bg-emerald-500/20 !text-emerald-700 dark:!text-emerald-300 !border-emerald-200 dark:!border-emerald-500/20"
                  >
                    {{ cat.name }}
                  </Badge>
                  <span
                    v-if="!book.categories?.length"
                    class="text-sm text-gray-400"
                  >غير مصنف</span>
                </div>
              </Card>
                            
              <Card class="bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-900/10 dark:to-transparent border-emerald-100 dark:border-emerald-500/10">
                <h3 class="text-xs font-bold text-emerald-800 dark:text-emerald-400 uppercase tracking-widest mb-4">
                  الوسوم
                </h3>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="tag in book.tags"
                    :key="tag.id"
                    class="px-2 py-1 rounded-sm bg-white dark:bg-black/20 border border-emerald-100 dark:border-emerald-500/20 text-xs font-medium text-emerald-600 dark:text-emerald-400"
                  >
                    #{{ tag.name }}
                  </span>
                  <span
                    v-if="!book.tags?.length"
                    class="text-sm text-gray-400"
                  >لا يوجد وسوم</span>
                </div>
              </Card>
            </div>
          </div>
                    
          <div class="space-y-6">
            <Card>
              <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">
                ملفات التحميل
              </h3>
              <a 
                v-if="book.versions?.[0]?.file_path || book.file_path" 
                :href="'/storage/' + (book.versions?.[0]?.file_path || book.file_path)"
                target="_blank"
                class="flex items-center justify-between w-full p-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/10 hover:bg-emerald-100 dark:hover:bg-emerald-800/20 border border-emerald-100 dark:border-emerald-500/10 transition-all group cursor-pointer"
              >
                <div class="flex items-center gap-4">
                  <div class="w-10 h-10 rounded-lg bg-white dark:bg-emerald-900/30 border border-emerald-100 dark:border-emerald-500/20 flex items-center justify-center text-red-500 shadow-sm">
                    <svg
                      class="w-5 h-5"
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
                    <div class="text-sm font-bold text-gray-900 dark:text-white font-sans">PDF Document</div>
                    <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono tracking-tight uppercase">Ready for Download</div>
                  </div>
                </div>
                <svg
                  class="w-4 h-4 text-emerald-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                /></svg>
              </a>
              <div
                v-else
                class="text-center py-8 text-gray-400 text-sm"
              >
                لا توجد ملفات مرفقة
              </div>
            </Card>
          </div>
        </div>

        <!-- Details Tab -->
        <div
          v-if="activeTab === 'details'"
          class="animate-fade-in"
        >
          <Card>
            <h3 class="text-lg font-black text-gray-900 dark:text-white mb-6">
              البيانات التقنية
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-emerald-900/5 border border-gray-100 dark:border-emerald-500/10">
                <dt class="text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-2">
                  ISBN
                </dt>
                <dd class="text-lg font-mono font-bold text-gray-800 dark:text-emerald-100">
                  {{ book.versions?.[0]?.isbn || '-' }}
                </dd>
              </div>
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-emerald-900/5 border border-gray-100 dark:border-emerald-500/10">
                <dt class="text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-2">
                  الناشر
                </dt>
                <dd class="text-lg font-bold text-gray-800 dark:text-emerald-100">
                  {{ book.versions?.[0]?.publisher?.name || 'غير معروف' }}
                </dd>
              </div>
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-emerald-900/5 border border-gray-100 dark:border-emerald-500/10">
                <dt class="text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-2">
                  تاريخ الإضافة
                </dt>
                <dd class="text-lg font-bold text-gray-800 dark:text-emerald-100">
                  {{ new Date(book.created_at).toLocaleDateString('ar-EG') }}
                </dd>
              </div>
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-emerald-900/5 border border-gray-100 dark:border-emerald-500/10">
                <dt class="text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-2">
                  اخر تحديث
                </dt>
                <dd class="text-lg font-bold text-gray-800 dark:text-emerald-100">
                  {{ new Date(book.updated_at).toLocaleDateString('ar-EG') }}
                </dd>
              </div>
            </div>
          </Card>
        </div>

        <!-- Reviews Tab -->
        <div
          v-if="activeTab === 'reviews'"
          class="animate-fade-in"
        >
          <Card>
            <div class="flex items-center justify-between mb-8">
              <h3 class="text-lg font-black text-gray-900 dark:text-white">
                التعليقات والمناقشات
              </h3>
              <Badge
                variant="success"
                class="!bg-emerald-100 dark:!bg-emerald-500/10 !text-emerald-800 dark:!text-emerald-300"
              >
                {{ book.comments?.length || 0 }} تعليق
              </Badge>
            </div>
                        
            <div class="space-y-6">
              <div
                v-for="comment in book.comments"
                :key="comment.id"
                class="flex gap-4 p-6 rounded-lg bg-emerald-50 dark:bg-emerald-900/5 border border-emerald-100 dark:border-emerald-500/10"
              >
                <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-800 flex items-center justify-center font-black text-emerald-600 dark:text-emerald-300 shrink-0">
                  {{ comment.user?.name?.charAt(0) || '?' }}
                </div>
                <div>
                  <div class="flex items-center gap-2 mb-1">
                    <span class="font-bold text-gray-900 dark:text-white">{{ comment.user?.name || 'مستخدم' }}</span>
                    <span class="text-xs text-gray-400">• {{ new Date(comment.created_at).toLocaleDateString('ar-EG') }}</span>
                  </div>
                  <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                    {{ comment.content }}
                  </p>
                </div>
              </div>
                            
              <div
                v-if="!book.comments?.length"
                class="text-center py-12"
              >
                <div class="w-16 h-16 bg-gray-100 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                  <svg
                    class="w-8 h-8"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                  /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                  لا توجد تعليقات حتى الآن
                </h3>
                <p class="text-gray-500 text-sm">
                  كن أول من يشارك برأيه حول هذا الكتاب
                </p>
              </div>
            </div>
          </Card>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.perspective-1000 {
    perspective: 1000px;
}
.preserve-3d {
    transform-style: preserve-3d;
}
.rotate-y-6 {
    transform: rotateY(-10deg) rotateX(5deg);
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
