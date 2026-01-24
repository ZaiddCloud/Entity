<template>
  <AuthenticatedLayout title="سجل المحذوفات">
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-400 shadow-sm border border-rose-100 dark:border-rose-500/20">
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            ><path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            /></svg>
          </div>
          <div>
            <h2 class="font-black text-2xl dark:text-white leading-tight text-emerald-600">
              سجل المحذوفات
            </h2>
            <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">
              تتبع الواردات الممسوحة وأسباب الحذف لضمان أمان البيانات
            </p>
          </div>
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
              id="deletions-search-input"
              v-model="search"
              placeholder="بحث في سبب الحذف أو اسم المستخدم..."
              class="w-full pr-12 pl-4 border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 dark:bg-black/20"
            />
          </div>
        </div>
      </Card>

      <!-- Table View -->
      <Table>
        <template #head>
          <TableHead>
            <TableHeaderCell>المستخدم المسؤول</TableHeaderCell>
            <TableHeaderCell align="center">
              نوع المحتوى
            </TableHeaderCell>
            <TableHeaderCell align="center">
              سبب الحذف
            </TableHeaderCell>
            <TableHeaderCell align="left">
              التاريخ والوقت
            </TableHeaderCell>
            <TableHeaderCell align="left">
              الإجراءات
            </TableHeaderCell>
          </TableHead>
        </template>
        <template #body>
          <TableBody>
            <TableRow
              v-for="deletion in deletions.data"
              :key="deletion.id"
            >
              <TableCell>
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center text-[10px] font-black text-rose-600 uppercase border border-rose-100 dark:border-rose-500/20">
                    {{ deletion.user?.name.substring(0, 1) || 'S' }}
                  </div>
                  <div class="text-sm font-black text-gray-900 dark:text-white">
                    {{ deletion.user?.name || 'النظام التلقائي' }}
                  </div>
                </div>
              </TableCell>
              <TableCell align="center">
                <span class="text-[10px] font-black text-gray-500 bg-gray-100 dark:bg-white/5 px-2 py-1 rounded-md uppercase">
                  {{ deletion.entity_type.split('\\').pop() }}
                </span>
              </TableCell>
              <TableCell align="center">
                <div
                  class="text-xs font-bold text-gray-500 dark:text-gray-400 italic max-w-xs truncate"
                  :title="deletion.reason"
                >
                  {{ deletion.reason || 'لم يتم ذكر سبب' }}
                </div>
              </TableCell>
              <TableCell align="left">
                <span
                  class="text-xs font-mono font-bold text-gray-400"
                  dir="ltr"
                >
                  {{ new Date(deletion.created_at).toLocaleString('ar-EG', { hour12: true, hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' }) }}
                </span>
              </TableCell>
              <TableCell align="left">
                <div class="flex items-center justify-end">
                  <IconButton
                    :href="route('deletions.show', deletion.id)"
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
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </template>
        <template #pagination>
          <Pagination :links="deletions.links" />
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
