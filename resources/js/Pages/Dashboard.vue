<template>
    <Head title="لوحة التحكم" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="font-black text-2xl bg-clip-text text-transparent bg-gradient-to-r from-purple-600 to-blue-500 dark:from-purple-400 dark:to-blue-300 leading-tight">لوحة التحكم الإدارية</h2>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ currentDate }}</span>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-50 dark:bg-gray-950 min-h-screen" dir="rtl">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
                
                <!-- Welcome Banner -->
                <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 rounded-3xl p-8 shadow-2xl shadow-purple-500/20">
                    <div class="absolute top-0 right-0 -m-12 w-64 h-64 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                    <div class="absolute bottom-0 left-0 -m-8 w-48 h-48 bg-black/10 rounded-full blur-2xl"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <h1 class="text-3xl font-black text-white mb-2">مرحباً بك في النظام الرقمي</h1>
                            <p class="text-indigo-100 text-lg max-w-xl font-medium">هنا يمكنك إدارة محتواك المعرفي، تتبع النشاطات، واستكشاف إحصائيات مكتبتك الشاملة.</p>
                        </div>
                        <div class="flex gap-4">
                            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20 text-center min-w-[100px]">
                                <span class="block text-white/70 text-xs font-bold uppercase mb-1">الكتب</span>
                                <span class="text-2xl font-black text-white">{{ stats.books }}</span>
                            </div>
                            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20 text-center min-w-[100px]">
                                <span class="block text-white/70 text-xs font-bold uppercase mb-1">المؤلفون</span>
                                <span class="text-2xl font-black text-white">{{ stats.authors }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Primary Stats Grid (Glassmorphism) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Books -->
                    <div class="stat-card bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-xl shadow-gray-200/50 dark:shadow-none hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <span class="text-green-500 font-bold text-sm bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-full">+{{ Math.round(stats.books * 0.1) }}%</span>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-gray-500 dark:text-gray-400 text-sm font-bold uppercase tracking-wider">إجمالي الكتب</h3>
                            <p class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ stats.books }}</p>
                        </div>
                    </div>

                    <!-- Authors -->
                    <div class="stat-card bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-xl shadow-gray-200/50 dark:shadow-none hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="p-3 bg-cyan-50 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 rounded-2xl">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-gray-500 dark:text-gray-400 text-sm font-bold uppercase tracking-wider">المؤلفون</h3>
                            <p class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ stats.authors }}</p>
                        </div>
                    </div>

                    <!-- Publishers -->
                    <div class="stat-card bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-xl shadow-gray-200/50 dark:shadow-none hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="p-3 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-2xl">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-gray-500 dark:text-gray-400 text-sm font-bold uppercase tracking-wider">دور النشر</h3>
                            <p class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ stats.publishers }}</p>
                        </div>
                    </div>

                    <!-- Videos & Audio -->
                    <div class="stat-card bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-xl shadow-gray-200/50 dark:shadow-none hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="p-3 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-2xl">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-between items-end">
                            <div>
                                <h3 class="text-gray-500 dark:text-gray-400 text-sm font-bold uppercase tracking-wider">مرئيات وسمعيات</h3>
                                <p class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ stats.videos + stats.audios }}</p>
                            </div>
                            <div class="text-right text-xs text-gray-400">
                                <div>{{ stats.videos }} مرئي</div>
                                <div>{{ stats.audios }} صوتي</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- New Ecosystem: Recent Books Section -->
                    <div class="lg:col-span-2 space-y-8">
                        
                        <!-- Recent Books Carousel-like list -->
                        <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 shadow-xl border border-gray-100 dark:border-gray-800">
                            <div class="flex justify-between items-center mb-8">
                                <h3 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                                    <span class="w-2 h-8 bg-blue-500 rounded-full"></span>
                                    أحدث المضافات المكتبية
                                </h3>
                                <Link :href="route('books.index')" class="text-sm font-bold text-blue-600 hover:text-blue-500 transition-colors">تصفح المكتبة</Link>
                            </div>
                            
                            <div class="space-y-6">
                                <div v-for="book in recentBooks" :key="book.id" class="group flex items-center p-4 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-800/50 border border-transparent hover:border-gray-100 dark:hover:border-gray-700 transition-all">
                                    <div class="w-16 h-20 bg-gray-100 dark:bg-gray-800 rounded-xl overflow-hidden ml-6 flex-shrink-0 shadow-sm transition-transform group-hover:scale-105">
                                        <!-- Placeholder or actual cover -->
                                        <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600">
                                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V12h2v4zm4 0h-2V10h2v6zm-4-8H9V7h2v1z"></path></svg>
                                        </div>
                                    </div>
                                    <div class="flex-grow">
                                        <h4 class="font-bold text-gray-900 dark:text-white group-hover:text-blue-600 transition-colors">{{ book.title }}</h4>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            <span v-for="author in book.authors" :key="author.id" class="text-xs text-indigo-500 dark:text-indigo-400 font-semibold italic">
                                                بقلم: {{ author.name }}
                                            </span>
                                        </div>
                                        <div v-if="book.versions && book.versions.length" class="text-[10px] text-gray-400 mt-2 uppercase font-bold tracking-widest">
                                            الإصدار: {{ book.versions[0].edition_number }} • {{ book.versions[0].published_year }}
                                        </div>
                                    </div>
                                    <div class="mr-auto">
                                        <Link :href="route('books.show', book.slug)" class="p-2 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-blue-600 hover:text-white rounded-xl transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Timeline -->
                        <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 shadow-xl border border-gray-100 dark:border-gray-800">
                             <div class="flex justify-between items-center mb-8">
                                <h3 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                                    <span class="w-2 h-8 bg-purple-500 rounded-full"></span>
                                    سجل العمليات
                                </h3>
                                <Link :href="route('activities.index')" class="text-sm font-bold text-purple-600 hover:text-purple-500 transition-colors">عرض السجل الكامل</Link>
                            </div>

                            <div class="relative space-y-6">
                                <div class="absolute top-0 bottom-0 right-4 w-0.5 bg-gray-100 dark:bg-gray-800 ml-4"></div>
                                
                                <div v-for="item in recent" :key="item.id" class="relative flex items-center gap-6 group">
                                    <div :class="{
                                        'w-8 h-8 rounded-full z-10 flex items-center justify-center ring-4 ring-white dark:ring-gray-900 transition-transform group-hover:scale-125': true,
                                        'bg-blue-500': item.type === 'book',
                                        'bg-purple-500': item.type === 'video',
                                        'bg-green-500': item.type === 'audio',
                                        'bg-amber-500': item.type === 'manuscript',
                                        'bg-gray-400': !['book', 'video', 'audio', 'manuscript'].includes(item.type)
                                    }">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    </div>
                                    <div class="flex-grow bg-gray-50 dark:bg-gray-800/40 p-4 rounded-2xl hover:shadow-md transition-all">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-black uppercase px-2 py-0.5 rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 shadow-sm">
                                                    {{ translateActivity(item.activity_type) }}
                                                </span>
                                                <Link v-if="item.entity_slug" :href="route(item.type + 's.show', item.entity_slug)" class="font-bold text-gray-800 dark:text-white hover:text-indigo-500 transition-colors">
                                                    {{ item.entity_title }}
                                                </Link>
                                                <span v-else class="text-gray-400 font-bold">{{ item.entity_title }}</span>
                                            </div>
                                            <span class="text-[10px] text-gray-400 font-bold">{{ formatDate(item.created_at) }}</span>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">بواسطة <span class="text-indigo-600 dark:text-indigo-400 font-black">{{ item.user_name }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Side Column: Stats & Actions -->
                    <div class="space-y-8">
                        
                        <!-- Mini Ecosystem Stats Card -->
                        <div class="bg-gradient-to-br from-gray-900 to-black rounded-3xl p-8 shadow-2xl overflow-hidden relative group">
                            <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
                            <h3 class="relative z-10 text-lg font-black text-white mb-6 border-b border-white/10 pb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                نظرة على الوسائط
                            </h3>
                            <div class="relative z-10 space-y-4">
                                <!-- Progress Bars for Mix -->
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs font-bold text-white/70">
                                        <span>الكتب</span>
                                        <span>{{ stats.books }}</span>
                                    </div>
                                    <div class="w-full bg-white/10 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-blue-500 h-full rounded-full transition-all duration-1000" :style="{ width: (stats.books / totalEntities * 100) + '%' }"></div>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs font-bold text-white/70">
                                        <span>المرئيات</span>
                                        <span>{{ stats.videos }}</span>
                                    </div>
                                    <div class="w-full bg-white/10 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-purple-500 h-full rounded-full transition-all duration-1000" :style="{ width: (stats.videos / totalEntities * 100) + '%' }"></div>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs font-bold text-white/70">
                                        <span>المخطوطات</span>
                                        <span>{{ stats.manuscripts }}</span>
                                    </div>
                                    <div class="w-full bg-white/10 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-amber-500 h-full rounded-full transition-all duration-1000" :style="{ width: (stats.manuscripts / totalEntities * 100) + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Versions Mini-Card -->
                            <div class="relative z-10 mt-8 pt-6 border-t border-white/10 flex items-center justify-between">
                                <span class="text-xs font-black text-white/50 uppercase">إجمالي النسخ الرقمية</span>
                                <span class="text-2xl font-black text-indigo-400 font-mono tracking-tighter">{{ stats.versions }}</span>
                            </div>
                        </div>

                        <!-- Quick Actions Table -->
                        <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-xl border border-gray-100 dark:border-gray-800">
                             <h3 class="text-lg font-black text-gray-900 dark:text-white mb-6 pr-2">اختصارات سريعة</h3>
                             <div class="grid grid-cols-1 gap-3">
                                 <Link :href="route('books.create')" class="flex items-center gap-4 p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/10 hover:bg-blue-600 group transition-all">
                                    <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-blue-600 shadow-sm group-hover:bg-transparent group-hover:text-white transition-all">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                    <span class="font-bold text-gray-700 dark:text-gray-200 group-hover:text-white">إضافة عمل جديد</span>
                                 </Link>
                                 
                                 <Link :href="route('categories.create')" class="flex items-center gap-4 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/10 hover:bg-emerald-600 group transition-all">
                                    <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-emerald-600 shadow-sm group-hover:bg-transparent group-hover:text-white transition-all">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                    </div>
                                    <span class="font-bold text-gray-700 dark:text-gray-200 group-hover:text-white">تصنيفات جديدة</span>
                                 </Link>

                                 <Link :href="route('deletions.index')" class="flex items-center gap-4 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/10 hover:bg-rose-600 group transition-all">
                                    <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-rose-600 shadow-sm group-hover:bg-transparent group-hover:text-white transition-all">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </div>
                                    <span class="font-bold text-gray-700 dark:text-gray-200 group-hover:text-white">المهملات</span>
                                 </Link>
                             </div>
                        </div>

                        <!-- Secondary Info Row -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-3xl text-center">
                                <span class="block text-[10px] font-black uppercase text-gray-400 mb-1">التعليقات</span>
                                <span class="text-xl font-black dark:text-white">{{ stats.comments }}</span>
                            </div>
                             <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-3xl text-center">
                                <span class="block text-[10px] font-black uppercase text-gray-400 mb-1">الوسوم</span>
                                <span class="text-xl font-black dark:text-white">{{ stats.tags }}</span>
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
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    recent: Array,
    recentBooks: Array,
});

const totalEntities = computed(() => {
    return (props.stats.books + props.stats.videos + props.stats.audios + props.stats.manuscripts) || 1;
});

const currentDate = computed(() => {
    return new Date().toLocaleDateString('ar-EG', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
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
        'updated': 'تعديل',
        'deleted': 'حذف',
        'viewed': 'مشاهدة',
        'restored': 'استعادة',
        'attached': 'ربط',
        'detached': 'فك ربط',
        'synced': 'مزامنة'
    };
    return map[type] || type;
};
</script>

<style scoped>
.stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}
</style>
