<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, inject, onMounted } from 'vue';
import { useResilientSync } from '@/Core/Sync/useResilientSync';

const props = defineProps({
    title: String,
    isSidebarOpen: Boolean
});

const emit = defineEmits(['toggleSidebar']);

const { isDark, toggleDarkMode } = inject('themeContext');
const { 
    isOnline, 
    isSyncing, 
    storageStats, 
    isExporting, 
    isRestoring, 
    isDownloading,
    updateStorageStats, 
    handleBackup, 
    handleRestore,
    handleForceSync,
    handleDownloadAll 
} = useResilientSync();

const isUserMenuOpen = ref(false);
const isDataHubOpen = ref(false);
const isPwaInstallable = ref(false);
const navbarSearch = ref('');
const user = usePage().props.auth.user;
const dataHubFileInput = ref(null);

const performSearch = () => {
    if (navbarSearch.value.trim()) {
        router.get(route('search'), { q: navbarSearch.value });
    }
};

const triggerRestore = (event) => {
    const file = event.target.files[0];
    if (file && confirm('⚠️ سيتم استبدال البيانات الحالية بالكامل. هل أنت متأكد؟')) {
        handleRestore(file);
    }
    event.target.value = '';
};

const installPwa = async () => {
    if (window.pwaInstallPrompt) {
        window.pwaInstallPrompt.prompt();
        const { outcome } = await window.pwaInstallPrompt.userChoice;
        if (outcome === 'accepted') {
            isPwaInstallable.value = false;
        }
    }
};

onMounted(() => {
    updateStorageStats();

    // Catch PWA event from GlobalSyncObserver
    window.addEventListener('pwa-can-install', (e) => {
        isPwaInstallable.value = e.detail;
    });

    // Check if prompt is already available
    if (window.pwaInstallPrompt) {
        isPwaInstallable.value = true;
    }
});
</script>

