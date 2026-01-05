<template>
    <AuthenticatedLayout title="إضافة تعليق">
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('comments.index')"
                    class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-xl transition-all"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </Link>
                <div>
                    <h2 class="font-black text-2xl dark:text-white leading-tight text-emerald-600">إضافة تعليق جديد</h2>
                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-widest">التفاعل مع المحتوى عبر إضافة تعقيب أو ملاحظة عامة</p>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto py-8">
            <Card>
                <form @submit.prevent="form.post(route('comments.store'))" class="space-y-8">
                    <div class="space-y-2">
                        <InputLabel for="content" value="نص التعليق" />
                        <textarea
                            id="content"
                            v-model="form.content"
                            rows="6"
                            class="w-full rounded-2xl border-gray-200 dark:border-white/10 dark:bg-black/20 focus:border-emerald-500 focus:ring-emerald-500/20 text-sm font-medium transition-all"
                            placeholder="اكتب تعليقك هنا..."
                            required
                        ></textarea>
                        <p v-if="form.errors.content" class="text-xs text-rose-500 font-bold">{{ form.errors.content }}</p>
                    </div>

                    <!-- Note: entity_id and entity_type are usually passed as hidden or pre-selected in a real flow, 
                         but here we keep the original simplified create form structure -->

                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-white/5">
                        <Link
                            :href="route('comments.index')"
                            class="px-6 py-3 text-sm font-black text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            إلغاء
                        </Link>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            حفظ التعليق
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
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    content: '',
    entity_id: '',
    entity_type: '',
});
</script>
