<template>
    <AuthenticatedLayout title="الكتب">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-black text-2xl dark:text-white leading-tight">المكتبة الرقمية</h2>
                        <p class="text-xs text-gray-400 font-bold mt-1">تصفح وإدارة كافة المخطوطات والكتب المسجلة</p>
                    </div>
                </div>
                <Link
                    :href="route('books.create')"
                    class="w-full sm:w-auto px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black text-xs transition-all shadow-xl shadow-indigo-500/20 active:scale-95 text-center"
                >
                    إضافة عمل جديد
                </Link>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Search & Filters -->
            <div class="bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2.5rem] p-8 shadow-sm">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-[300px] relative group">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="بحث عن عنوان، مؤلف، أو موضوع..."
                            class="w-full pr-12 pl-4 py-3 bg-gray-50 dark:bg-white/5 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-black focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-sm font-medium transition-all"
                        />
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-48 relative">
                            <select
                                v-model="category"
                                class="w-full pr-10 pl-4 py-3 bg-gray-50 dark:bg-white/5 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-black focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-xs font-bold transition-all appearance-none cursor-pointer"
                            >
                                <option :value="undefined">كل التصنيفات</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>

                        <div class="w-48 relative text-right">
                            <select
                                v-model="tag"
                                class="w-full pr-10 pl-4 py-3 bg-gray-50 dark:bg-white/5 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-black focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-xs font-bold transition-all appearance-none cursor-pointer"
                            >
                                <option :value="undefined">كل الوسوم</option>
                                <option v-for="t in tags" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>

                        <button
                            @click="search = ''; category = undefined; tag = undefined"
                            class="p-3 text-gray-400 hover:text-rose-500 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-2xl transition-all"
                            title="إعادة تعيين"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table View -->
            <div class="bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2.5rem] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-white/5">
                        <thead class="bg-gray-50/50 dark:bg-white/2">
                            <tr>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">الرقم التسلسلي</th>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">عنوان العمل</th>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">المؤلف</th>
                                <th scope="col" class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">الأوسمة</th>
                                <th scope="col" class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                            <tr v-for="book in books.data" :key="book.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/2 transition-colors">
                                <td class="px-8 py-6 whitespace-nowrap text-sm font-black font-mono text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 transition-colors">
                                    {{ book.formatted_serial_number }}
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="text-sm font-black text-gray-900 dark:text-white">{{ book.title }}</div>
                                    <div class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter">تاريخ الإضافة: {{ formatDate(book.created_at) }}</div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <div v-if="book.authors && book.authors.length" class="flex flex-wrap gap-2">
                                        <span v-for="author in book.authors" :key="author.id" class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-bold text-gray-600 dark:text-gray-300">
                                            {{ author.name }}
                                        </span>
                                    </div>
                                    <span v-else class="text-gray-400 italic text-xs">غير محدد</span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="tag in book.tags" :key="tag.id" class="px-2 py-0.5 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-md text-[10px] font-black">
                                            #{{ tag.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-left text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="route('books.show', book.slug)" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-xl transition-all" title="عرض التفاصيل">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </Link>
                                        <Link :href="route('books.edit', book.slug)" class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-xl transition-all" title="تعديل">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-white/2 border-t border-gray-100 dark:border-white/5">
                    <Pagination :links="books.links" />
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
    books: Object,
    filters: Object,
    categories: Array,
    tags: Array,
});

const search = ref(props.filters.search);
const category = ref(props.filters.category);
const tag = ref(props.filters.tag);

watch([search, category, tag], debounce(() => {
    router.get(route('books.index'), {
        search: search.value,
        category: category.value,
        tag: tag.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>
