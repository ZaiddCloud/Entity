<template>
    <AuthenticatedLayout :title="'رف: ' + shelf.location_code">
        <!-- Premium Hero Section -->
        <template #header>
            <div class="relative overflow-hidden bg-emerald-700 rounded-[2.5rem] p-12 text-white shadow-2xl shadow-emerald-900/40">
                <!-- Abstract Background Elements -->
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-lime-400/10 rounded-full blur-3xl"></div>

                <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 rounded-3xl bg-white/10 backdrop-blur-xl flex items-center justify-center text-4xl border border-white/20 shadow-inner">
                            <svg class="w-10 h-10 text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2zm0 0h16M9 17v-4m3 4v-4m3 4v-4"></path></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-3 flex-wrap">
                                <h1 class="text-4xl font-black tracking-tight">{{ shelf.location_code }}</h1>
                                <Badge color="emerald" class="bg-emerald-500/30 text-emerald-100 border-emerald-400/30">رف / خزانة</Badge>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-6 text-emerald-100/80 text-sm font-bold uppercase tracking-widest">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    <span>السعة: {{ shelf.capacity }} نسخة</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                                    <span>المقتنيات: {{ shelf.versions?.length || 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link :href="route('shelves.edit', shelf.id)">
                            <PrimaryButton class="bg-white/10 hover:bg-white/20 text-white border-white/20 backdrop-blur-md">
                                تعديل بيانات الرف
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-12 py-8">
            <!-- Tabs Interface -->
            <div class="flex items-center gap-8 border-b border-gray-100 dark:border-white/5 pb-px overflow-x-auto no-scrollbar">
                <button
                    @click="activeTab = 'contents'"
                    :class="[
                        'pb-4 text-xs font-black uppercase tracking-[0.2em] transition-all relative',
                        activeTab === 'contents' ? 'text-emerald-600' : 'text-gray-400 hover:text-gray-600 dark:hover:text-white'
                    ]"
                >
                    المحتويات المودعة
                    <div v-if="activeTab === 'contents'" class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-500 rounded-full"></div>
                </button>
                <button
                    @click="activeTab = 'stats'"
                    :class="[
                        'pb-4 text-xs font-black uppercase tracking-[0.2em] transition-all relative',
                        activeTab === 'stats' ? 'text-emerald-600' : 'text-gray-400 hover:text-gray-600 dark:hover:text-white'
                    ]"
                >
                    إحصائيات الإشغال
                    <div v-if="activeTab === 'stats'" class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-500 rounded-full"></div>
                </button>
            </div>

            <!-- Tab Contents -->
            <div v-if="activeTab === 'contents'" class="space-y-6">
                <div v-if="shelf.versions?.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card v-for="version in shelf.versions" :key="version.id" class="group">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 shrink-0 border border-emerald-100 dark:border-emerald-500/20">
                                <svg v-if="version.versionable_type.includes('Book')" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                <svg v-else-if="version.versionable_type.includes('Audio')" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                                <svg v-else-if="version.versionable_type.includes('Video')" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <Badge color="emerald">{{ version.version_label || 'نسخة أساسية' }}</Badge>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ version.versionable_type.split('\\').pop() }}</span>
                                </div>
                                <h3 class="font-black text-gray-900 dark:text-white truncate group-hover:text-emerald-600 transition-colors">
                                    {{ version.versionable?.title || version.versionable?.name || 'محتوى غير معروف' }}
                                </h3>
                                <p class="text-xs text-gray-400 mt-2 font-medium line-clamp-1">
                                    {{ version.notes || 'لا توجد ملاحظات إضافية لهذه النسخة' }}
                                </p>
                            </div>
                        </div>
                    </Card>
                </div>
                <div v-else class="py-20 text-center bg-gray-50 dark:bg-white/2 rounded-[2.5rem] border border-dashed border-gray-200 dark:border-white/10">
                    <div class="w-16 h-16 bg-white dark:bg-white/5 rounded-2xl flex items-center justify-center text-gray-300 mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 00-2 2H6a2 2 0 00-2 2v-5m16 0h-3.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                    </div>
                    <p class="text-gray-400 font-bold text-xs uppercase tracking-widest">هذا الرف خالٍ حالياً</p>
                </div>
            </div>

            <div v-if="activeTab === 'stats'" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <Card class="bg-emerald-50/50 dark:bg-emerald-500/5 border-emerald-100 dark:border-emerald-500/10">
                    <div class="flex flex-col items-center text-center p-4">
                        <div class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-[0.2em] mb-4">نسبة الإشغال</div>
                        <div class="text-5xl font-black text-emerald-900 dark:text-white mb-2">
                            {{ Math.round((shelf.versions?.length / (shelf.capacity || 1)) * 100) }}%
                        </div>
                        <div class="w-full bg-emerald-200 dark:bg-emerald-900/40 h-2 rounded-full overflow-hidden mt-4">
                            <div
                                class="bg-emerald-500 h-full rounded-full transition-all duration-1000"
                                :style="{ width: Math.min((shelf.versions?.length / (shelf.capacity || 1)) * 100, 100) + '%' }"
                            ></div>
                        </div>
                    </div>
                </Card>

                <Card>
                    <div class="flex flex-col items-center text-center p-4">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">المساحة المتاحة</div>
                        <div class="text-5xl font-black text-gray-900 dark:text-white mb-2">
                             {{ Math.max((shelf.capacity || 0) - (shelf.versions?.length || 0), 0) }}
                        </div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-2">نسخة إضافية</p>
                    </div>
                </Card>

                <Card>
                    <div class="flex flex-col items-center text-center p-4">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">تاريخ التأسيس</div>
                        <div class="text-2xl font-black text-gray-900 dark:text-white mb-2">
                             {{ new Date(shelf.created_at).toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-2">تاريخ تسجيل الرف</p>
                    </div>
                </Card>
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
    shelf: Object
});

const activeTab = ref('contents');
</script>
