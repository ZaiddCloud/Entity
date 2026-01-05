<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    author: Object,
});

const activeTab = ref('overview');

const tabs = [
    { id: 'overview', name: 'نبذة عامة' },
    { id: 'production', name: 'الإنتاج العلمي' },
];
</script>

<template>
    <Head :title="author.name" />

    <AuthenticatedLayout :title="author.name">
        <!-- Hide Default Header -->
        <template #header>
            <div class="hidden"></div>
        </template>

        <div class="-mt-8 -mx-8 mb-8">
            <!-- Hero Section -->
            <div class="relative w-full overflow-hidden bg-emerald-950 min-h-[400px] flex items-end pb-12">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10"
                     style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23a3e635\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
                </div>
                
                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-emerald-950 via-emerald-950/80 to-transparent"></div>

                <div class="relative z-10 w-full max-w-7xl mx-auto px-8">
                    <div class="flex flex-col md:flex-row items-center md:items-end gap-8">
                        <!-- Author Avatar/Icon -->
                        <div class="w-32 h-32 md:w-40 md:h-40 rounded-[2.5rem] bg-gradient-to-br from-emerald-800 to-emerald-900 border-4 border-emerald-500/30 shadow-2xl flex items-center justify-center text-4xl font-black text-lime-400">
                            {{ author.name.substring(0, 1) }}
                        </div>

                        <!-- Author Info -->
                        <div class="flex-1 text-center md:text-right pb-4">
                            <h1 class="text-3xl md:text-5xl font-black text-white mb-4 tracking-tight leading-tight">
                                {{ author.name }}
                            </h1>
                            
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-6">
                                <span class="px-4 py-1.5 rounded-full bg-emerald-900/50 border border-emerald-500/30 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                                    {{ author.birth_year ? 'ولد عام ' + author.birth_year + ' هـ' : 'تاريخ الميلاد غير مسجل' }}
                                </span>
                                <span v-if="author.death_year" class="px-4 py-1.5 rounded-full bg-rose-950/30 border border-rose-500/30 text-rose-200 text-xs font-bold backdrop-blur-sm">
                                    توفى عام {{ author.death_year }} هـ
                                </span>
                                <span v-else class="px-4 py-1.5 rounded-full bg-lime-950/30 border border-lime-500/30 text-lime-400 text-xs font-bold backdrop-blur-sm animate-pulse">
                                    على قيد الحياة
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                                <Link :href="route('authors.edit', author.id)">
                                    <PrimaryButton class="!bg-lime-400 !text-black hover:!bg-lime-300 !border-0 !text-sm !px-8 !py-3 !rounded-xl !shadow-[0_0_20px_rgba(163,230,53,0.3)]">
                                        تعديل البيانات
                                    </PrimaryButton>
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
                    @click="activeTab = tab.id"
                    :class="[
                        'px-8 py-4 text-sm font-black transition-all relative whitespace-nowrap',
                        activeTab === tab.id
                            ? 'text-emerald-600 dark:text-emerald-400'
                            : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'
                    ]"
                >
                    {{ tab.name }}
                    <div
                        v-if="activeTab === tab.id"
                        class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500 shadow-[0_-2px_10px_rgba(16,185,129,0.5)]"
                    ></div>
                </button>
            </div>

            <!-- Tab Content: Overview -->
            <div v-if="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up">
                <div class="lg:col-span-2 space-y-8">
                    <!-- Bio -->
                    <Card class="!p-8">
                        <h3 class="font-black text-xl mb-6 flex items-center gap-2 dark:text-white">
                            <span class="w-1 h-6 bg-emerald-500 rounded-full"></span>
                            السيرة الذاتية
                        </h3>
                        <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-loose">
                            {{ author.bio || 'لا توجد سيرة ذاتية مسجلة لهذا المؤلف.' }}
                        </div>
                    </Card>
                </div>
                
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <Card class="!p-6 !bg-gradient-to-br !from-gray-900 !to-black !border-white/5">
                        <h3 class="font-black text-sm mb-6 text-gray-400 uppercase tracking-widest">إحصائيات المكتبة</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white/5 rounded-2xl p-4 text-center border border-white/5">
                                <div class="text-2xl font-black text-white mb-1">{{ author.books_count || 0 }}</div>
                                <div class="text-[10px] text-gray-400 font-bold uppercase">كتاب</div>
                            </div>
                            <div class="bg-white/5 rounded-2xl p-4 text-center border border-white/5">
                                <div class="text-2xl font-black text-white mb-1">{{ author.audios_count || 0 }}</div>
                                <div class="text-[10px] text-gray-400 font-bold uppercase">مادة صوتية</div>
                            </div>
                            <div class="bg-white/5 rounded-2xl p-4 text-center border border-white/5">
                                <div class="text-2xl font-black text-white mb-1">{{ author.videos_count || 0 }}</div>
                                <div class="text-[10px] text-gray-400 font-bold uppercase">مادة مرئية</div>
                            </div>
                            <div class="bg-white/5 rounded-2xl p-4 text-center border border-white/5">
                                <div class="text-2xl font-black text-white mb-1">{{ author.manuscripts_count || 0 }}</div>
                                <div class="text-[10px] text-gray-400 font-bold uppercase">مخطوط</div>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>

            <!-- Tab Content: Production -->
            <div v-if="activeTab === 'production'" class="space-y-8 animate-fade-in-up">
                <div class="flex flex-col items-center justify-center py-20 bg-gray-50 dark:bg-white/5 rounded-[2.5rem] border-2 border-dashed border-gray-200 dark:border-white/10">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-white/10 rounded-full flex items-center justify-center text-gray-400 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <p class="text-gray-500 font-bold">قائمة الإنتاج العلمي ستكون متاحة قريباً</p>
                    <p class="text-xs text-gray-400 mt-2">يتم العمل على تجهيز عرض تفصيلي لكافة أعمال المؤلف</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
