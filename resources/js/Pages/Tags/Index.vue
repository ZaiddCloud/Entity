<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    tags: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('tags.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const selectedIds = ref([]);

const allSelected = computed({
    get: () => props.tags.data.length > 0 && selectedIds.value.length === props.tags.data.length,
    set: (value) => {
        selectedIds.value = value ? props.tags.data.map(t => t.id) : [];
    }
});

const bulkDelete = () => {
    if (confirm('هل أنت متأكد من حذف الوسوم المحددة؟')) {
        router.post(route('tags.bulk-destroy'), {
            ids: selectedIds.value
        }, {
            onSuccess: () => selectedIds.value = [],
        });
    }
};
</script>

<template>
    <AuthenticatedLayout title="الوسوم">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    الوسوم
                </h2>
                <div class="flex items-center gap-4">
                    <button
                        v-if="selectedIds.length > 0"
                        @click="bulkDelete"
                        class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 transition ease-in-out duration-150"
                    >
                        حذف المحدد ({{ selectedIds.length }})
                    </button>
                    <Link
                        :href="route('tags.create')"
                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                        إضافة وسم جديد
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-[300px]">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="بحث في الوسوم..."
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="pb-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-right">
                                            <input type="checkbox" v-model="allSelected" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الاسم</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">النوع</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">عدد المحتويات</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="tag in tags.data" :key="tag.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" :value="tag.id" v-model="selectedIds" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ tag.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ tag.type || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <span class="ml-4">كتب: {{ tag.books_count }}</span>
                                            <span class="ml-4">صوتيات: {{ tag.audio_count }}</span>
                                            <span class="ml-4">مرئيات: {{ tag.videos_count }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('tags.show', tag.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 ml-3">عرض</Link>
                                            <Link :href="route('tags.edit', tag.id)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 ml-3">تعديل</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">
                            <Pagination :links="tags.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
