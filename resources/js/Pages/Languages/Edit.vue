<template>
    <AuthenticatedLayout title="تعديل اللغة">
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('languages.index')"
                    class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-xl transition-all"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </Link>
                <div>
                    <h2 class="font-black text-2xl dark:text-white leading-tight text-blue-600">تعديل اللغة</h2>
                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-widest">تحديث بيانات اللغة: {{ language.name }}</p>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto py-8">
            <Card>
                <form @submit.prevent="form.put(route('languages.update', language.id))" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Name -->
                        <div class="space-y-2">
                            <InputLabel for="name" value="اسم اللغة" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full"
                                placeholder="مثال: العربية، الإنجليزية، التركية..."
                                required
                            />
                            <p v-if="form.errors.name" class="text-xs text-rose-500 font-bold">{{ form.errors.name }}</p>
                        </div>

                        <!-- Code -->
                        <div class="space-y-2">
                            <InputLabel for="code" value="الرمز الدولي (ISO)" />
                            <TextInput
                                id="code"
                                v-model="form.code"
                                type="text"
                                class="w-full lowercase"
                                placeholder="مثال: ar, en, tr..."
                                required
                            />
                            <p v-if="form.errors.code" class="text-xs text-rose-500 font-bold">{{ form.errors.code }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-white/5">
                        <Link
                            :href="route('languages.index')"
                            class="px-6 py-3 text-sm font-black text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            إلغاء
                        </Link>
                        <PrimaryButton
                            title="تحديث"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            تحديث اللغة
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

const props = defineProps({
    language: Object,
});

const form = useForm({
    name: props.language.name,
    code: props.language.code,
});
</script>