<template>
  <nav class="h-16 sticky top-0 z-40 bg-white dark:bg-[#0a0a0a] border-b border-gray-100 dark:border-white/5 flex items-center justify-between px-8">
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

      <!-- Data Management Hub -->
      <div class="relative">
        <button
          v-tooltip="'إدارة البيانات والتموضع 🛡️'"
          class="hidden lg:flex items-center justify-center gap-2 px-3 h-10 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-500 hover:bg-gray-200 dark:hover:bg-white/10 transition-all border border-gray-200 dark:border-white/10 active:scale-95 group/data"
          @click="isDataHubOpen = !isDataHubOpen"
        >
          <span class="w-1.5 h-1.5 rounded-full" :class="{
              'bg-emerald-500': storageStats.percent < 0.7,
              'bg-orange-500': storageStats.percent >= 0.7 && storageStats.percent < 0.9,
              'bg-red-500': storageStats.percent >= 0.9
          }"></span>
          <span class="text-[10px] font-mono tabular-nums opacity-70">{{ storageStats.usedMB }}MB</span>
          <i class="ri-shield-check-line text-lg group-hover/data:scale-110 transition-transform"></i>
        </button>

        <!-- Data Hub Dropdown -->
        <div 
          v-if="isDataHubOpen"
          class="absolute left-0 top-12 w-72 bg-white dark:bg-[#0f0f0f] rounded-[2rem] shadow-2xl border border-gray-100 dark:border-white/5 p-4 z-50 animate-in fade-in slide-in-from-top-2 duration-200 overflow-hidden"
        >
          <!-- Header -->
          <div class="flex justify-between items-center mb-4 px-2">
            <div class="flex items-center gap-2">
               <i class="ri-shield-user-line text-indigo-500 text-lg"></i>
               <span class="text-xs font-black dark:text-white">إدارة البيانات</span>
            </div>
            <div class="flex items-center gap-2">
               <span class="text-[10px] font-mono text-gray-400">{{ storageStats.usedMB }}MB</span>
               <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
            </div>
          </div>

          <!-- Actions List -->
          <div class="space-y-1">
            <button @click="handleBackup" :disabled="isExporting" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all w-full text-right active:scale-[0.98] disabled:opacity-40">
                <span class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-500 shadow-sm border border-indigo-100 dark:border-indigo-500/10">
                    <i v-if="!isExporting" class="ri-download-2-line text-lg"></i>
                    <i v-else class="ri-loader-4-line animate-spin text-lg"></i>
                </span>
                <span class="flex flex-col text-right">
                    <span class="text-xs font-black text-gray-800 dark:text-gray-200">تصدير (Backup)</span>
                    <span class="text-[9px] text-gray-500">حفظ نسخة كاملة للجهاز</span>
                </span>
            </button>

            <button @click="dataHubFileInput.click()" :disabled="isRestoring" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all w-full text-right active:scale-[0.98] disabled:opacity-40">
                <input type="file" ref="dataHubFileInput" @change="triggerRestore" accept=".entbak" class="hidden" />
                <span class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-500 shadow-sm border border-blue-100 dark:border-blue-500/10">
                    <i v-if="!isRestoring" class="ri-upload-2-line text-lg"></i>
                    <i v-else class="ri-loader-4-line animate-spin text-lg"></i>
                </span>
                <span class="flex flex-col text-right">
                    <span class="text-xs font-black text-gray-800 dark:text-gray-200">استعادة (Restore)</span>
                    <span class="text-[9px] text-gray-500">رفع ملف .entbak</span>
                </span>
            </button>

            <button @click="handleForceSync" :disabled="!isOnline || isSyncing" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all w-full text-right active:scale-[0.98] disabled:opacity-40">
                <span class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl bg-orange-50 dark:bg-orange-500/10 text-orange-500 shadow-sm border border-orange-100 dark:border-orange-500/10">
                    <i v-if="!isSyncing" class="ri-flashlight-line text-lg"></i>
                    <i v-else class="ri-loader-4-line animate-spin text-lg"></i>
                </span>
                <span class="flex flex-col text-right">
                    <span class="text-xs font-black text-gray-800 dark:text-gray-200">مزامنة (Sync)</span>
                    <span class="text-[9px] text-gray-500">تحديث فوري مع السيرفر</span>
                </span>
            </button>

            <button @click="handleDownloadAll('assigned')" :disabled="!isOnline || isDownloading" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all w-full text-right active:scale-[0.98] disabled:opacity-40">
                <span class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-500/10 text-violet-500 shadow-sm border border-violet-100 dark:border-violet-500/10">
                    <i v-if="!isDownloading" class="ri-briefcase-line text-lg"></i>
                    <i v-else class="ri-loader-4-line animate-spin text-lg"></i>
                </span>
                <span class="flex flex-col text-right">
                    <span class="text-xs font-black text-gray-800 dark:text-gray-200">مهامي (My Tasks)</span>
                    <span class="text-[9px] text-gray-500">تحميل ما تم إسناده لي فقط</span>
                </span>
            </button>

            <button @click="handleDownloadAll('full')" :disabled="!isOnline || isDownloading" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all w-full text-right active:scale-[0.98] disabled:opacity-40">
                <span class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 shadow-sm border border-emerald-100 dark:border-emerald-500/10">
                    <i v-if="!isDownloading" class="ri-download-cloud-2-line text-lg"></i>
                    <i v-else class="ri-loader-4-line animate-spin text-lg"></i>
                </span>
                <span class="flex flex-col text-right">
                    <span class="text-xs font-black text-gray-800 dark:text-gray-200">تحميل كامل (Full)</span>
                    <span class="text-[9px] text-gray-500">تنزيل كل البيانات للأوفلاين</span>
                </span>
            </button>

            <!-- PWA Install Button -->
            <button 
                v-if="isPwaInstallable"
                @click="installPwa" 
                class="flex items-center gap-3 p-2.5 rounded-2xl bg-indigo-500/10 hover:bg-indigo-500/20 transition-all w-full text-right active:scale-[0.98] border border-indigo-500/20 mt-2"
            >
                <span class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl bg-indigo-500 text-white shadow-lg shadow-indigo-500/20">
                    <i class="ri-app-store-line text-lg"></i>
                </span>
                <span class="flex flex-col text-right">
                    <span class="text-xs font-black text-indigo-600 dark:text-indigo-400">تثبيت التطبيق (Install)</span>
                    <span class="text-[9px] text-indigo-500/70">استخدم الكيان كبرنامج مستقل</span>
                </span>
            </button>
          </div>

          <!-- Quota Info -->
          <div class="mt-4 p-3 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-white/5">
            <div class="flex justify-between items-center mb-2">
                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">سعة التخزين محلياً</span>
                <span class="text-[9px] font-mono text-gray-500" dir="ltr">
                    {{ storageStats.usedMB }}MB / {{ storageStats.quotaMB }}MB
                </span>
            </div>
            <div class="w-full h-1.5 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                <div 
                    class="h-full transition-all duration-1000 shadow-lg" 
                    :class="{
                        'bg-indigo-500': storageStats.percent < 0.7,
                        'bg-orange-500': storageStats.percent >= 0.7 && storageStats.percent < 0.9,
                        'bg-red-500': storageStats.percent >= 0.9
                    }"
                    :style="{ width: `${storageStats.percent * 100}%` }"
                ></div>
            </div>
          </div>

          <div class="mt-4 pt-2 border-t border-gray-100 dark:border-white/5 text-[8px] font-mono text-center text-gray-400 tracking-widest">
            ENTITY SOVEREIGNTY PROTOCOL V1.0
          </div>
        </div>

        <!-- Backdrop -->
        <div 
          v-if="isDataHubOpen" 
          class="fixed inset-0 z-40" 
          @click="isDataHubOpen = false"
        />
      </div>

      <!-- Wi-Fi Bridge (Offline Tasks) -->
      <Link
        :href="route('assignments.index')"
        v-tooltip="'مهام الأوفلاين 📡'"
        class="hidden lg:flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-500 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all border border-indigo-100 dark:border-indigo-500/20 active:scale-95 group/wifi"
      >
        <i class="ri-wifi-line text-xl animate-pulse group-hover/wifi:scale-110 transition-transform"></i>
      </Link>

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
