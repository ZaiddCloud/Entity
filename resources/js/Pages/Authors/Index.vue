<template>
    <AuthenticatedLayout title="المؤلفون">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-500/10 flex items-center justify-center text-teal-600 dark:text-teal-400 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-black text-2xl dark:text-white leading-tight">سجل المؤلفين</h2>
                        <p class="text-xs text-gray-400 font-bold mt-1">إدارة بيانات المؤلفين والعلماء وكافة الكتاب</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <button
                        v-if="selectedIds.length > 0"
                        @click="bulkDelete"
                        class="px-6 py-3 bg-rose-500 hover:bg-rose-600 text-white rounded-2xl font-black text-xs transition-all shadow-xl shadow-rose-500/20 active:scale-95"
                    >
                        حذف المحدد ({{ selectedIds.length }})
                    </button>
                    <Link
                        :href="route('authors.create')"
                        class="flex-1 sm:flex-none px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black text-xs transition-all shadow-xl shadow-indigo-500/20 active:scale-95 text-center"
                    >
                        إضافة مؤلف جديد
                    </Link>
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
                            placeholder="بحث عن مؤلف..."
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
                                <th scope="col" class="px-8 py-5 text-right">
                                    <input type="checkbox" v-model="allSelected" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-black text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                </th>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">المؤلف</th>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">تاريخ الوفاة</th>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">الإنتاج العلمي</th>
                                <th scope="col" class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                            <tr v-for="author in authors.data" :key="author.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/2 transition-colors">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <input type="checkbox" :value="author.id" v-model="selectedIds" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-black text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-xs font-black text-indigo-600">
                                            {{ author.name.substring(0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-black text-gray-900 dark:text-white">{{ author.name }}</div>
                                            <div class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter">سنة الميلاد: {{ author.birth_year || '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span v-if="author.death_year" class="px-3 py-1 bg-rose-50 dark:bg-rose-500/10 rounded-lg text-[10px] font-black text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-500/20">
                                        توفى عام {{ author.death_year }} هـ
                                    </span>
                                    <span v-else class="text-emerald-500 text-[10px] font-black">على قيد الحياة</span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter">كتب: {{ author.books_count }}</span>
                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mt-1">صوتيات: {{ author.audios_count }}</span>
                                        </div>
                                        <div class="h-8 w-px bg-gray-100 dark:bg-white/5"></div>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter">مرئيات: {{ author.videos_count }}</span>
                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mt-1">مخطوطات: {{ author.manuscripts_count }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-left text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="route('authors.show', author.id)" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-xl transition-all" title="عرض">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </Link>
                                        <Link :href="route('authors.edit', author.id)" class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-xl transition-all" title="تعديل">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-white/2 border-t border-gray-100 dark:border-white/5">
                    <Pagination :links="authors.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    authors: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('authors.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const selectedIds = ref([]);

const allSelected = computed({
    get: () => props.authors.data.length > 0 && selectedIds.value.length === props.authors.data.length,
    set: (value) => {
        selectedIds.value = value ? props.authors.data.map(a => a.id) : [];
    }
});

const bulkDelete = () => {
    if (confirm('هل أنت متأكد من حذف المؤلفين المحددين؟')) {
        router.post(route('authors.bulk-destroy'), {
            ids: selectedIds.value
        }, {
            onSuccess: () => selectedIds.value = [],
        });
    }
};
</script>
