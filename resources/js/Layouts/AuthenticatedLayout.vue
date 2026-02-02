<script setup>
import { ref, onMounted, provide } from 'vue';
import { Head } from '@inertiajs/vue3';
import Sidebar from './Partials/Sidebar.vue';
import Navbar from './Partials/Navbar.vue';

defineProps({
    title: String,
});

const isSidebarOpen = ref(localStorage.getItem('isSidebarOpen') !== 'false'); // Default to true if not set
const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
    localStorage.setItem('isSidebarOpen', isSidebarOpen.value);
};

const isDark = ref(false);

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

onMounted(() => {
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
    }
});

provide('themeContext', { isDark, toggleDarkMode });
</script>

<template>
  <div
    class="min-h-screen bg-gray-50 dark:bg-[#050505] text-gray-900 dark:text-[#ededec]"
    dir="rtl"
  >
    <Head :title="title" />
        
    <!-- Sidebar -->
    <Sidebar :is-open="isSidebarOpen" />

    <!-- Main Content Area -->
    <div :class="['transition-all duration-300 flex flex-col flex-1', isSidebarOpen ? 'mr-64' : 'mr-20']">
      <!-- Navbar (Sticky) -->
      <Navbar 
        class="sticky top-0 z-40 bg-gray-50 dark:bg-[#050505]"
        :title="title" 
        :is-sidebar-open="isSidebarOpen"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Header Slot Implementation -->
      <header
        v-if="$slots.header"
        class="px-8 mt-8"
      >
        <div class="bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2rem] p-6 shadow-sm">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Implementation Section -->
      <main class="p-8 animate-fade-in custom-main min-h-screen">
        <slot />
      </main>
            
      <!-- Footer -->
      <footer class="py-12 border-t border-gray-100 dark:border-white/5 mt-12 px-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-gray-400 text-xs font-medium">
          <p>&copy; {{ new Date().getFullYear() }} Entity App. جميع الحقوق محفوظة.</p>
          <div class="flex gap-6">
            <a
              href="#"
              class="hover:text-indigo-500 transition-colors"
            >الدعم الفني</a>
            <a
              href="#"
              class="hover:text-indigo-500 transition-colors"
            >سياسة الخصوصية</a>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>

<style>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out forwards;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.3);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.5);
}

.custom-main {
    min-height: calc(100vh - 16rem);
}
</style>

