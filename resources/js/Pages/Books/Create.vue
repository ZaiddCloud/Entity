<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    authors: Array,
    publishers: Array,
    categories: Array,
});

const form = useForm({
    title: '',
    author_ids: [],
    publisher_id: '',
    isbn: '',
    pages: '',
    published_year: '',
    edition_number: 1,
    description: '',
    cover: null,
    file: null,
});

const submit = () => {
    form.post(route('books.store'));
};
</script>

<template>
  <AuthenticatedLayout title="إضافة كتاب جديد">
    <template #header>
      <h2 class="font-black text-2xl dark:text-white leading-tight">
        إضافة كتاب جديد
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <Card>
          <form
            class="space-y-8"
            @submit.prevent="submit"
          >
            <!-- Title -->
            <div>
              <label
                for="title"
                class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"
              >عنوان الكتاب</label>
              <TextInput
                id="title"
                v-model="form.title"
                type="text"
                class="w-full"
                required
                autofocus
                placeholder="مثال: مقدمة ابن خلدون"
              />
              <div
                v-if="form.errors.title"
                class="mt-2 text-xs font-bold text-rose-500"
              >
                {{ form.errors.title }}
              </div>
            </div>

            <!-- Authors (Multi-Select) -->
            <div>
              <label
                for="authors"
                class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"
              >المؤلفون</label>
              <div class="relative">
                <select
                  id="authors"
                  v-model="form.author_ids"
                  multiple
                  class="block w-full border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-black focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-sm font-medium transition-all bg-gray-50 dark:bg-white/5 h-32 custom-scrollbar"
                >
                  <option
                    v-for="author in authors"
                    :key="author.id"
                    :value="author.id"
                    class="py-2 px-4"
                  >
                    {{ author.name }}
                  </option>
                </select>
                <div class="absolute top-2 left-2 pointer-events-none">
                  <svg
                    class="w-5 h-5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  /></svg>
                </div>
              </div>
              <p class="text-[10px] font-bold text-gray-400 mt-2">
                اضغط Ctrl (أو Cmd) لتحديد أكثر من مؤلف
              </p>
              <div
                v-if="form.errors.author_ids"
                class="mt-2 text-xs font-bold text-rose-500"
              >
                {{ form.errors.author_ids }}
              </div>
            </div>

            <!-- Publisher -->
            <div>
              <label
                for="publisher"
                class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"
              >الناشر</label>
              <SelectInput
                id="publisher"
                v-model="form.publisher_id"
                class="w-full"
                :options="publishers"
                placeholder="اختر ناشر..."
              />
              <div
                v-if="form.errors.publisher_id"
                class="mt-2 text-xs font-bold text-rose-500"
              >
                {{ form.errors.publisher_id }}
              </div>
            </div>

            <!-- Version Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label
                  for="isbn"
                  class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"
                >ISBN</label>
                <TextInput
                  id="isbn"
                  v-model="form.isbn"
                  type="text"
                  class="w-full"
                />
                <div
                  v-if="form.errors.isbn"
                  class="mt-2 text-xs font-bold text-rose-500"
                >
                  {{ form.errors.isbn }}
                </div>
              </div>
              <div>
                <label
                  for="pages"
                  class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"
                >عدد الصفحات</label>
                <TextInput
                  id="pages"
                  v-model="form.pages"
                  type="number"
                  class="w-full"
                />
              </div>
              <div>
                <label
                  for="published_year"
                  class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"
                >سنة النشر</label>
                <TextInput
                  id="published_year"
                  v-model="form.published_year"
                  type="number"
                  class="w-full"
                />
              </div>
              <div>
                <label
                  for="edition_number"
                  class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"
                >رقم الطبعة</label>
                <TextInput
                  id="edition_number"
                  v-model="form.edition_number"
                  type="number"
                  class="w-full"
                />
              </div>
            </div>

            <!-- Description -->
            <div>
              <label
                for="description"
                class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"
              >الوصف</label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="block w-full border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-black focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-sm font-medium transition-all bg-gray-50 dark:bg-white/5"
              />
              <div
                v-if="form.errors.description"
                class="mt-2 text-xs font-bold text-rose-500"
              >
                {{ form.errors.description }}
              </div>
            </div>

            <!-- Files -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label
                  for="cover"
                  class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"
                >صورة الغلاف</label>
                <div class="relative group">
                  <input
                    id="cover"
                    type="file"
                    class="block w-full text-xs text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all bg-gray-50 dark:bg-white/5 rounded-2xl p-2 cursor-pointer"
                    accept="image/*"
                    @input="form.cover = $event.target.files[0]"
                  >
                </div>
                <div
                  v-if="form.errors.cover"
                  class="mt-2 text-xs font-bold text-rose-500"
                >
                  {{ form.errors.cover }}
                </div>
              </div>

              <div>
                <label
                  for="file"
                  class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"
                >ملف الكتاب (PDF) <span class="text-rose-500">*</span></label>
                <input
                  id="file"
                  type="file"
                  class="block w-full text-xs text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all bg-gray-50 dark:bg-white/5 rounded-2xl p-2 cursor-pointer"
                  accept="application/pdf"
                  required
                  @input="form.file = $event.target.files[0]"
                >
                <div
                  v-if="form.errors.file"
                  class="mt-2 text-xs font-bold text-rose-500"
                >
                  {{ form.errors.file }}
                </div>
                <progress
                  v-if="form.progress"
                  :value="form.progress.percentage"
                  max="100"
                  class="w-full mt-4 h-1.5 rounded-full bg-gray-100 [&::-webkit-progress-value]:bg-indigo-600 [&::-moz-progress-bar]:bg-indigo-600 overflow-hidden"
                >
                  {{ form.progress.percentage }}%
                </progress>
              </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6">
              <Link
                :href="route('books.index')"
                class="text-sm font-bold text-gray-500 hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
              >
                إلغاء
              </Link>
              <PrimaryButton
                type="submit"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
              >
                حفظ الكتاب
              </PrimaryButton>
            </div>
          </form>
        </Card>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

