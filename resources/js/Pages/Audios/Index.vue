<template>
  <AuthenticatedLayout title="الصوتيات">
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <h2 class="font-black text-2xl text-gray-800 dark:text-white leading-tight flex items-center gap-2">
          <span class="w-2 h-8 bg-emerald-500 rounded-full inline-block" />
          المكتبة الصوتية
        </h2>
        <Link :href="route('audios.create')">
          <PrimaryButton class="flex items-center gap-2">
            <svg
              class="w-5 h-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            ><path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4v16m8-8H4"
            /></svg>
            إضافة تسجيل جديد
          </PrimaryButton>
        </Link>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Search & Filters -->
      <Card class="!p-6">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <TextInput
              v-model="search"
              type="text"
              placeholder="بحث في التسجيلات..."
              class="w-full"
            >
              <template #prefix>
                <svg
                  class="w-5 h-5 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                /></svg>
              </template>
            </TextInput>
          </div>
          <div class="w-full md:w-64">
            <SelectInput
              v-model="category"
              :options="categories"
              placeholder="كل التصنيفات"
              class="w-full"
            />
          </div>
          <div class="flex items-center">
            <button
              class="p-2.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-xl transition-all"
              title="إعادة تعيين"
              @click="resetFilters"
            >
              <svg
                class="w-5 h-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              /></svg>
            </button>
          </div>
        </div>
      </Card>

      <!-- Table -->
      <Table :pagination="audios.links">
        <template #head>
          <TableHead>
            <TableRow>
              <TableHeaderCell>التسلسلي</TableHeaderCell>
              <TableHeaderCell>عنوان التسجيل</TableHeaderCell>
              <TableHeaderCell>المؤلف / القارئ</TableHeaderCell>
              <TableHeaderCell>الناشر / المنصة</TableHeaderCell>
              <TableHeaderCell>الإجراءات</TableHeaderCell>
            </TableRow>
          </TableHead>
        </template>
        <template #body>
          <TableBody>
            <TableRow
              v-for="audio in audios.data"
              :key="audio.id"
              class="group hover:bg-emerald-50/5 dark:hover:bg-emerald-900/10"
            >
              <TableCell class="font-mono font-bold text-gray-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                {{ audio.formatted_serial_number }}
              </TableCell>
              <TableCell>
                <div class="font-bold text-gray-900 dark:text-white mb-1">
                  {{ audio.title }}
                </div>
                <div class="flex flex-wrap gap-1">
                  <Badge
                    v-for="tag in audio.tags"
                    :key="tag.id"
                    color="gray"
                    size="sm"
                  >
                    #{{ tag.name }}
                  </Badge>
                </div>
              </TableCell>
              <TableCell class="text-gray-500 dark:text-gray-400 font-medium">
                {{ audio.authors?.map(a => a.name).join('، ') || '-' }}
              </TableCell>
              <TableCell>
                <Badge
                  v-if="audio.versions?.[0]?.publisher?.name"
                  color="emerald"
                >
                  {{ audio.versions?.[0]?.publisher?.name }}
                </Badge>
                <span
                  v-else
                  class="text-sm text-gray-400"
                >-</span>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-2">
                  <Link :href="route('audios.show', audio.slug)">
                    <IconButton color="gray">
                      <svg
                        class="w-5 h-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
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
                  </Link>
                  <Link :href="route('audios.edit', audio.slug)">
                    <IconButton color="emerald">
                      <svg
                        class="w-5 h-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      ><path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                      /></svg>
                    </IconButton>
                  </Link>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </template>
      </Table>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import Card from '@/Components/Card.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Badge from '@/Components/Badge.vue';
import IconButton from '@/Components/IconButton.vue';
import Table from '@/Components/Table/Table.vue';
import TableHead from '@/Components/Table/TableHead.vue';
import TableBody from '@/Components/Table/TableBody.vue';
import TableRow from '@/Components/Table/TableRow.vue';
import TableHeaderCell from '@/Components/Table/TableHeaderCell.vue';
import TableCell from '@/Components/Table/TableCell.vue';

const props = defineProps({
    audios: Object,
    filters: Object,
    categories: Array,
    tags: Array,
});

const search = ref(props.filters.search);
const category = ref(props.filters.category);
const tag = ref(props.filters.tag);

const resetFilters = () => {
    search.value = '';
    category.value = null;
    tag.value = null;
};

watch([search, category, tag], debounce(() => {
    router.get(route('audios.index'), {
        search: search.value,
        category: category.value,
        tag: tag.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>
