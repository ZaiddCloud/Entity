<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    audio: Object,
    first_content_slug: String,
});
</script>

<template>
    <AuthenticatedLayout :title="audio.title">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    <span class="text-gray-400 dark:text-gray-500 ml-2 italic">{{ audio.formatted_serial_number }}</span>
                    تفاصيل الملف الصوتي: {{ audio.title }}
                </h2>
                <div class="flex space-x-2 space-x-reverse">
                    <Link
                        :href="route('audios.edit', audio.slug)"
                        class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 transition duration-150 ease-in-out"
                    >
                        تعديل
                    </Link>
                    <Link
                        :href="route('audios.index')"
                        class="px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 transition duration-150 ease-in-out"
                    >
                        العودة للقائمة
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">المعلومات الأساسية</h3>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">العنوان</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ audio.title }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">المؤلفون / القراء</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            <span v-if="audio.authors && audio.authors.length">
                                                {{ audio.authors.map(a => a.name).join('، ') }}
                                            </span>
                                            <span v-else class="italic text-gray-400">غير محدد</span>
                                        </dd>
                                    </div>

                                    <!-- Version Details -->
                                    <template v-if="audio.versions && audio.versions.length">
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الناشر / المنتج</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                {{ audio.versions[0].publisher?.name || 'غير معروف' }}
                                            </dd>
                                        </div>
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">سنة الإصدار</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ audio.versions[0].published_year || '-' }}</dd>
                                        </div>
                                    </template>
                                </dl>
                            </div>
                            
                            <div>
                                <div v-if="audio.cover_path" class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">الغلاف</h3>
                                    <img :src="'/storage/' + audio.cover_path" alt="Cover" class="w-full max-w-md h-auto rounded-lg shadow-md object-cover">
                                </div>

                                <div v-if="audio.versions?.[0]?.file_path || audio.file_path" class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">الاستماع</h3>
                                    <audio controls class="w-full rounded-lg shadow-md bg-gray-100 dark:bg-gray-700">
                                        <source :src="'/storage/' + (audio.versions?.[0]?.file_path || audio.file_path)" type="audio/mpeg">
                                        متصفحك لا يدعم تشغيل الصوت.
                                    </audio>
                                </div>

                                <!-- Content Editor (MongoDB) -->
                                <div class="mb-8">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">تحرير المحتوى (Transcription)</h3>
                                    <Link 
                                        v-if="first_content_slug"
                                        :href="`/editor/audio/${first_content_slug}`"
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-l from-blue-600 to-blue-500 text-white rounded-xl hover:from-blue-700 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-lg hover:shadow-blue-500/20 group scale-100 hover:scale-[1.02] active:scale-95"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 ml-2 group-hover:rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <div class="text-right">
                                            <div class="font-black text-sm">فتح محرر المحتوى</div>
                                            <div class="text-[10px] opacity-80 font-bold">تحرير النص والمقاطع (MongoDB)</div>
                                        </div>
                                    </Link>
                                </div>
                                
                                <div v-if="audio.description" class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">الوصف</h3>
                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ audio.description }}</p>
                                </div>

                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">التصنيفات والوسوم</h3>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">الوسوم</dt>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="tag in audio.tags" :key="tag.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ tag.name }}
                                        </span>
                                        <span v-if="!audio.tags.length" class="text-sm text-gray-400 italic">لا يوجد وسوم</span>
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">التصنيفات</dt>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="category in audio.categories" :key="category.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ category.name }}
                                        </span>
                                        <span v-if="!audio.categories.length" class="text-sm text-gray-400 italic">لا يوجد تصنيفات</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-gray-200 dark:border-gray-700 pt-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">التعليقات</h3>
                            <div class="space-y-4">
                                <div v-for="comment in audio.comments" :key="comment.id" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-semibold text-sm text-indigo-600 dark:text-indigo-400">{{ comment.user?.name || 'مستخدم' }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ new Date(comment.created_at).toLocaleDateString('ar-EG') }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ comment.content }}</p>
                                </div>
                                <p v-if="!audio.comments.length" class="text-sm text-gray-400 italic">لا يوجد تعليقات بعد</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
