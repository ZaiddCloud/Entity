<template>
  <AuthenticatedLayout title="اللغات والترجمة">
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
              d="M3 5h12M9 3v2m1.048 9.531a11.115 11.115 0 01-1.048-3.531m6.241 3a9.904 9.904 0 01-6.241 3m0 0a9.904 9.904 0 01-6.241-3m6.241 3v2m0-6V7m0 0H7m3 0h3"
            /></svg>
          </div>
          <div>
            <h2 class="font-black text-2xl dark:text-white leading-tight text-emerald-600">
              اللغات والترجمة
            </h2>
            <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">
              إدارة اللغات المتاحة للمحتوى الرقمي والمادي
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
            :href="route('languages.create')"
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
            إضافة لغة جديدة
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
              id="languages-search-input"
              v-model="search"
              placeholder="بحث عن لغة أو رمز..."
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
            <TableHeaderCell>اللغة</TableHeaderCell>
            <TableHeaderCell align="center">
              الرمز الدولي
            </TableHeaderCell>
            <TableHeaderCell>إحصائيات المحتوى</TableHeaderCell>
            <TableHeaderCell align="left">
              الإجراءات
            </TableHeaderCell>
          </TableHead>
        </template>
        <template #body>
          <TableBody>
            <TableRow
              v-for="lang in languages.data"
              :key="lang.id"
            >
              <TableCell>
                <input
                  v-model="selectedIds"
                  type="checkbox"
                  :value="lang.id"
                  class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 dark:bg-white/5 dark:border-white/10"
                >
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-xs font-black text-emerald-600 border border-emerald-100 dark:border-emerald-500/20 lowercase">
                    {{ lang.code || lang.name.substring(0, 2) }}
                  </div>
                  <div>
                    <div class="font-black text-gray-900 dark:text-white">
                      {{ lang.name }}
                    </div>
                    <div class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter">
                      ID: {{ lang.id.substring(0, 8) }}...
                    </div>
                  </div>
                </div>
              </TableCell>
              <TableCell align="center">
                <span class="px-3 py-1 bg-gray-100 dark:bg-white/5 rounded-lg text-xs font-black font-mono text-gray-500 uppercase">
                  {{ lang.code || '-' }}
                </span>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-3">
                  <div class="flex flex-col">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter whitespace-nowrap">كتب: {{ lang.books_count }}</span>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mt-1 whitespace-nowrap">صوتيات: {{ lang.audios_count }}</span>
                  </div>
                  <div class="h-8 w-px bg-gray-100 dark:bg-white/5" />
                  <div class="flex flex-col">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter whitespace-nowrap">مرئيات: {{ lang.videos_count }}</span>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mt-1 whitespace-nowrap">مخطوطات: {{ lang.manuscripts_count }}</span>
                  </div>
                </div>
              </TableCell>
              <TableCell class="text-left">
                <div class="flex items-center justify-end gap-2">
                  <IconButton
                    :href="route('languages.show', lang.id)"
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
                    :href="route('languages.edit', lang.id)"
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
          <Pagination :links="languages.links" />
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
    languages: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('languages.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const selectedIds = ref([]);

const allSelected = computed({
    get: () => props.languages.data.length > 0 && selectedIds.value.length === props.languages.data.length,
    set: (value) => {
        selectedIds.value = value ? props.languages.data.map(l => l.id) : [];
    }
});

const bulkDelete = () => {
    if (confirm('هل أنت متأكد من حذف اللغات المحددة؟')) {
        router.post(route('languages.bulk-destroy'), {
            ids: selectedIds.value
        }, {
            onSuccess: () => selectedIds.value = [],
        });
    }
};
</script>
