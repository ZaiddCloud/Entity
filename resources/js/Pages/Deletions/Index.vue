<template>
    <AuthenticatedLayout title="سجل المحذوفات">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-400 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-black text-2xl dark:text-white leading-tight">سجل المحذوفات</h2>
                        <p class="text-xs text-gray-400 font-bold mt-1">تتبع الواردات الممسوحة وأسباب الحذف لضمان أمان البيانات</p>
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
                            placeholder="بحث في سبب الحذف أو اسم المستخدم..."
                            class="w-full pr-12 pl-4 py-3 bg-gray-50 dark:bg-white/5 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-black focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-sm font-medium transition-all"
                        />
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
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">نوع المحتوى</th>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">سبب الحذف</th>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest text-left">التاريخ والوقت</th>
                                <th scope="col" class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                            <tr v-for="deletion in deletions.data" :key="deletion.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/2 transition-colors">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-800 flex items-center justify-center text-[10px] font-black text-rose-500 uppercase">
                                            {{ deletion.user?.name.substring(0, 1) || 'S' }}
                                        </div>
                                        <div class="text-sm font-black text-gray-900 dark:text-white">{{ deletion.user?.name || 'النظام التلقائي' }}</div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-center">
                                    <span class="text-[10px] font-black text-gray-500 bg-gray-100 dark:bg-white/5 px-2 py-1 rounded-md uppercase">
                                        {{ deletion.entity_type.split('\\').pop() }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-center max-w-xs truncate">
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 italic">
                                        {{ deletion.reason || 'لم يتم ذكر سبب' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-left" dir="ltr">
                                    <span class="text-xs font-mono font-bold text-gray-400">
                                        {{ new Date(deletion.created_at).toLocaleString('ar-EG', { hour12: true, hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' }) }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-left text-sm font-medium">
                                    <div class="flex items-center justify-end">
                                        <Link :href="route('deletions.show', deletion.id)" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-xl transition-all" title="عرض التفاصيل">
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
                    <Pagination :links="deletions.links" />
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
    deletions: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('deletions.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>
