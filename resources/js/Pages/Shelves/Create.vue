<template>
    <AuthenticatedLayout title="إضافة رف جديد">
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('shelves.index')"
                    class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-xl transition-all"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </Link>
                <div>
                    <h2 class="font-black text-2xl dark:text-white leading-tight text-emerald-600">إضافة رف جديد</h2>
                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-widest">إنشاء موقع جديد لتخزين المحتوى</p>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto">
            <Card>
                <form @submit.prevent="form.post(route('shelves.store'))" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Location Code -->
                        <div class="space-y-2">
                            <InputLabel for="location_code" value="رمز الموقع / الرف" />
                            <TextInput
                                id="location_code"
                                v-model="form.location_code"
                                type="text"
                                class="w-full"
                                placeholder="مثال: A1, B2, Shelf-01..."
                                required
                            />
                            <p v-if="form.errors.location_code" class="text-xs text-rose-500 font-bold">{{ form.errors.location_code }}</p>
                        </div>

                        <!-- Capacity -->
                        <div class="space-y-2">
                            <InputLabel for="capacity" value="السعة الاستيعابية (عدد النسخ)" />
                            <TextInput
                                id="capacity"
                                v-model="form.capacity"
                                type="number"
                                class="w-full font-mono"
                                placeholder="0"
                                required
                            />
                            <p v-if="form.errors.capacity" class="text-xs text-rose-500 font-bold">{{ form.errors.capacity }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-white/5">
                        <Link
                            :href="route('shelves.index')"
                            class="px-6 py-3 text-sm font-black text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            إلغاء
                        </Link>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            حفظ الرف
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
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    location_code: '',
    capacity: 50,
});
</script>
