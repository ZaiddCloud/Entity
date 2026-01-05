<template>
    <AuthenticatedLayout title="إدارة الأوسمة">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shadow-sm border border-emerald-100 dark:border-emerald-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-black text-2xl dark:text-white leading-tight text-emerald-600">إدارة الأوسمة</h2>
                        <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">تصنيفات مرنة ووسوم ذكية لتنظيم المكتبة</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <button
                        v-if="selectedIds.length > 0"
                        @click="bulkDelete"
                        class="px-6 py-3 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-black text-xs transition-all shadow-lg shadow-rose-500/20 active:scale-95"
                    >
                        حذف المحدد ({{ selectedIds.length }})
                    </button>
                    <Link
                        :href="route('tags.create')"
                        class="px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black text-xs transition-all shadow-lg shadow-emerald-500/20 active:scale-95 text-center flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        إضافة وسم جديد
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Search & Filters -->
            <Card>
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-[300px] relative group">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <TextInput
                            v-model="search"
                            id="tags-search-input"
                            placeholder="بحث في الأوسمة..."
                            class="w-full pr-12 pl-4 border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 dark:bg-black/20"
                        />
                    </div>
                </div>
            </Card>

            <!-- Table View -->
            <Table>
                <template #head>
                    <TableHead>
                        <TableHeaderCell class="w-10">
                             <input type="checkbox" v-model="allSelected" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 dark:bg-white/5 dark:border-white/10" />
                        </TableHeaderCell>
                        <TableHeaderCell>الوسم</TableHeaderCell>
                        <TableHeaderCell>نوع الوسم</TableHeaderCell>
                        <TableHeaderCell>إحصائيات الانتشار</TableHeaderCell>
                        <TableHeaderCell align="left">الإجراءات</TableHeaderCell>
                    </TableHead>
                </template>
                <template #body>
                    <TableBody>
                        <TableRow v-for="tag in tags.data" :key="tag.id">
                            <TableCell>
                                <input type="checkbox" v-model="selectedIds" :value="tag.id" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 dark:bg-white/5 dark:border-white/10" />
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                                    <div>
                                        <div class="font-black text-gray-900 dark:text-white">{{ tag.name }}</div>
                                        <div class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter">Slug: {{ tag.slug }}</div>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge color="amber" class="bg-amber-500/10 text-amber-600 border-amber-500/20">
                                    {{ tag.type || 'عام' }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter whitespace-nowrap">كتب: {{ tag.books_count }}</span>
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mt-1 whitespace-nowrap">صوتيات: {{ tag.audio_count }}</span>
                                    </div>
                                    <div class="h-8 w-px bg-gray-100 dark:bg-white/5"></div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter whitespace-nowrap">مرئيات: {{ tag.videos_count }}</span>
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mt-1 whitespace-nowrap">مخطوطات: {{ tag.manuscripts_count }}</span>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="text-left">
                                <div class="flex items-center justify-end gap-2">
                                    <IconButton :href="route('tags.show', tag.id)" color="emerald" title="عرض">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </IconButton>
                                    <IconButton :href="route('tags.edit', tag.id)" color="blue" title="تعديل">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </IconButton>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </template>
                
                <template #pagination>
                    <Pagination :links="tags.links" />
                </template>
            </Table>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';

// UI Components
import Card from '@/Components/Card.vue';
import TextInput from '@/Components/TextInput.vue';
import Badge from '@/Components/Badge.vue';
import IconButton from '@/Components/IconButton.vue';
import Table from '@/Components/Table/Table.vue';
import TableHead from '@/Components/Table/TableHead.vue';
import TableBody from '@/Components/Table/TableBody.vue';
import TableRow from '@/Components/Table/TableRow.vue';
import TableHeaderCell from '@/Components/Table/TableHeaderCell.vue';
import TableCell from '@/Components/Table/TableCell.vue';

const props = defineProps({
    tags: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('tags.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const selectedIds = ref([]);

const allSelected = computed({
    get: () => props.tags.data.length > 0 && selectedIds.value.length === props.tags.data.length,
    set: (value) => {
        selectedIds.value = value ? props.tags.data.map(t => t.id) : [];
    }
});

const bulkDelete = () => {
    if (confirm('هل أنت متأكد من حذف الوسوم المحددة؟')) {
        router.post(route('tags.bulk-destroy'), {
            ids: selectedIds.value
        }, {
            onSuccess: () => selectedIds.value = [],
        });
    }
};
</script>
