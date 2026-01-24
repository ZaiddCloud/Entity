<template>
  <AuthenticatedLayout title="المؤلفون">
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shadow-sm border border-emerald-100 dark:border-emerald-500/20">
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            ><path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
            /></svg>
          </div>
          <div>
            <h2 class="font-black text-2xl dark:text-white leading-tight">
              سجل المؤلفين
            </h2>
            <p class="text-xs text-gray-400 font-bold mt-1">
              إدارة بيانات المؤلفين والعلماء وكافة الكتاب
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <button
            v-if="selectedIds.length > 0"
            class="px-6 py-3 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-black text-xs transition-all shadow-lg shadow-rose-500/20 active:scale-95"
            @click="bulkDelete"
          >
            حذف المحدد ({{ selectedIds.length }})
          </button>
          <Link
            :href="route('authors.create')"
            class="flex-1 sm:flex-none px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black text-xs transition-all shadow-lg shadow-emerald-500/20 active:scale-95 text-center flex items-center justify-center gap-2"
          >
            <svg
              class="w-4 h-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            ><path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4v16m8-8H4"
            /></svg>
            إضافة مؤلف جديد
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
              <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              /></svg>
            </div>
            <TextInput
              id="author-search-input"
              v-model="search"
              placeholder="بحث عن مؤلف..."
              class="w-full pr-12 pl-4 border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 dark:bg-black/20"
            />
          </div>
        </div>
      </Card>

      <!-- Table View -->
      <Table>
        <TableHead>
          <TableHeaderCell class="!px-4 w-12 text-center">
            <input
              id="authors-select-all"
              v-model="allSelected"
              type="checkbox"
              class="rounded border-gray-300 dark:border-white/10 dark:bg-black text-emerald-600 focus:ring-emerald-500"
            >
          </TableHeaderCell>
          <TableHeaderCell>المؤلف</TableHeaderCell>
          <TableHeaderCell>الحالة / الوفاة</TableHeaderCell>
          <TableHeaderCell>الإنتاج العلمي</TableHeaderCell>
          <TableHeaderCell align="left">
            الإجراءات
          </TableHeaderCell>
        </TableHead>
        <TableBody>
          <TableRow
            v-for="author in authors.data"
            :key="author.id"
          >
            <TableCell class="!px-4 text-center">
              <input
                :id="'author-select-' + author.id"
                v-model="selectedIds"
                type="checkbox"
                :value="author.id"
                class="rounded border-gray-300 dark:border-white/10 dark:bg-black text-emerald-600 focus:ring-emerald-500"
              >
            </TableCell>
            <TableCell>
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-sm font-black text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                  {{ author.name.substring(0, 1) }}
                </div>
                <div>
                  <div class="font-black text-gray-900 dark:text-white">
                    {{ author.name }}
                  </div>
                  <div class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter">
                    سنة الميلاد: {{ author.birth_year || '-' }}
                  </div>
                </div>
              </div>
            </TableCell>
            <TableCell>
              <Badge
                v-if="author.death_year"
                variant="error"
                class="!bg-rose-50 !text-rose-600 !border-rose-100"
              >
                توفى عام {{ author.death_year }} هـ
              </Badge>
              <Badge
                v-else
                variant="success"
                class="!bg-emerald-50 !text-emerald-600 !border-emerald-100"
              >
                على قيد الحياة
              </Badge>
            </TableCell>
            <TableCell>
              <div class="flex items-center gap-4">
                <div class="flex flex-col gap-1">
                  <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400" />
                    كتب: {{ author.books_count }}
                  </span>
                  <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400" />
                    صوتيات: {{ author.audios_count }}
                  </span>
                </div>
                <div class="h-8 w-px bg-gray-100 dark:bg-white/5" />
                <div class="flex flex-col gap-1">
                  <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-400" />
                    مرئيات: {{ author.videos_count }}
                  </span>
                  <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400" />
                    مخطوطات: {{ author.manuscripts_count }}
                  </span>
                </div>
              </div>
            </TableCell>
            <TableCell class="text-left">
              <div class="flex items-center justify-end gap-2">
                <IconButton
                  :href="route('authors.show', author.id)"
                  color="emerald"
                  title="عرض التفاصيل"
                >
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  /><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                  /></svg>
                </IconButton>
                <IconButton
                  :href="route('authors.edit', author.id)"
                  color="blue"
                  title="تعديل"
                >
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  /></svg>
                </IconButton>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
                
        <template #pagination>
          <Pagination :links="authors.links" />
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
