<template>
  <AuthenticatedLayout title="الرفوف والخزائن">
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
              d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2zm0 0h16M9 17v-4m3 4v-4m3 4v-4"
            /></svg>
          </div>
          <div>
            <h2 class="font-black text-2xl dark:text-white leading-tight text-emerald-600">
              الرفوف والخزائن
            </h2>
            <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">
              تنظيم وتتبع المواقع المادية والرقمية للمحتوى
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
            :href="route('shelves.create')"
            class="px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black text-xs transition-all shadow-lg shadow-emerald-500/20 active:scale-95 text-center flex items-center justify-center gap-2"
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
            إضافة رف جديد
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
              id="shelves-search-input"
              v-model="search"
              placeholder="بحث عن رمز الرف..."
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
              <input
                v-model="allSelected"
                type="checkbox"
                class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 dark:bg-white/5 dark:border-white/10"
              >
            </TableHeaderCell>
            <TableHeaderCell>رمز الرف / الموقع</TableHeaderCell>
            <TableHeaderCell class="text-center">
              السعة الاستيعابية
            </TableHeaderCell>
            <TableHeaderCell>إجمالي المقتنيات</TableHeaderCell>
            <TableHeaderCell align="left">
              الإجراءات
            </TableHeaderCell>
          </TableHead>
        </template>
        <template #body>
          <TableBody>
            <TableRow
              v-for="shelf in shelves.data"
              :key="shelf.id"
            >
              <TableCell>
                <input
                  v-model="selectedIds"
                  type="checkbox"
                  :value="shelf.id"
                  class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 dark:bg-white/5 dark:border-white/10"
                >
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-xs font-black text-emerald-600 border border-emerald-100 dark:border-emerald-500/20">
                    {{ shelf.location_code?.substring(0, 2) || 'S' }}
                  </div>
                  <div>
                    <div class="font-black text-gray-900 dark:text-white">
                      {{ shelf.location_code }}
                    </div>
                    <div class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter line-clamp-1 max-w-sm">
                      ID: {{ shelf.id.substring(0, 8) }}...
                    </div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-gray-100 dark:bg-white/5 rounded-lg">
                  <span class="text-xs font-black font-mono text-gray-600 dark:text-gray-300">{{ shelf.capacity || 'غير محدد' }}</span>
                </div>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-xs font-black text-emerald-600 border border-emerald-100 dark:border-emerald-500/20">
                    {{ shelf.versions_count }}
                  </div>
                  <span class="text-xs font-bold text-gray-400 uppercase tracking-tighter">نسخة</span>
                </div>
              </TableCell>
              <TableCell class="text-left">
                <div class="flex items-center justify-end gap-2">
                  <IconButton
                    :href="route('shelves.show', shelf.id)"
                    color="emerald"
                    title="عرض"
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
                    :href="route('shelves.edit', shelf.id)"
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
        </template>
                
        <template #pagination>
          <Pagination :links="shelves.links" />
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
    shelves: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('shelves.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const selectedIds = ref([]);

const allSelected = computed({
    get: () => props.shelves.data.length > 0 && selectedIds.value.length === props.shelves.data.length,
    set: (value) => {
        selectedIds.value = value ? props.shelves.data.map(s => s.id) : [];
    }
});

const bulkDelete = () => {
    if (confirm('هل أنت متأكد من حذف الرفوف المحددة؟')) {
        router.post(route('shelves.bulk-destroy'), {
            ids: selectedIds.value
        }, {
            onSuccess: () => selectedIds.value = [],
        });
    }
};
</script>
