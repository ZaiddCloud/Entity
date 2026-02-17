<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    authors: Array,
    publishers: Array,
    categories: Array,
});

const form = useForm({
    title: '',
    code: '',
    manuscript_century: '',
    manuscript_century_label: '',
    author_ids: [],
    publisher_id: '',
    pages: '',
    published_year: '',
    description: '',
    original_title: '',
    catalog_number: '',
    scribe: '',
    madhab: '',
    copy_date: '',
    parts: '',
    script_type: '',
    dimensions: '',
    lines_per_page: '',
    inscriptions: '',
    notes: '',
    is_autograph: false,
    manuscript_start: '',
    manuscript_end: '',
    cover: null,
    file: null,
});

const submit = () => {
    form.post(route('manuscripts.store'));
};
</script>

<template>
  <AuthenticatedLayout title="إضافة مخطوطة جديدة">
    <template #header>
      <h2 class="font-black text-xl text-gray-800 dark:text-gray-200 leading-tight">
        إضافة مخطوطة جديدة
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <Card class="!p-8">
          <form
            class="space-y-6"
            @submit.prevent="submit"
          >
            <!-- Heading -->
            <div class="mb-8 border-b border-gray-100 dark:border-white/5 pb-4">
              <h3 class="text-lg font-black text-gray-900 dark:text-white">
                بيانات المخطوطة
              </h3>
              <p class="text-sm text-gray-500">
                أدخل المعلومات الأساسية للمخطوطة الأثرية
              </p>
            </div>

            <!-- Title & Code -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="md:col-span-2">
                <InputLabel for="title">
                  شهرة المخطوطة (العنوان)
                </InputLabel>
                <TextInput
                  id="title"
                  v-model="form.title"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  autofocus
                  placeholder="مثلاً: فتح الباري شرح صحيح البخاري"
                />
                <div v-if="form.errors.title" class="mt-2 text-sm text-red-600">
                  {{ form.errors.title }}
                </div>
              </div>
              <div>
                <InputLabel for="code" :optional="true">
                  كود العمل / المجموعة
                </InputLabel>
                <TextInput
                  id="code"
                  v-model="form.code"
                  type="text"
                  class="mt-1 block w-full font-mono uppercase"
                  placeholder="مثلاً: FB_GROUP_1"
                />
                <p class="mt-1 text-[10px] text-gray-400">استخدم نفس الكود لربط النسخ المختلفة لهذا العمل</p>
                <div v-if="form.errors.code" class="mt-2 text-sm text-red-600">
                  {{ form.errors.code }}
                </div>
              </div>
            </div>

            <!-- Authors & Publisher -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Authors (Multi-Select) -->
              <div class="md:col-span-2">
                <InputLabel for="authors">
                  المؤلف / الناسخ
                </InputLabel>
                <div class="mt-1 relative">
                  <select
                    id="authors"
                    v-model="form.author_ids"
                    multiple
                    class="block w-full rounded-2xl border-gray-300 dark:border-gray-700 dark:bg-black/20 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500/20 shadow-sm h-32 text-sm transition-all text-center md:text-right"
                  >
                    <option
                      v-for="author in authors"
                      :key="author.id"
                      :value="author.id"
                    >
                      {{ author.name }}
                    </option>
                  </select>
                  <p class="text-xs text-gray-400 mt-2 font-bold flex items-center gap-1">
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    ><path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    /></svg>
                    اضغط على Ctrl (أو Cmd) لتحديد عدة مؤلفين
                  </p>
                </div>
                <div
                  v-if="form.errors.author_ids"
                  class="mt-2 text-sm text-red-600"
                >
                  {{ form.errors.author_ids }}
                </div>
              </div>

              <!-- Publisher -->
              <div class="md:col-span-2">
                <InputLabel for="publisher">
                  المكتبة / المصدر
                </InputLabel>
                <SelectInput
                  id="publisher"
                  v-model="form.publisher_id"
                  :options="publishers"
                  placeholder="اختر مصدر المخطوطة..."
                  class="mt-1 block w-full"
                />
                <div
                  v-if="form.errors.publisher_id"
                  class="mt-2 text-sm text-red-600"
                >
                  {{ form.errors.publisher_id }}
                </div>
              </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div>
                <InputLabel for="manuscript_century_label" :optional="true">
                  القرن (نصي)
                </InputLabel>
                <TextInput
                  id="manuscript_century_label"
                  v-model="form.manuscript_century_label"
                  type="text"
                  class="mt-1 block w-full"
                  placeholder="مثلاً: 9 هـ"
                />
              </div>
              <div>
                <InputLabel for="manuscript_century" :optional="true">
                  القرن (رقمي)
                </InputLabel>
                <TextInput
                  id="manuscript_century"
                  v-model="form.manuscript_century"
                  type="text"
                  class="mt-1 block w-full"
                  placeholder="مثلاً: 9"
                />
              </div>
              <div>
                <InputLabel for="copy_date" :optional="true">
                  تاريخ النسخ
                </InputLabel>
                <TextInput
                  id="copy_date"
                  v-model="form.copy_date"
                  type="text"
                  class="mt-1 block w-full"
                  placeholder="مثلاً: 850 هـ"
                />
              </div>
              <div>
                <InputLabel for="catalog_number" :optional="true">
                  رقم المخطوط
                </InputLabel>
                <TextInput
                  id="catalog_number"
                  v-model="form.catalog_number"
                  type="text"
                  class="mt-1 block w-full font-mono"
                  placeholder="مثلاً: MS-1234"
                />
              </div>
            </div>

            <!-- Physical Metadata Section -->
            <div class="pt-6 border-t border-gray-100 dark:border-white/5">
              <h3 class="text-sm font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>
                الوصف الفيزيائي والميتا-بيانات
              </h3>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                 <!-- Is Autograph Toggle -->
                 <div class="md:col-span-3">
                     <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" v-model="form.is_autograph" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 dark:peer-focus:ring-emerald-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-emerald-600 transition-colors">
                            نسخة بخط المؤلف (Autograph)
                        </span>
                     </label>
                 </div>

                <div class="md:col-span-2">
                  <InputLabel for="original_title" :optional="true">العنوان كما ورد في النسخة</InputLabel>
                  <TextInput id="original_title" v-model="form.original_title" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                  <InputLabel for="scribe" :optional="true">الناسخ</InputLabel>
                  <TextInput id="scribe" v-model="form.scribe" type="text" class="mt-1 block w-full" />
                </div>

                <div>
                  <InputLabel for="madhab" :optional="true">المذهب</InputLabel>
                  <SelectInput 
                    id="madhab" 
                    v-model="form.madhab" 
                    :options="[{id: 'شافعي', name: 'شافعي'}, {id: 'حنفي', name: 'حنفي'}, {id: 'مالكي', name: 'مالكي'}, {id: 'حنبلي', name: 'حنبلي'}, {id: 'ظاهري', name: 'ظاهري'}]" 
                    class="mt-1 block w-full" 
                  />
                </div>
                <div>
                  <InputLabel for="script_type" :optional="true">نوع الخط</InputLabel>
                  <SelectInput 
                    id="script_type" 
                    v-model="form.script_type" 
                    :options="[{id: 'نسخ', name: 'نسخ'}, {id: 'كوفي', name: 'كوفي'}, {id: 'ديواني', name: 'ديواني'}, {id: 'ثلث', name: 'ثلث'}, {id: 'رقعة', name: 'رقعة'}]" 
                    class="mt-1 block w-full" 
                  />
                </div>
                <div>
                  <InputLabel for="parts" :optional="true">عدد الأجزاء</InputLabel>
                  <TextInput id="parts" v-model="form.parts" type="text" class="mt-1 block w-full" />
                </div>

                <div>
                  <InputLabel for="dimensions" :optional="true">المقاسات</InputLabel>
                  <TextInput id="dimensions" v-model="form.dimensions" type="text" class="mt-1 block w-full" placeholder="25x18 سم" />
                </div>
                <div>
                  <InputLabel for="lines_per_page" :optional="true">عدد الأسطر</InputLabel>
                  <TextInput id="lines_per_page" v-model="form.lines_per_page" type="number" class="mt-1 block w-full" />
                </div>
                <div>
                  <InputLabel for="pages" :optional="true">عدد الأوراق</InputLabel>
                  <TextInput id="pages" v-model="form.pages" type="number" class="mt-1 block w-full" />
                </div>

                <!-- Start/End Text -->
                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="manuscript_start" :optional="true">بداية المخطوط</InputLabel>
                        <textarea id="manuscript_start" v-model="form.manuscript_start" rows="3" class="mt-1 block w-full rounded-2xl border-gray-300 dark:border-gray-700 dark:bg-black/20 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500/20 shadow-sm text-sm" placeholder="الحمد لله الذي..." />
                    </div>
                    <div>
                        <InputLabel for="manuscript_end" :optional="true">نهاية المخطوط</InputLabel>
                        <textarea id="manuscript_end" v-model="form.manuscript_end" rows="3" class="mt-1 block w-full rounded-2xl border-gray-300 dark:border-gray-700 dark:bg-black/20 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500/20 shadow-sm text-sm" placeholder="وكتبه العبد الفقير..." />
                    </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                  <InputLabel for="inscriptions" :optional="true">القيود والبلاغات</InputLabel>
                  <textarea id="inscriptions" v-model="form.inscriptions" rows="3" class="mt-1 block w-full rounded-2xl border-gray-300 dark:border-gray-700 dark:bg-black/20 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500/20 shadow-sm text-sm" />
                </div>
                <div>
                  <InputLabel for="notes" :optional="true">ملاحظات إضافية</InputLabel>
                  <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 block w-full rounded-2xl border-gray-300 dark:border-gray-700 dark:bg-black/20 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500/20 shadow-sm text-sm" />
                </div>
              </div>
            </div>

            <!-- Description -->
            <div>
              <InputLabel
                for="description"
                :optional="true"
              >
                ملاحظات / وصف
              </InputLabel>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="mt-1 block w-full rounded-2xl border-gray-300 dark:border-gray-700 dark:bg-black/20 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500/20 shadow-sm text-sm transition-all"
                placeholder="أضف أي تفاصيل إضافية حول المخطوطة..."
              />
              <div
                v-if="form.errors.description"
                class="mt-2 text-sm text-red-600"
              >
                {{ form.errors.description }}
              </div>
            </div>

            <!-- File Uploads -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100 dark:border-white/5">
              <div>
                <InputLabel
                  for="cover"
                  :optional="true"
                >
                  صورة الغلاف
                </InputLabel>
                <div class="mt-1 flex items-center justify-center w-full">
                  <label
                    for="cover"
                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 dark:border-gray-700 border-dashed rounded-2xl cursor-pointer bg-gray-50 dark:bg-white/5 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors"
                  >
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <svg
                        class="w-8 h-8 mb-3 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      ><path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                      /></svg>
                      <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-bold">اضغط للرفع</span> أو اسحب الصورة</p>
                    </div>
                    <input
                      id="cover"
                      type="file"
                      accept="image/*"
                      class="hidden"
                      @input="form.cover = $event.target.files[0]"
                    >
                  </label>
                </div>
                <div
                  v-if="form.cover"
                  class="mt-2 text-xs text-emerald-500 font-bold text-center"
                >
                  تم اختيار: {{ form.cover.name }}
                </div>
              </div>

              <div>
                <InputLabel for="file">
                  ملف المخطوطة (PDF)
                </InputLabel>
                <div class="mt-1 flex items-center justify-center w-full">
                  <label
                    for="file"
                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 dark:border-gray-700 border-dashed rounded-2xl cursor-pointer bg-gray-50 dark:bg-white/5 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors"
                  >
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <svg
                        class="w-8 h-8 mb-3 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      ><path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                      /></svg>
                      <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-bold">اضغط للرفع</span> (PDF)</p>
                    </div>
                    <input
                      id="file"
                      type="file"
                      accept="application/pdf"
                      class="hidden"
                      @input="form.file = $event.target.files[0]"
                    >
                  </label>
                </div>
                <div
                  v-if="form.file"
                  class="mt-2 text-xs text-emerald-500 font-bold text-center"
                >
                  تم اختيار: {{ form.file.name }}
                </div>
                <progress
                  v-if="form.progress"
                  :value="form.progress.percentage"
                  max="100"
                  class="w-full mt-2 h-1 bg-gray-200 rounded-full overflow-hidden"
                >
                  <div
                    class="h-full bg-emerald-500"
                    :style="{ width: form.progress.percentage + '%' }"
                  />
                </progress>
                <div
                  v-if="form.errors.file"
                  class="mt-2 text-sm text-red-600"
                >
                  {{ form.errors.file }}
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-white/5">
              <Link
                :href="route('manuscripts.index')"
                class="text-sm font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition-colors"
              >
                إلغاء
              </Link>
              <PrimaryButton
                class="bg-emerald-600 hover:bg-emerald-500"
                :disabled="form.processing"
              >
                حفظ المخطوطة
              </PrimaryButton>
            </div>
          </form>
        </Card>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
