<template>
    <Head title="لوحة التحكم" />

    <AuthenticatedLayout title="نظرة عامة">
        <div class="space-y-10 pb-10">
            
            <!-- Hero Section -->
            <section class="relative overflow-hidden group">
                <div class="absolute inset-0 bg-linear-to-br from-indigo-600 via-purple-600 to-pink-500 rounded-[2.5rem] shadow-2xl shadow-purple-500/20 translate-y-0 group-hover:-translate-y-1 transition-transform duration-500"></div>
                <div class="absolute top-0 right-0 -m-20 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                
                <div class="relative z-10 p-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-[10px] font-black uppercase tracking-widest mb-4">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-ping"></span>
                            النظام يعمل بكفاءة
                        </div>
                        <h1 class="text-4xl font-black text-white mb-4 leading-tight">مرحباً بك في عالمك المعرفي الرقمي</h1>
                        <p class="text-indigo-100/80 text-lg font-medium leading-relaxed">
                            استكشف محتويات مكتبتك، تتبع نمو معرفتك، وقم بإدارة أصولك الثقافية من مكان واحد مصمم خصيصاً ليناسب تطلعاتك.
                        </p>
                    </div>
                    
                    <div class="flex gap-4 shrink-0">
                        <div class="bg-black/20 backdrop-blur-xl rounded-3xl p-6 border border-white/10 text-center min-w-[140px] hover:bg-black/30 transition-colors">
                            <span class="block text-white/50 text-[10px] font-black uppercase mb-2 tracking-widest">إجمالي المحتوى</span>
                            <span class="text-4xl font-black text-white">{{ totalEntities }}</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-6 border border-white/10 text-center min-w-[140px] hover:bg-white/20 transition-colors">
                            <span class="block text-white/70 text-[10px] font-black uppercase mb-2 tracking-widest">المؤلفون</span>
                            <span class="text-4xl font-black text-white">{{ stats.authors }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stats Showcase -->
            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Books -->
                <div class="group bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2rem] p-6 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/5 transition-all duration-500 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="flex items-center justify-between mb-6 relative z-10">
                        <div class="p-3 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-2xl group-hover:rotate-12 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">كتب</span>
                            <div class="text-2xl font-black dark:text-white">{{ stats.books }}</div>
                        </div>
                    </div>
                    <div class="w-full bg-gray-50 dark:bg-white/5 h-1.5 rounded-full overflow-hidden relative z-10">
                        <div class="bg-blue-500 h-full rounded-full w-[65%] shadow-[0_0_8px_rgba(59,130,246,0.5)]"></div>
                    </div>
                </div>

                <!-- Video -->
                <div class="group bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2rem] p-6 shadow-sm hover:shadow-2xl hover:shadow-purple-500/5 transition-all duration-500 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-purple-500/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="flex items-center justify-between mb-6 relative z-10">
                        <div class="p-3 bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-2xl group-hover:rotate-12 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">مرئيات</span>
                            <div class="text-2xl font-black dark:text-white">{{ stats.videos }}</div>
                        </div>
                    </div>
                    <div class="w-full bg-gray-50 dark:bg-white/5 h-1.5 rounded-full overflow-hidden relative z-10">
                        <div class="bg-purple-500 h-full rounded-full w-[45%] shadow-[0_0_8px_rgba(168,85,247,0.5)]"></div>
                    </div>
                </div>

                <!-- Audio -->
                <div class="group bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2rem] p-6 shadow-sm hover:shadow-2xl hover:shadow-emerald-500/5 transition-all duration-500 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="flex items-center justify-between mb-6 relative z-10">
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-2xl group-hover:rotate-12 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">صوتيات</span>
                            <div class="text-2xl font-black dark:text-white">{{ stats.audios }}</div>
                        </div>
                    </div>
                    <div class="w-full bg-gray-50 dark:bg-white/5 h-1.5 rounded-full overflow-hidden relative z-10">
                        <div class="bg-emerald-500 h-full rounded-full w-[30%] shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                    </div>
                </div>

                <!-- Manuscripts -->
                <div class="group bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2rem] p-6 shadow-sm hover:shadow-2xl hover:shadow-amber-500/5 transition-all duration-500 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="flex items-center justify-between mb-6 relative z-10">
                        <div class="p-3 bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-2xl group-hover:rotate-12 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">مخطوطات</span>
                            <div class="text-2xl font-black dark:text-white">{{ stats.manuscripts }}</div>
                        </div>
                    </div>
                    <div class="w-full bg-gray-50 dark:bg-white/5 h-1.5 rounded-full overflow-hidden relative z-10">
                        <div class="bg-amber-500 h-full rounded-full w-[20%] shadow-[0_0_8px_rgba(245,158,11,0.5)]"></div>
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                
                <!-- Main Activity Stream -->
                <div class="lg:col-span-2 space-y-10">
                    
                    <!-- Advanced Content Feed -->
                    <div class="bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2.5rem] p-8">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-8">
                            <div>
                                <h3 class="text-xl font-black transition-colors flex items-center gap-3">
                                    <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span>
                                    الإضافات الأخيرة
                                </h3>
                                <p class="text-xs text-gray-400 font-bold mt-1 pr-4">آخر ما تم إدراجه في المكتبة الرقمية</p>
                            </div>
                            <Link :href="route('books.index')" class="px-4 py-2 bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 rounded-xl text-xs font-black transition-all">تصفح الشامل</Link>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <Link v-for="book in recentBooks" :key="book.id" :href="route('books.show', book.slug)" class="group bg-gray-50/50 dark:bg-white/2 p-4 rounded-3xl border border-transparent hover:border-indigo-500/20 hover:bg-white dark:hover:bg-black transition-all duration-300">
                                <div class="flex gap-4">
                                    <div class="w-20 h-28 bg-gray-200 dark:bg-gray-800 rounded-2xl overflow-hidden shrink-0 shadow-lg group-hover:scale-105 transition-transform duration-500 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    </div>
                                    <div class="flex flex-col justify-between py-1">
                                        <div>
                                            <h4 class="font-black text-gray-900 dark:text-white line-clamp-1 group-hover:text-indigo-500 transition-colors">{{ book.title }}</h4>
                                            <div class="flex items-center gap-1 mt-1">
                                                <span v-for="author in book.authors" :key="author.id" class="text-[10px] font-bold text-gray-400 bg-gray-100 dark:bg-white/5 px-2 py-0.5 rounded-lg">
                                                    {{ author.name }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between text-[10px] font-black text-indigo-400 tracking-tighter uppercase">
                                            <span>#{{ book.serial_number }}</span>
                                            <span class="text-gray-400">{{ formatDate(book.created_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <!-- Enhanced Timeline -->
                    <div class="bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2.5rem] p-8 overflow-hidden relative">
                        <div class="flex justify-between items-center mb-10">
                            <div>
                                <h3 class="text-xl font-black flex items-center gap-3">
                                    <span class="w-1.5 h-6 bg-rose-500 rounded-full"></span>
                                    النشاطات الحية
                                </h3>
                            </div>
                            <Link :href="route('activities.index')" class="text-[10px] font-black uppercase text-gray-400 hover:text-rose-500 transition-colors tracking-widest">السجل الكامل</Link>
                        </div>

                        <div class="space-y-6 relative">
                            <!-- Timeline Line -->
                            <div class="absolute top-0 bottom-0 right-4 w-[1px] bg-linear-to-b from-rose-500/50 via-gray-200 dark:via-white/5 to-transparent"></div>
                            
                            <div v-for="item in recent" :key="item.id" class="relative pr-12 group">
                                <!-- Dot -->
                                <div class="absolute right-3 top-2 w-3 h-3 rounded-full bg-white dark:bg-black border-2 border-gray-200 dark:border-gray-700 group-hover:border-rose-500 transition-colors z-10 shadow-sm"></div>
                                
                                <div class="bg-gray-50/50 dark:bg-white/2 p-5 rounded-3xl border border-transparent hover:border-gray-200 dark:hover:border-white/10 hover:shadow-xl hover:shadow-gray-500/5 transition-all duration-300">
                                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-rose-500 shadow-sm font-black text-xs">
                                                {{ item.user_name.charAt(0) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium dark:text-white">
                                                    <span class="font-black text-rose-500">{{ item.user_name }}</span>
                                                    قام بـ{{ translateActivity(item.activity_type) }}
                                                    <Link v-if="item.entity_slug" :href="route(item.type + 's.show', item.entity_slug)" class="font-black hover:text-rose-500 transition-colors decoration-rose-500/20 underline decoration-2 underline-offset-4">{{ item.entity_title }}</Link>
                                                    <span v-else class="font-black">{{ item.entity_title }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        <time class="text-[10px] font-black text-gray-400 bg-gray-100 dark:bg-white/5 px-2 py-1 rounded-lg shrink-0">{{ formatDate(item.created_at) }}</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar Inside Dashboard -->
                <div class="space-y-10">
                    <!-- Quick Launch Pad -->
                    <div class="bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2.5rem] p-8">
                        <h3 class="text-lg font-black mb-6">أوامر سريعة</h3>
                        <div class="grid grid-cols-1 gap-3">
                            <Link v-for="action in quickActions" :key="action.name" :href="route(action.route)" :class="[
                                'flex items-center gap-4 p-4 rounded-2xl group transition-all',
                                action.bgClass
                            ]">
                                <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg transition-transform group-hover:-translate-y-1 shadow-black/5', action.iconBg]">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="action.icon" /></svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-black text-gray-900 dark:text-white group-hover:translate-x-[-4px] transition-transform">{{ action.name }}</span>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ action.desc }}</span>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <!-- Growth Analytics Card -->
                    <div class="bg-[#0f0f0f] rounded-[2.5rem] p-8 shadow-2xl relative overflow-hidden group border border-white/5">
                        <div class="absolute inset-0 bg-linear-to-tr from-indigo-500/10 via-transparent to-purple-500/10 opacity-50"></div>
                        <h3 class="relative z-10 text-lg font-black text-white mb-8 border-b border-white/5 pb-4">مؤشرات المحتوى</h3>
                        
                        <div class="relative z-10 space-y-6">
                            <div v-for="type in contentMix" :key="type.name" class="space-y-2">
                                <div class="flex justify-between text-[10px] font-black uppercase text-gray-500 tracking-widest">
                                    <span>{{ type.name }}</span>
                                    <span class="text-white">{{ type.count }}</span>
                                </div>
                                <div class="w-full bg-white/5 h-2 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-[1.5s] ease-out shadow-[0_0_10px_rgba(255,255,255,0.05)]" :class="type.color" :style="{ width: (type.count / totalEntities * 100) + '%' }"></div>
                                </div>
                            </div>
                        </div>

                        <div class="relative z-10 mt-10 pt-6 border-t border-white/5 flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">إجمالي النسخ</span>
                                <span class="text-3xl font-black text-indigo-400 font-mono tracking-tighter">{{ stats.versions }}</span>
                            </div>
                            <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center text-indigo-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
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

const contentMix = computed(() => [
    { name: 'الكتب', count: props.stats.books, color: 'bg-blue-500', icon: 'M12 6.253v13' },
    { name: 'المرئيات', count: props.stats.videos, color: 'bg-purple-500', icon: 'M15 10l' },
    { name: 'الصوتيات', count: props.stats.audios, color: 'bg-emerald-500', icon: 'M9 19V6' },
    { name: 'المخطوطات', count: props.stats.manuscripts, color: 'bg-amber-500', icon: 'M9 12h6' }
]);

const quickActions = [
    { name: 'كتاب جديد', desc: 'إضافة عمل مكتبي جديد', route: 'books.create', icon: 'M12 4v16m8-8H4', bgClass: 'bg-blue-50/50 dark:bg-blue-500/5 hover:bg-blue-100 dark:hover:bg-blue-500/10', iconBg: 'bg-blue-600' },
    { name: 'تصنيف جديد', desc: 'إدارة الفئات المركزية', route: 'categories.create', icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7', bgClass: 'bg-emerald-50/50 dark:bg-emerald-500/5 hover:bg-emerald-100 dark:hover:bg-emerald-500/10', iconBg: 'bg-emerald-600' },
    { name: 'إدارة المهملات', desc: 'استعادة الأصول المحذوفة', route: 'deletions.index', icon: 'M19 7l-.867 12.142A2 2 0 0116.138 21', bgClass: 'bg-rose-50/50 dark:bg-rose-500/5 hover:bg-rose-100 dark:hover:bg-rose-500/10', iconBg: 'bg-rose-600' }
];

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'short',
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
section {
    animation: slideUp 0.6s ease-out forwards;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
