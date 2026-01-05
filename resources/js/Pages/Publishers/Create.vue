<template>
    <AuthenticatedLayout title="إضافة دار نشر">
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('publishers.index')" class="p-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-all">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </Link>
                <div>
                    <h2 class="font-black text-2xl dark:text-white leading-tight text-emerald-600">إضافة دار نشر جديدة</h2>
                    <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">إنشاء سجل جديد في قاعدة بيانات الناشرين</p>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto">
            <form @submit.prevent="submit" class="space-y-8 animate-fade-in-up">
                <Card class="!p-8 overflow-visible relative">
                    <div class="absolute -top-4 -right-4 w-12 h-12 bg-emerald-500 rounded-2xl shadow-lg shadow-emerald-500/20 flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Name -->
                        <div class="md:col-span-2 space-y-2">
                             <InputLabel for="name" value="اسم دار النشر" required />
                             <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full"
                                required
                                placeholder="مثلاً: دار المعرفة، مؤسسة الرسالة..."
                            />
                            <p v-if="form.errors.name" class="text-xs text-rose-500 font-bold">{{ form.errors.name }}</p>
                        </div>

                        <!-- Country Code -->
                        <div class="space-y-2">
                            <InputLabel for="country_code" value="رمز الدولة (ISO)" />
                            <TextInput
                                id="country_code"
                                v-model="form.country_code"
                                type="text"
                                class="w-full"
                                placeholder="مخلاً: SA, EG, LB..."
                                maxlength="3"
                            />
                            <p v-if="form.errors.country_code" class="text-xs text-rose-500 font-bold">{{ form.errors.country_code }}</p>
                        </div>

                        <!-- Logo -->
                        <div class="space-y-2">
                             <InputLabel value="شعار دار النشر" />
                             <div class="flex items-center justify-center w-full">
                                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-2xl cursor-pointer bg-gray-50 dark:hover:bg-white/5 dark:bg-black/20 hover:bg-gray-100 dark:border-white/10 dark:hover:border-emerald-500/50 transition-all group">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-4 text-gray-500 group-hover:text-emerald-500 transition-colors" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-black">اضغط لرفع الشعار</span></p>
                                        <p class="text-xs text-gray-400 uppercase font-bold tracking-tighter">PNG, JPG up to 2MB</p>
                                    </div>
                                    <input type="file" class="hidden" @input="form.logo = $event.target.files[0]" />
                                </label>
                            </div>
                            <p v-if="form.errors.logo" class="text-xs text-rose-500 font-bold">{{ form.errors.logo }}</p>
                            <div v-if="form.logo" class="mt-2 text-xs font-bold text-emerald-500 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                تم اختيار: {{ form.logo.name }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 flex justify-end gap-4 border-t border-gray-100 dark:border-white/5 pt-8">
                        <Link :href="route('publishers.index')">
                            <button type="button" class="px-8 py-3 rounded-xl text-sm font-bold text-gray-500 hover:text-gray-700 dark:hover:text-white transition-all">إلغاء</button>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="!px-12 !bg-emerald-600 hover:!bg-emerald-500">
                             {{ form.processing ? 'جاري الحفظ...' : 'حفظ البيانات' }}
                        </PrimaryButton>
                    </div>
                </Card>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    name: '',
    country_code: '',
    logo: null,
});

const submit = () => {
    form.post(route('publishers.store'), {
        onSuccess: () => {
            // Success logic if needed
        }
    });
};
</script>
