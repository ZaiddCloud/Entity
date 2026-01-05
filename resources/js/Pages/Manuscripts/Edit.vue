<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    manuscript: Object,
    authors: Array,
    publishers: Array,
    categories: Array,
});

const form = useForm({
    title: props.manuscript.title,
    century: props.manuscript.century || '',
    author_ids: props.manuscript.authors?.map(a => a.id) || [],
    publisher_id: props.manuscript.versions?.[0]?.publisher_id || '',
    pages: props.manuscript.versions?.[0]?.pages || '',
    published_year: props.manuscript.versions?.[0]?.published_year || '',
    description: props.manuscript.description || '',
    cover: null,
    file: null,
    _method: 'PUT',
});

const submit = () => {
    form.post(route('manuscripts.update', props.manuscript.slug));
};
</script>

<template>
    <AuthenticatedLayout :title="'تعديل: ' + manuscript.title">
        <template #header>
            <h2 class="font-black text-xl text-gray-800 dark:text-gray-200 leading-tight">
                تعديل المخطوطة: {{ manuscript.title }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <Card class="!p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Heading -->
                        <div class="mb-8 border-b border-gray-100 dark:border-white/5 pb-4">
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">تعديل البيانات</h3>
                            <p class="text-sm text-gray-500">تحديث معلومات المخطوطة</p>
                        </div>

                        <!-- Title -->
                        <div>
                            <InputLabel for="title">شهرة المخطوطة (العنوان)</InputLabel>
                            <TextInput
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <div v-if="form.errors.title" class="mt-2 text-sm text-red-600">{{ form.errors.title }}</div>
                        </div>

                        <!-- Authors & Publisher -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Authors (Multi-Select) -->
                            <div class="md:col-span-2">
                                <InputLabel for="authors">المؤلف / الناسخ</InputLabel>
                                <div class="mt-1 relative">
                                    <select
                                        id="authors"
                                        multiple
                                        v-model="form.author_ids"
                                        class="block w-full rounded-2xl border-gray-300 dark:border-gray-700 dark:bg-black/20 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500/20 shadow-sm h-32 text-sm transition-all text-center md:text-right"
                                    >
                                        <option v-for="author in authors" :key="author.id" :value="author.id">
                                            {{ author.name }}
                                        </option>
                                    </select>
                                    <p class="text-xs text-gray-400 mt-2 font-bold flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        اضغط على Ctrl (أو Cmd) لتحديد عدة مؤلفين
                                    </p>
                                </div>
                                <div v-if="form.errors.author_ids" class="mt-2 text-sm text-red-600">{{ form.errors.author_ids }}</div>
                            </div>

                            <!-- Publisher -->
                            <div class="md:col-span-2">
                                <InputLabel for="publisher">المكتبة / المصدر</InputLabel>
                                <SelectInput
                                    id="publisher"
                                    v-model="form.publisher_id"
                                    :options="publishers"
                                    placeholder="اختر مصدر المخطوطة..."
                                    class="mt-1 block w-full"
                                />
                                <div v-if="form.errors.publisher_id" class="mt-2 text-sm text-red-600">{{ form.errors.publisher_id }}</div>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <InputLabel for="century" :optional="true">القرن الهجري</InputLabel>
                                <TextInput
                                    id="century"
                                    v-model="form.century"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="مثلاً: القرن الثامن"
                                />
                            </div>
                            <div>
                                <InputLabel for="pages" :optional="true">عدد الأوراق</InputLabel>
                                <TextInput
                                    id="pages"
                                    v-model="form.pages"
                                    type="number"
                                    class="mt-1 block w-full"
                                    placeholder="0"
                                />
                            </div>
                            <div>
                                <InputLabel for="published_year" :optional="true">سنة النسخ (رقمياً)</InputLabel>
                                <TextInput
                                    id="published_year"
                                    v-model="form.published_year"
                                    type="number"
                                    class="mt-1 block w-full"
                                    placeholder="مثلاً: 1445"
                                />
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <InputLabel for="description" :optional="true">ملاحظات / وصف</InputLabel>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="mt-1 block w-full rounded-2xl border-gray-300 dark:border-gray-700 dark:bg-black/20 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500/20 shadow-sm text-sm transition-all"
                            ></textarea>
                            <div v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</div>
                        </div>

                        <!-- File Uploads -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100 dark:border-white/5">
                            <div>
                                <InputLabel for="cover" :optional="true">تحديث صورة الغلاف</InputLabel>
                                <div class="mt-1 flex items-center justify-center w-full">
                                    <label for="cover" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 dark:border-gray-700 border-dashed rounded-2xl cursor-pointer bg-gray-50 dark:bg-white/5 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-bold">اضغط للرفع</span> أو اسحب</p>
                                        </div>
                                        <input id="cover" type="file" @input="form.cover = $event.target.files[0]" accept="image/*" class="hidden" />
                                    </label>
                                </div>
                                <div v-if="form.cover" class="mt-2 text-xs text-emerald-500 font-bold text-center">
                                    تم اختيار: {{ form.cover.name }}
                                </div>
                            </div>

                            <div>
                                <InputLabel for="file" :optional="true">تحديث ملف المخطوطة</InputLabel>
                                <div class="mt-1 flex items-center justify-center w-full">
                                    <label for="file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 dark:border-gray-700 border-dashed rounded-2xl cursor-pointer bg-gray-50 dark:bg-white/5 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-bold">اضغط للرفع</span> (PDF)</p>
                                        </div>
                                        <input id="file" type="file" @input="form.file = $event.target.files[0]" accept="application/pdf" class="hidden" />
                                    </label>
                                </div>
                                <div v-if="form.file" class="mt-2 text-xs text-emerald-500 font-bold text-center">
                                    تم اختيار: {{ form.file.name }}
                                </div>
                                <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full mt-2 h-1 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500" :style="{ width: form.progress.percentage + '%' }"></div>
                                </progress>
                                <div v-if="form.errors.file" class="mt-2 text-sm text-red-600">{{ form.errors.file }}</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-white/5">
                            <Link :href="route('manuscripts.show', manuscript.slug)" class="text-sm font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition-colors">
                                إلغاء
                            </Link>
                            <PrimaryButton class="bg-emerald-600 hover:bg-emerald-500" :disabled="form.processing">
                                تحديث البيانات
                            </PrimaryButton>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
