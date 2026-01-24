<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

// UI Components
import Card from '@/Components/Card.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    collection: Object,
});

const form = useForm({
    name: props.collection.name,
    description: props.collection.description || '',
    is_public: props.collection.is_public,
});

const submit = () => {
    form.put(route('collections.update', props.collection.id));
};
</script>

<template>
  <AuthenticatedLayout :title="'تعديل: ' + collection.name">
    <template #header>
      <div class="flex items-center gap-4">
        <Link
          :href="route('collections.index')"
          class="p-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-all"
        >
          <svg
            class="w-6 h-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          ><path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 19l-7-7 7-7"
          /></svg>
        </Link>
        <div>
          <h2 class="font-black text-2xl dark:text-white leading-tight text-blue-500">
            تعديل المجموعة: {{ collection.name }}
          </h2>
          <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">
            تحديث بيانات القائمة المخصصة
          </p>
        </div>
      </div>
    </template>

    <div class="max-w-4xl mx-auto">
      <form
        class="space-y-8 animate-fade-in-up"
        @submit.prevent="submit"
      >
        <Card class="!p-8 overflow-visible relative">
          <!-- Icon Badge -->
          <div class="absolute -top-4 -right-4 w-12 h-12 bg-blue-500 rounded-2xl shadow-lg shadow-blue-500/20 flex items-center justify-center text-white">
            <svg
              class="w-6 h-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            ><path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
            /></svg>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-1 gap-8">
            <!-- Name -->
            <div class="space-y-2">
              <InputLabel
                for="name"
                value="اسم المجموعة"
                required
              />
              <TextInput
                id="name"
                v-model="form.name"
                type="text"
                class="w-full"
                required
                placeholder="مثلاً: مجموعتي المختارة، كتب العقيدة..."
              />
              <p
                v-if="form.errors.name"
                class="text-xs text-rose-500 font-bold"
              >
                {{ form.errors.name }}
              </p>
            </div>

            <!-- Description -->
            <div class="space-y-2">
              <InputLabel
                for="description"
                value="الوصف"
              />
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl text-sm font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all dark:text-white p-4"
                placeholder="اكتب وصفاً مختصراً عن محتويات هذه المجموعة..."
              />
              <p
                v-if="form.errors.description"
                class="text-xs text-rose-500 font-bold"
              >
                {{ form.errors.description }}
              </p>
            </div>

            <!-- Visibility -->
            <div class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5">
              <div class="flex-1">
                <h4 class="text-sm font-black dark:text-white">
                  مجموعة عامة
                </h4>
                <p class="text-[10px] text-gray-400 font-bold">
                  المجموعات العامة تظهر للجميع في البحث والملف الشخصي
                </p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  v-model="form.is_public"
                  type="checkbox"
                  class="sr-only peer"
                >
                <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none dark:bg-white/10 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500 shadow-inner" />
              </label>
            </div>
          </div>

          <div class="mt-12 flex justify-end gap-4 border-t border-gray-100 dark:border-white/5 pt-8">
            <Link :href="route('collections.index')">
              <button
                type="button"
                class="px-8 py-3 rounded-xl text-sm font-bold text-gray-500 hover:text-gray-700 dark:hover:text-white transition-all"
              >
                إلغاء
              </button>
            </Link>
            <PrimaryButton
              :disabled="form.processing"
              class="!px-12 !bg-blue-600 hover:!bg-blue-500"
            >
              {{ form.processing ? 'جاري الحفظ...' : 'حفظ التعديلات' }}
            </PrimaryButton>
          </div>
        </Card>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
