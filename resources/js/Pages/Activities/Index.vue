<template>
    <AuthenticatedLayout title="سجل النشاطات">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-500/10 flex items-center justify-center text-slate-600 dark:text-slate-400 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-black text-2xl dark:text-white leading-tight">سجل الرقابة</h2>
                        <p class="text-xs text-gray-400 font-bold mt-1">تتبع كافة العمليات والتغييرات المنفذة في النظام</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Search Bubble -->
            <div class="bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2.5rem] p-8 shadow-sm">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-[300px] relative group">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="بحث في الوصف أو اسم المستخدم..."
                            class="w-full pr-12 pl-4 py-3 bg-gray-50 dark:bg-white/5 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-black focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-sm font-medium transition-all"
                        />
                    </div>
                    <div class="w-full sm:w-64">
                        <select
                            v-model="type"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-black focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-sm font-black appearance-none"
                            style="background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E\"); background-position: left 1rem center; background-repeat: no-repeat; background-size: 1.5em;"
                        >
                            <option :value="undefined">جميع أنواع العمليات</option>
                            <option value="created">إنشاء عنصر جديد</option>
                            <option value="updated">تعديل بيانات</option>
                            <option value="deleted">حذف عنصر</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table View -->
            <div class="bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2.5rem] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-white/5">
                        <thead class="bg-gray-50/50 dark:bg-white/2">
                            <tr>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">المستخدم المسؤول</th>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">نوع العملية</th>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">نوع المحتوى</th>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest text-left">التوقيت الزمنـي</th>
                                <th scope="col" class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">التفاصيل</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                            <tr v-for="activity in activities.data" :key="activity.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/2 transition-colors">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[10px] font-black text-slate-500 uppercase">
                                            {{ activity.user?.name.substring(0, 1) || 'S' }}
                                        </div>
                                        <div class="text-sm font-black text-gray-900 dark:text-white">{{ activity.user?.name || 'النظام التلقائي' }}</div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-center">
                                    <span 
                                        :class="[
                                            'px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter inline-flex items-center gap-1',
                                            activity.activity_type === 'created' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20' :
                                            activity.activity_type === 'updated' ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20' :
                                            'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-500/20'
                                        ]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ 
                                            activity.activity_type === 'created' ? 'إضافة' :
                                            activity.activity_type === 'updated' ? 'تحديث' : 'حذف'
                                        }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-center">
                                    <span class="text-[10px] font-black text-gray-500 bg-gray-100 dark:bg-white/5 px-2 py-1 rounded-md uppercase">
                                        {{ activity.entity_type.split('\\').pop() }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-left" dir="ltr">
                                    <span class="text-xs font-mono font-bold text-gray-400">
                                        {{ new Date(activity.created_at).toLocaleString('ar-EG', { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' }) }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-left text-sm font-medium">
                                    <div class="flex items-center justify-end">
                                        <Link :href="route('activities.show', activity.id)" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-xl transition-all" title="عرض التفاصيل">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-white/2 border-t border-gray-100 dark:border-white/5">
                    <Pagination :links="activities.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    activities: Object,
    filters: Object,
});

const search = ref(props.filters.search);
const type = ref(props.filters.type);

watch([search, type], debounce(() => {
    router.get(route('activities.index'), {
        search: search.value,
        type: type.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>
