<template>
    <AuthenticatedLayout title="الملاحظات">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400 shadow-sm border border-amber-100 dark:border-amber-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-black text-2xl dark:text-white leading-tight text-emerald-600">دفتر الملاحظات</h2>
                        <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">تدوين وتنظيم الأفكار العابرة والفوائد العلمية المستخلصة</p>
                    </div>
                </div>
                <Link
                    :href="route('notes.create')"
                    class="w-full sm:w-auto px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black text-xs transition-all shadow-lg shadow-emerald-500/20 active:scale-95 text-center flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    إضافة ملاحظة جديدة
                </Link>
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
                            id="note-search-input"
                            placeholder="بحث في محتوى الملاحظات..."
                            class="w-full pr-12 pl-4 border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 dark:bg-black/20"
                        />
                    </div>
                </div>
            </Card>

            <!-- Table View -->
            <Table>
                <template #head>
                    <TableHead>
                        <TableHeaderCell>صاحب الملاحظة</TableHeaderCell>
                        <TableHeaderCell>نص الملاحظة</TableHeaderCell>
                        <TableHeaderCell align="center">المحتوى المرتبط</TableHeaderCell>
                        <TableHeaderCell align="left">التاريخ</TableHeaderCell>
                        <TableHeaderCell align="left">الإجراءات</TableHeaderCell>
                    </TableHead>
                </template>
                <template #body>
                    <TableBody>
                        <TableRow v-for="note in notes.data" :key="note.id">
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-[10px] font-black text-emerald-600 uppercase border border-emerald-100 dark:border-emerald-500/20">
                                        {{ note.user?.name.substring(0, 1) || 'N' }}
                                    </div>
                                    <div class="text-sm font-black text-gray-900 dark:text-white">{{ note.user?.name || 'مستخدم غير معروف' }}</div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="text-xs font-bold text-gray-600 dark:text-gray-300 line-clamp-2 max-w-sm leading-relaxed italic">
                                    "{{ note.content }}"
                                </div>
                            </TableCell>
                            <TableCell align="center">
                                <span class="text-[10px] font-black text-gray-500 bg-gray-100 dark:bg-white/5 px-2 py-1 rounded-md uppercase">
                                    {{ note.entity_type.split('\\').pop() }}
                                </span>
                            </TableCell>
                            <TableCell align="left">
                                <span class="text-xs font-mono font-bold text-gray-400" dir="ltr">
                                    {{ new Date(note.created_at).toLocaleDateString('ar-EG') }}
                                </span>
                            </TableCell>
                            <TableCell align="left">
                                <div class="flex items-center justify-end gap-2">
                                    <IconButton :href="route('notes.show', note.id)" color="emerald" title="عرض">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </IconButton>
                                    <IconButton :href="route('notes.edit', note.id)" color="blue" title="تعديل">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </IconButton>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </template>
                <template #pagination>
                    <Pagination :links="notes.links" />
                </template>
            </Table>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

// UI Components
import Card from '@/Components/Card.vue';
import TextInput from '@/Components/TextInput.vue';
import IconButton from '@/Components/IconButton.vue';
import Table from '@/Components/Table/Table.vue';
import TableHead from '@/Components/Table/TableHead.vue';
import TableBody from '@/Components/Table/TableBody.vue';
import TableRow from '@/Components/Table/TableRow.vue';
import TableHeaderCell from '@/Components/Table/TableHeaderCell.vue';
import TableCell from '@/Components/Table/TableCell.vue';

const props = defineProps({
    notes: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('notes.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>
