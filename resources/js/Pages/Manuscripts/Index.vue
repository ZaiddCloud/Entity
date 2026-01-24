<template>
  <AuthenticatedLayout title="المخطوطات">
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400 shadow-sm border border-amber-100 dark:border-amber-500/20">
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            ><path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            /></svg>
          </div>
          <div>
            <h2 class="font-black text-2xl dark:text-white leading-tight">
              سجل المخطوطات
            </h2>
            <p class="text-xs text-gray-400 font-bold mt-1">
              إدارة وحفظ التراث المخطوط والنسخ النادرة
            </p>
          </div>
        </div>
        <Link
          :href="route('manuscripts.create')"
          class="w-full sm:w-auto px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black text-xs transition-all shadow-lg shadow-emerald-500/20 active:scale-95 text-center flex items-center justify-center gap-2"
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
          إضافة مخطوطة جديدة
        </Link>
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
              id="manuscript-search-input"
              v-model="search"
              placeholder="بحث في المخطوطات..."
              class="w-full pr-12 pl-4 border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 dark:bg-black/20"
            />
          </div>
                    
          <div class="flex items-center gap-3">
            <div class="w-48">
              <SelectInput
                v-model="category"
                :options="categories"
                placeholder="كل التصنيفات"
                class="w-full"
              />
            </div>

            <IconButton
              color="rose"
              title="إعادة تعيين"
              @click="search = ''; category = undefined; tag = undefined"
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
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              /></svg>
            </IconButton>
          </div>
        </div>
      </Card>

      <!-- Table View -->
      <Table>
        <TableHead>
          <TableHeaderCell>التسلسلي</TableHeaderCell>
          <TableHeaderCell>عنوان المخطوطة</TableHeaderCell>
          <TableHeaderCell>المؤلف / الناسخ</TableHeaderCell>
          <TableHeaderCell>المصدر / الدار</TableHeaderCell>
          <TableHeaderCell align="left">
            الإجراءات
          </TableHeaderCell>
        </TableHead>
        <TableBody>
          <TableRow
            v-for="manuscript in manuscripts.data"
            :key="manuscript.id"
          >
            <TableCell>
              <span class="font-black font-mono text-gray-300 dark:text-gray-600 group-hover:text-amber-500 transition-colors">
                {{ manuscript.formatted_serial_number }}
              </span>
            </TableCell>
            <TableCell>
              <div class="font-black text-gray-900 dark:text-white">
                {{ manuscript.title }}
              </div>
              <div class="flex gap-1 mt-1">
                <Badge
                  v-for="tag in manuscript.tags"
                  :key="tag.id"
                  color="gray"
                  size="sm"
                >
                  #{{ tag.name }}
                </Badge>
              </div>
            </TableCell>
            <TableCell>
              <span
                v-if="manuscript.authors && manuscript.authors.length"
                class="text-sm text-gray-600 dark:text-gray-400 font-bold"
              >
                {{ manuscript.authors.map(a => a.name).join('، ') }}
              </span>
              <span
                v-else
                class="text-gray-400 italic text-xs"
              >غير محدد</span>
            </TableCell>
            <TableCell>
              <span class="text-sm text-gray-500 dark:text-gray-400 font-bold">
                {{ manuscript.versions?.[0]?.publisher?.name || 'مخطوط أصلي' }}
              </span>
            </TableCell>
            <TableCell class="text-left">
              <div class="flex items-center justify-end gap-2">
                <IconButton
                  :href="route('manuscripts.show', manuscript.slug)"
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
                  :href="route('manuscripts.edit', manuscript.slug)"
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
          <Pagination :links="manuscripts.links" />
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
import SelectInput from '@/Components/SelectInput.vue';
import Badge from '@/Components/Badge.vue';
import IconButton from '@/Components/IconButton.vue';
import Table from '@/Components/Table/Table.vue';
import TableHead from '@/Components/Table/TableHead.vue';
import TableBody from '@/Components/Table/TableBody.vue';
import TableRow from '@/Components/Table/TableRow.vue';
import TableHeaderCell from '@/Components/Table/TableHeaderCell.vue';
import TableCell from '@/Components/Table/TableCell.vue';

const props = defineProps({
    manuscripts: Object,
    filters: Object,
    categories: Array,
    tags: Array,
});

const search = ref(props.filters.search);
const category = ref(props.filters.category);
const tag = ref(props.filters.tag);

watch([search, category, tag], debounce(() => {
    router.get(route('manuscripts.index'), {
        search: search.value,
        category: category.value,
        tag: tag.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>
