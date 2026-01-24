<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, inject } from 'vue';

defineProps({
    title: String,
    isSidebarOpen: Boolean
});

const emit = defineEmits(['toggleSidebar']);

const { isDark, toggleDarkMode } = inject('themeContext');
const isUserMenuOpen = ref(false);
const navbarSearch = ref('');
const user = usePage().props.auth.user;

const performSearch = () => {
    if (navbarSearch.value.trim()) {
        router.get(route('search'), { q: navbarSearch.value });
    }
};
</script>

<template>
  <nav class="h-16 sticky top-0 z-40 bg-white/80 dark:bg-[#0a0a0a]/80 backdrop-blur-xl border-b border-gray-100 dark:border-white/5 flex items-center justify-between px-8">
    <div class="flex items-center gap-6">
      <button
        class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        @click="$emit('toggleSidebar')"
      >
        <svg
          class="w-6 h-6"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 6h16M4 12h16m-7 6h7"
          />
        </svg>
      </button>

      <!-- Theme Toggle -->
      <button
        class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        @click="toggleDarkMode"
      >
        <svg
          v-if="!isDark"
          xmlns="http://www.w3.org/2000/svg"
          class="h-6 w-6"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
          />
        </svg>
        <svg
          v-else
          xmlns="http://www.w3.org/2000/svg"
          class="h-6 w-6"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
          />
        </svg>
      </button>
            
      <div class="flex items-center gap-2 text-sm text-gray-400 font-medium">
        <Link
          href="/dashboard"
          class="hover:text-indigo-500 transition-colors"
        >
          الرئيسية
        </Link>
        <svg
          class="w-4 h-4 rotate-180"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        ><path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 5l7 7-7 7"
        /></svg>
        <span class="text-gray-900 dark:text-white font-bold">{{ title }}</span>
      </div>
    </div>

    <div class="flex items-center gap-4">
      <!-- Search Bubble -->
      <div class="hidden md:flex items-center bg-gray-100 dark:bg-gray-800 rounded-xl px-4 py-2 border border-transparent focus-within:border-indigo-500/50 focus-within:bg-white dark:focus-within:bg-black transition-all">
        <svg
          class="w-4 h-4 text-gray-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        ><path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
        /></svg>
        <input 
          id="navbar-search"
          v-model="navbarSearch"
          type="text" 
          name="q"
          placeholder="بحث سريع..."
          class="bg-transparent border-none focus:ring-0 text-xs w-48 text-gray-500" 
          @keyup.enter="performSearch"
        >
      </div>

      <!-- User Menu -->
      <div class="flex items-center gap-4 border-r border-gray-100 dark:border-white/5 pr-4 mr-2">
        <div class="flex flex-col items-start leading-none hidden sm:flex">
          <span class="text-sm font-black dark:text-white">{{ user.name }}</span>
          <span class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">مسؤول النظام</span>
        </div>
        <div class="relative">
          <button 
            class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center p-0.5 border border-gray-200 dark:border-white/10 overflow-hidden"
            @click="isUserMenuOpen = !isUserMenuOpen"
          >
            <div class="w-full h-full rounded-lg bg-linear-to-tr from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
              <span class="text-xs font-black">{{ user.name.charAt(0) }}</span>
            </div>
          </button>
                    
          <!-- Dropdown Menu -->
          <div 
            v-if="isUserMenuOpen"
            class="absolute left-0 top-12 w-48 bg-white dark:bg-[#0f0f0f] rounded-2xl shadow-2xl border border-gray-100 dark:border-white/5 py-2 z-50 animate-in fade-in slide-in-from-top-2 duration-200"
          >
            <Link 
              :href="route('logout')" 
              method="post" 
              as="button" 
              class="w-full text-right px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors"
            >
              تسجيل الخروج
            </Link>
          </div>
                    
          <!-- Backdrop to close menu when clicking outside -->
          <div 
            v-if="isUserMenuOpen" 
            class="fixed inset-0 z-40" 
            @click="isUserMenuOpen = false"
          />
        </div>
      </div>
    </div>
  </nav>
</template>
