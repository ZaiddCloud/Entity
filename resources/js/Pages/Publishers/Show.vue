<template>
    <AuthenticatedLayout :title="publisher.name">
        <!-- Premium Hero Section -->
        <div class="relative -mt-8 -mx-8 mb-8 overflow-hidden bg-emerald-950 min-h-[400px] flex items-end">
            <!-- Decorative Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-1/2 -right-24 w-64 h-64 bg-lime-400/10 rounded-full blur-3xl animate-pulse delay-700"></div>
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20 mix-blend-overlay"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pb-12 relative z-10">
                <div class="flex flex-col md:flex-row gap-8 items-center md:items-end">
                    <!-- logo / cover -->
                    <div class="w-48 h-48 rounded-[2.5rem] bg-white dark:bg-black/40 p-1 shadow-2xl relative group">
                        <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500 to-lime-400 rounded-[2.5rem] blur opacity-20 group-hover:opacity-40 transition-opacity"></div>
                        <div class="w-full h-full rounded-[2.2rem] bg-gray-50 dark:bg-emerald-900/20 overflow-hidden flex items-center justify-center border border-white/10 relative z-10">
                            <img v-if="publisher.logo_path" :src="'/storage/' + publisher.logo_path" class="w-full h-full object-contain" :alt="publisher.name">
                            <div v-else class="text-6xl font-black text-emerald-500/20">{{ publisher.name.substring(0, 1) }}</div>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 text-center md:text-right pb-4">
                        <div class="flex items-center justify-center md:justify-start gap-2 mb-2">
                            <Badge variant="gray" class="!bg-emerald-500/10 !text-emerald-400 !border-emerald-500/20 !text-[10px] font-black uppercase tracking-wider backdrop-blur-sm">دار نشر</Badge>
                            <span class="px-3 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-black backdrop-blur-sm">
                                {{ publisher.country_code || 'INT' }}
                            </span>
                        </div>

                        <h1 class="text-3xl md:text-5xl font-black text-white mb-4 tracking-tight leading-tight">
                            {{ publisher.name }}
                        </h1>
                        
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                            <Link :href="route('publishers.edit', publisher.id)">
                                <PrimaryButton class="!bg-lime-400 !text-black hover:!bg-lime-300 !border-0 !text-sm !px-6 !py-3 !rounded-xl !shadow-[0_0_20px_rgba(163,230,53,0.3)] flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    تعديل البيانات
                                </PrimaryButton>
                            </Link>
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
                    <!-- Statistics -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Card class="!p-6 !bg-gradient-to-br !from-emerald-500/5 !to-transparent border-emerald-500/10">
                            <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">الكتب</span>
                            <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ publisher.books_count }}</span>
                        </Card>
                        <Card class="!p-6 !bg-gradient-to-br !from-blue-500/5 !to-transparent border-blue-500/10">
                            <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">الصوتيات</span>
                            <span class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ publisher.audios_count }}</span>
                        </Card>
                         <Card class="!p-6 !bg-gradient-to-br !from-purple-500/5 !to-transparent border-purple-500/10">
                            <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">المرئيات</span>
                            <span class="text-3xl font-black text-purple-600 dark:text-purple-400">{{ publisher.videos_count }}</span>
                        </Card>
                         <Card class="!p-6 !bg-gradient-to-br !from-amber-500/5 !to-transparent border-amber-500/10">
                            <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">المخطوطات</span>
                            <span class="text-3xl font-black text-amber-600 dark:text-amber-400">{{ publisher.manuscripts_count }}</span>
                        </Card>
                    </div>

                    <Card class="!p-8">
                        <h3 class="font-black text-xl mb-6 flex items-center gap-2 dark:text-white">
                            <span class="w-1 h-6 bg-emerald-500 rounded-full"></span>
                            عن دار النشر
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 leading-relaxed italic">
                            هذا النص يصف دار النشر "{{ publisher.name }}". حالياً نقوم بجلب البيانات من النظام بشكل ديناميكي.
                        </p>
                    </Card>
                </div>

                <div class="space-y-8">
                    <Card class="!p-8">
                        <h3 class="font-black text-lg mb-6 dark:text-white">معلومات إضافية</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-white/5 font-bold">
                                <span class="text-xs text-gray-400 uppercase">الاسم الكامل</span>
                                <span class="text-sm dark:text-white">{{ publisher.name }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-white/5 font-bold">
                                <span class="text-xs text-gray-400 uppercase">رمز الدولة</span>
                                <span class="text-sm dark:text-white">{{ publisher.country_code || '---' }}</span>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    publisher: Object
});

const activeTab = ref('overview');

const tabs = [
    { id: 'overview', name: 'نظرة عامة' },
    { id: 'publications', name: 'الإصدارات' },
    { id: 'history', name: 'السجل' },
];
</script>
