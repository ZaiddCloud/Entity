<template>
  <AuthenticatedLayout title="الكتب">
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-sm">
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            ><path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
            /></svg>
          </div>
          <div>
            <h2 class="font-black text-2xl dark:text-white leading-tight">
              المكتبة الرقمية
            </h2>
            <p class="text-xs text-gray-400 font-bold mt-1">
              تصفح وإدارة كافة المخطوطات والكتب المسجلة
            </p>
          </div>
        </div>
        <Link :href="route('books.create')">
          <PrimaryButton>إضافة عمل جديد</PrimaryButton>
        </Link>
      </div>
    </template>

    <div class="space-y-8">
      <!-- Search & Filters -->
      <Card>
        <div class="flex flex-wrap gap-4 items-center">
          <div class="flex-1 min-w-[300px] relative group">
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500 transition-colors">
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
              id="book-search-input"
              v-model="search"
              placeholder="بحث عن عنوان، مؤلف، أو موضوع..."
              class="w-full pr-12 pl-4"
            />
          </div>
                    
          <div class="flex items-center gap-3">
            <div class="w-48">
              <SelectInput
                v-model="category"
                :options="categories"
                placeholder="كل التصنيفات"
              />
            </div>

            <div class="w-48">
              <SelectInput
                v-model="tag"
                :options="tags"
                placeholder="كل الوسوم"
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
          <TableHeaderCell>الرقم التسلسلي</TableHeaderCell>
          <TableHeaderCell>عنوان العمل</TableHeaderCell>
          <TableHeaderCell>المؤلف</TableHeaderCell>
          <TableHeaderCell>الأوسمة</TableHeaderCell>
          <TableHeaderCell align="left">
            الإجراءات
          </TableHeaderCell>
        </TableHead>
        <TableBody>
          <TableRow
            v-for="book in books.data"
            :key="book.id"
          >
            <TableCell>
              <span class="font-black font-mono text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 transition-colors">
                {{ book.formatted_serial_number }}
              </span>
            </TableCell>
            <TableCell>
              <div class="font-black text-gray-900 dark:text-white">
                {{ book.title }}
              </div>
              <div class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter">
                تاريخ الإضافة: {{ formatDate(book.created_at) }}
              </div>
            </TableCell>
            <TableCell>
              <div
                v-if="book.authors && book.authors.length"
                class="flex flex-wrap gap-2"
              >
                <Badge
                  v-for="author in book.authors"
                  :key="author.id"
                  color="gray"
                >
                  {{ author.name }}
                </Badge>
              </div>
              <span
                v-else
                class="text-gray-400 italic text-xs"
              >غير محدد</span>
            </TableCell>
            <TableCell>
              <div class="flex flex-wrap gap-1">
                <Badge
                  v-for="tag in book.tags"
                  :key="tag.id"
                  color="blue"
                >
                  #{{ tag.name }}
                </Badge>
              </div>
            </TableCell>
            <TableCell class="text-left">
              <div class="flex items-center justify-end gap-2">
                <IconButton
                  :href="route('reader.show', { type: 'book', slug: book.slug })"
                  class="!bg-lime-400 hover:!bg-lime-300 !text-emerald-950 shadow-sm"
                  title="فتح القارئ"
                >
                  <svg class="w-5 h-5 ml-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                  </svg>
                </IconButton>

                <IconButton
                  :href="route('books.show', book.slug)"
                  color="indigo"
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
                  :href="route('books.edit', book.slug)"
                  color="emerald"
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
          <Pagination :links="books.links" />
        </template>
      </Table>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import IconButton from '@/Components/IconButton.vue';

// Table Components
import Table from '@/Components/Table/Table.vue';
import TableHead from '@/Components/Table/TableHead.vue';
import TableBody from '@/Components/Table/TableBody.vue';
import TableRow from '@/Components/Table/TableRow.vue';
import TableHeaderCell from '@/Components/Table/TableHeaderCell.vue';
import TableCell from '@/Components/Table/TableCell.vue';

import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    books: Object,
    filters: Object,
    categories: Array,
    tags: Array,
    // Add authors prop if passed from controller, otherwise handle locally
});

const search = ref(props.filters?.search || '');
const category = ref(props.filters?.category || '');
const tag = ref(props.filters?.tag || '');

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


