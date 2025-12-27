<template>
    <Head title="لوحة التحكم" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight text-right">لوحة التحكم</h2>
        </template>

        <div class="py-12" dir="rtl">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-6 mr-2">إحصائيات المنصة</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-10">
                    <!-- Books -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-2xl p-5 border-b-4 border-blue-500 transform hover:scale-105 transition-transform">
                        <div class="flex items-center">
                            <div class="p-3 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 ml-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">الكتب</p>
                                <p class="text-2xl font-black dark:text-white">{{ stats.books }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Manuscripts -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-2xl p-5 border-b-4 border-amber-500 transform hover:scale-105 transition-transform">
                        <div class="flex items-center">
                            <div class="p-3 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 ml-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">المخطوطات</p>
                                <p class="text-2xl font-black dark:text-white">{{ stats.manuscripts }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Audios -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-2xl p-5 border-b-4 border-green-500 transform hover:scale-105 transition-transform">
                        <div class="flex items-center">
                            <div class="p-3 rounded-xl bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 ml-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">الصوتيات</p>
                                <p class="text-2xl font-black dark:text-white">{{ stats.audios }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Videos -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-2xl p-5 border-b-4 border-purple-500 transform hover:scale-105 transition-transform">
                        <div class="flex items-center">
                            <div class="p-3 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 ml-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">المرئيات</p>
                                <p class="text-2xl font-black dark:text-white">{{ stats.videos }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Collections -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-2xl p-5 border-b-4 border-rose-500 transform hover:scale-105 transition-transform">
                        <div class="flex items-center">
                            <div class="p-3 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 ml-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">المجموعات</p>
                                <p class="text-2xl font-black dark:text-white">{{ stats.collections }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secondary Stats Row -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800 text-center">
                        <span class="text-xs text-gray-500 font-bold">السلاسل</span>
                        <div class="text-xl font-bold dark:text-gray-200">{{ stats.series }}</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800 text-center">
                        <span class="text-xs text-gray-500 font-bold">التصنيفات</span>
                        <div class="text-xl font-bold dark:text-gray-200">{{ stats.categories }}</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800 text-center">
                        <span class="text-xs text-gray-500 font-bold">الوسوم</span>
                        <div class="text-xl font-bold dark:text-gray-200">{{ stats.tags }}</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800 text-center">
                        <span class="text-xs text-gray-500 font-bold">التعليقات</span>
                        <div class="text-xl font-bold dark:text-gray-200">{{ stats.comments }}</div>
                    </div>
                </div>

                <!-- Recent Activity & Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Recent Activity -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-secondary-100">أحدث النشاطات</h3>
                            <Link :href="route('activities.index')" class="text-sm text-purple-600 hover:text-purple-500 font-bold transition-colors">عرض الكل</Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="item in recent" :key="item.id" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/40 rounded-2xl hover:bg-gray-100 dark:hover:bg-gray-900 transition-all border border-transparent hover:border-purple-200 dark:hover:border-purple-900">
                                <div class="flex items-center space-x-4 space-x-reverse">
                                    <div :class="{
                                        'p-3 rounded-xl shadow-sm': true,
                                        'bg-blue-100 dark:bg-blue-900 text-blue-600': item.type === 'book',
                                        'bg-purple-100 dark:bg-purple-900 text-purple-600': item.type === 'video',
                                        'bg-green-100 dark:bg-green-900 text-green-600': item.type === 'audio',
                                        'bg-amber-100 dark:bg-amber-900 text-amber-600': item.type === 'manuscript',
                                        'bg-rose-100 dark:bg-rose-900 text-rose-600': item.type === 'collection',
                                        'bg-indigo-100 dark:bg-indigo-900 text-indigo-600': item.type === 'series'
                                    }">
                                        <svg v-if="item.type === 'book'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        <svg v-else-if="item.type === 'video'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                {{ translateActivity(item.activity_type) }}
                                            </span>
                                            <Link 
                                                v-if="item.entity_id"
                                                :href="item.type === 'book' || item.type === 'video' || item.type === 'audio' || item.type === 'manuscript' ? route(item.type + 's.show', item.entity_id) : '#'" 
                                                class="text-gray-900 dark:text-white font-bold hover:text-purple-500 transition-colors"
                                            >
                                                {{ item.entity_title }}
                                            </Link>
                                            <span v-else class="text-gray-400 font-bold">{{ item.entity_title }}</span>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">بواسطة <span class="text-purple-500 font-medium">{{ item.user_name }}</span> • {{ formatDate(item.created_at) }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <Link :href="route('activities.show', item.id)" class="p-2 text-gray-400 hover:text-purple-500 transition-colors" title="عرض التفاصيل">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </Link>
                                </div>
                            </div>
                            <div v-if="recent.length === 0" class="text-center py-12">
                                <p class="text-gray-500">لا توجد نشاطات حديثة حالياً.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">إجراءات سريعة</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <Link :href="route('books.create')" class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all text-gray-700 dark:text-gray-300 group">
                                <div class="flex items-center">
                                    <div class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-lg ml-3 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                    <span class="font-bold">إضافة كتاب جديد</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </Link>
                            
                            <Link :href="route('videos.create')" class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all text-gray-700 dark:text-gray-300 group">
                                <div class="flex items-center">
                                    <div class="p-2 bg-purple-50 dark:bg-purple-900/20 text-purple-600 rounded-lg ml-3 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    </div>
                                    <span class="font-bold">رفع مرئية</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </Link>

                            <Link :href="route('categories.create')" class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all text-gray-700 dark:text-gray-300 group">
                                <div class="flex items-center">
                                    <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 rounded-lg ml-3 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                    </div>
                                    <span class="font-bold">إضافة تصنيف</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </Link>

                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <Link :href="route('deletions.index')" class="flex items-center text-sm text-red-500 hover:text-red-600 font-bold transition-colors">
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    سجل المحذوفات
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    recent: Array,
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const translateActivity = (type) => {
    const map = {
        'created': 'إضافة',
        'updated': 'تحديث',
        'deleted': 'حذف',
        'viewed': 'مشاهدة',
        'restored': 'استعادة'
    };
    return map[type] || type;
};
</script>

<style scoped>
/* إضافة لمسات جمالية للخلفية إذا لزم الأمر */
</style>
