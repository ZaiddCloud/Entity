<template>
    <AuthenticatedLayout title="تعديل التصنيف">
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('categories.index')"
                    class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-xl transition-all"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </Link>
                <div>
                    <h2 class="font-black text-2xl dark:text-white leading-tight text-blue-600">تعديل التصنيف</h2>
                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-widest">تحديث بيانات التصنيف: {{ category.name }}</p>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto py-8">
            <Card>
                <form @submit.prevent="form.put(route('categories.update', category.id))" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Name -->
                        <div class="space-y-2">
                            <InputLabel for="name" value="اسم التصنيف" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full"
                                placeholder="مثال: الفنون، العلوم، التكنولوجيا..."
                                required
                            />
                            <p v-if="form.errors.name" class="text-xs text-rose-500 font-bold">{{ form.errors.name }}</p>
                        </div>

                        <!-- Parent Category -->
                        <div class="space-y-2">
                            <InputLabel for="parent_id" value="التصنيف الأعلى (اختياري)" />
                            <SelectInput
                                id="parent_id"
                                v-model="form.parent_id"
                                :options="parentCategories.map(c => ({ value: c.id, label: c.name }))"
                                class="w-full"
                                placeholder="اختر التصنيف الرئيسي..."
                            />
                            <p v-if="form.errors.parent_id" class="text-xs text-rose-500 font-bold">{{ form.errors.parent_id }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <InputLabel for="description" value="وصف التصنيف" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="w-full rounded-2xl border-gray-200 dark:border-white/10 dark:bg-black/20 focus:border-emerald-500 focus:ring-emerald-500/20 text-sm font-medium transition-all"
                            placeholder="وصف مختصر لمجال هذا التصنيف..."
                        ></textarea>
                        <p v-if="form.errors.description" class="text-xs text-rose-500 font-bold">{{ form.errors.description }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-white/5">
                        <Link
                            :href="route('categories.index')"
                            class="px-6 py-3 text-sm font-black text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            إلغاء
                        </Link>
                        <PrimaryButton
                            title="تحديث"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            تحديث التصنيف
                        </PrimaryButton>
                    </div>
                </form>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    category: Object,
    parentCategories: Array,
});

const form = useForm({
    name: props.category.name,
    parent_id: props.category.parent_id || '',
    description: props.category.description || '',
});
</script>
