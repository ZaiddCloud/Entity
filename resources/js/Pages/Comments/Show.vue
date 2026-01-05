<template>
    <AuthenticatedLayout title="عرض التعليق">
        <!-- Premium Hero Section -->
        <template #header>
            <div class="relative overflow-hidden bg-emerald-700 rounded-[2.5rem] p-12 text-white shadow-2xl shadow-emerald-900/40">
                <!-- Abstract Background Elements -->
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-lime-400/10 rounded-full blur-3xl"></div>

                <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 rounded-3xl bg-white/10 backdrop-blur-xl flex items-center justify-center text-4xl border border-white/20 shadow-inner">
                            <svg class="w-10 h-10 text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-3 flex-wrap">
                                <h1 class="text-4xl font-black tracking-tight">تفاصيل التعليق</h1>
                                <Badge color="emerald" class="bg-emerald-500/30 text-emerald-100 border-emerald-400/30">تعليق عام</Badge>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-6 text-emerald-100/80 text-sm font-bold uppercase tracking-widest">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    <span>بواسطة: {{ comment.user?.name || 'مستخدم غير معروف' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span dir="ltr">{{ new Date(comment.created_at).toLocaleString('ar-EG') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link :href="route('comments.edit', comment.id)">
                            <PrimaryButton class="bg-white/10 hover:bg-white/20 text-white border-white/20 backdrop-blur-md">
                                تعديل التعليق
                            </PrimaryButton>
                        </Link>
                        <button 
                            @click="deleteComment"
                            class="px-6 py-3 bg-rose-500/20 hover:bg-rose-500/30 text-rose-100 rounded-xl font-black text-xs transition-all border border-rose-500/30 backdrop-blur-md"
                        >
                            حذف التعليق
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-12 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Content Card -->
                <div class="lg:col-span-2">
                    <Card class="h-full">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>
                            نص التعليق الكامل
                        </h3>
                        <div class="text-gray-700 dark:text-gray-200 leading-relaxed text-lg italic bg-gray-50 dark:bg-white/5 p-8 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-inner">
                            {{ comment.content }}
                        </div>
                    </Card>
                </div>

                <!-- Info Card -->
                <div class="lg:col-span-1">
                    <Card class="h-full">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <span class="w-1 h-4 bg-lime-500 rounded-full"></span>
                            معلومات السياق
                        </h3>
                        <dl class="space-y-6">
                            <div>
                                <dt class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">المحتوى المرتبط</dt>
                                <dd class="text-base font-black text-gray-900 dark:text-white">
                                    <Badge variant="gray" class="!bg-gray-100 dark:!bg-white/5">
                                        {{ comment.entity_type.split('\\').pop() }}
                                    </Badge>
                                </dd>
                            </div>
                            <div v-if="comment.entity">
                                <dt class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">عنوان العنصر</dt>
                                <dd class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ comment.entity.title || comment.entity.name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">الحالة</dt>
                                <dd class="text-xs font-black text-emerald-500 uppercase tracking-tighter">نشط ومنشور</dd>
                            </div>
                        </dl>
                        <div class="mt-12 pt-12 border-t border-gray-100 dark:border-white/5">
                             <Link :href="route('comments.index')" class="text-xs font-black text-emerald-600 hover:text-emerald-500 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
                                العودة لقائمة التعليقات
                            </Link>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    comment: Object,
});

const deleteComment = () => {
    if (confirm('هل أنت متأكد من حذف هذا التعليق؟')) {
        router.delete(route('comments.destroy', props.comment.id));
    }
};
</script>
