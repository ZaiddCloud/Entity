<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    name: '',
    bio: '',
    birth_year: '',
    death_year: '',
});

const submit = () => {
    form.post(route('authors.store'));
};
</script>

<template>
  <Head title="إضافة مؤلف جديد" />

  <AuthenticatedLayout title="إضافة مؤلف جديد">
    <template #header>
      <div class="flex items-center gap-4">
        <Link
          :href="route('authors.index')"
          class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-white/5 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-all"
        >
          <svg
            class="w-6 h-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          ><path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 19l-7-7 7-7"
          /></svg>
        </Link>
        <div>
          <h2 class="font-black text-2xl dark:text-white leading-tight">
            إضافة مؤلف جديد
          </h2>
          <p class="text-xs text-gray-400 font-bold mt-1">
            إضافة سجل جديد لمؤلف أو عالم إلى قاعدة البيانات
          </p>
        </div>
      </div>
    </template>

    <div class="max-w-3xl mx-auto py-8">
      <Card>
        <form
          class="space-y-6"
          @submit.prevent="submit"
        >
          <div>
            <InputLabel
              for="name"
              value="اسم المؤلف"
            />
            <TextInput
              id="name"
              v-model="form.name"
              type="text"
              class="mt-1 block w-full"
              placeholder="مثال: ابن تيمية"
              required
              autofocus
            />
            <div
              v-if="form.errors.name"
              class="text-red-500 text-sm mt-1 font-bold"
            >
              {{ form.errors.name }}
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <InputLabel
                for="birth_year"
                value="سنة الميلاد (هجري)"
              />
              <TextInput
                id="birth_year"
                v-model="form.birth_year"
                type="number"
                class="mt-1 block w-full"
                placeholder="مثال: 661"
              />
              <div
                v-if="form.errors.birth_year"
                class="text-red-500 text-sm mt-1 font-bold"
              >
                {{ form.errors.birth_year }}
              </div>
            </div>

            <div>
              <InputLabel
                for="death_year"
                value="سنة الوفاة (هجري)"
                :optional="true"
              />
              <TextInput
                id="death_year"
                v-model="form.death_year"
                type="number"
                class="mt-1 block w-full"
                placeholder="مثال: 728"
              />
              <div
                v-if="form.errors.death_year"
                class="text-red-500 text-sm mt-1 font-bold"
              >
                {{ form.errors.death_year }}
              </div>
            </div>
          </div>

          <div>
            <InputLabel
              for="bio"
              value="نبذة عن المؤلف"
              :optional="true"
            />
            <textarea
              id="bio"
              v-model="form.bio"
              class="mt-1 block w-full border-gray-200 dark:border-white/10 dark:bg-black/20 focus:border-emerald-500 focus:ring-emerald-500/20 rounded-xl shadow-sm text-sm min-h-[150px]"
              placeholder="اكتب نبذة مختصرة عن حياة المؤلف ومسيرته العلمية..."
            />
            <div
              v-if="form.errors.bio"
              class="text-red-500 text-sm mt-1 font-bold"
            >
              {{ form.errors.bio }}
            </div>
          </div>

          <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-white/5">
            <Link
              :href="route('authors.index')"
              class="text-xs font-black text-gray-400 hover:text-gray-600 transition-colors"
            >
              إلغاء
            </Link>
            <PrimaryButton
              :disabled="form.processing"
              class="!bg-emerald-600 hover:!bg-emerald-500 !shadow-emerald-500/20"
            >
              <span v-if="form.processing">جاري الحفظ...</span>
              <span v-else>حفظ البيانات</span>
            </PrimaryButton>
          </div>
        </form>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
