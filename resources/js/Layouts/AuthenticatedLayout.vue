<script setup>
import { ref, onMounted } from 'vue';
import { Link, Head, usePage, router } from '@inertiajs/vue3';

defineProps({
    title: String,
});

const isSidebarOpen = ref(true);
const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const navigation = [
    {
        name: 'الرئيسية',
        items: [
            { name: 'لوحة التحكم', route: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
        ]
    },
    {
        name: 'المكتبة الرقمية',
        items: [
            { name: 'الكتب', route: 'books.index', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
            { name: 'المخطوطات', route: 'manuscripts.index', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            { name: 'الصوتيات', route: 'audios.index', icon: 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3' },
            { name: 'المرئيات', route: 'videos.index', icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z' },
        ]
    },
    {
        name: 'الأشخاص والجهات',
        items: [
            { name: 'المؤلفون', route: 'authors.index', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
            { name: 'المساهمون', route: 'bookers.index', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
            { name: 'دور النشر', route: 'publishers.index', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
        ]
    },
    {
        name: 'البيانات والتنظيم',
        items: [
            { name: 'التصنيفات', route: 'categories.index', icon: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2zm5-3a2 2 0 100 4 2 2 0 000-4z' },
            { name: 'الأوسمة', route: 'tags.index', icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' },
            { name: 'المواضيع', route: 'topics.index', icon: 'M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2' },
            { name: 'اللغات', route: 'languages.index', icon: 'M3 5h12M9 3v2m1.048 9.531a11.115 11.115 0 01-1.048-3.531m6.241 3a9.904 9.904 0 01-6.241 3m0 0a9.904 9.904 0 01-6.241-3m6.241 3v2m0-6V7m0 0H7m3 0h3' },
            { name: 'الرفوف', route: 'shelves.index', icon: 'M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2zm0 0h16M9 17v-4m3 4v-4m3 4v-4' },
        ]
    },
    {
        name: 'التفاعل والملحوظات',
        items: [
            { name: 'التعليقات', route: 'comments.index', icon: 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z' },
            { name: 'الملاحظات', route: 'notes.index', icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' },
        ]
    },
    {
        name: 'النظام',
        items: [
            { name: 'المجموعات', route: 'collections.index', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
            { name: 'السلاسل', route: 'series.index', icon: 'M4 6h16M4 10h16M4 14h16M4 18h16' },
            { name: 'النشاطات', route: 'activities.index', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
            { name: 'المهملات', route: 'deletions.index', icon: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16' },
        ]
    }
];

const navbarSearch = ref('');
const performSearch = () => {
    if (navbarSearch.value.trim()) {
        router.get(route('search'), { q: navbarSearch.value });
    }
};

const isMobileMenuOpen = ref(false);
const checkActive = (routeName) => {
    return route().current(routeName) || (routeName.includes('.index') && route().current(routeName.replace('.index', '.*')));
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-[#050505] text-gray-900 dark:text-[#ededec]" dir="rtl">
        <Head :title="title" />
        
        <!-- Sidebar -->
        <aside 
            :class="[
                'fixed top-0 right-0 z-50 h-screen transition-all duration-300 border-l border-gray-200 dark:border-white/5 bg-white dark:bg-[#0a0a0a]',
                isSidebarOpen ? 'w-64' : 'w-20'
            ]"
        >
            <!-- Sidebar Header -->
            <div class="h-16 flex items-center px-6 border-b border-gray-100 dark:border-white/5">
                <Link href="/" class="flex items-center gap-3 overflow-hidden">
                    <div class="w-8 h-8 rounded-xl bg-linear-to-br from-indigo-600 via-purple-600 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/20 shrink-0">
                        <span class="text-white font-black text-sm">E</span>
                    </div>
                    <span v-show="isSidebarOpen" class="font-black text-xl tracking-tight dark:text-white whitespace-nowrap">Entity</span>
                </Link>
            </div>

            <!-- Sidebar Content -->
            <div class="overflow-y-auto h-[calc(100vh-4rem)] custom-scrollbar py-4 px-3">
                <div v-for="group in navigation" :key="group.name" class="mb-6">
                    <h5 v-if="isSidebarOpen" class="px-3 mb-2 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                        {{ group.name }}
                    </h5>
                    <div class="space-y-1">
                        <Link 
                            v-for="item in group.items" 
                            :key="item.name"
                            :href="route(item.route)"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all group relative',
                                checkActive(item.route) 
                                    ? 'bg-indigo-50 dark:bg-indigo-900/10 text-indigo-600 dark:text-indigo-400' 
                                    : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                            v-tooltip="!isSidebarOpen ? item.name : ''"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                            </svg>
                            <span v-show="isSidebarOpen" class="font-bold text-sm">{{ item.name }}</span>
                            
                            <!-- Active Indicator -->
                            <div v-if="checkActive(item.route)" class="absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-indigo-600 dark:bg-indigo-400 rounded-l-full"></div>
                        </Link>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div :class="['transition-all duration-300', isSidebarOpen ? 'mr-64' : 'mr-20']">
            
            <!-- Navbar -->
            <nav class="h-16 sticky top-0 z-40 bg-white/80 dark:bg-[#0a0a0a]/80 backdrop-blur-xl border-b border-gray-100 dark:border-white/5 flex items-center justify-between px-8">
                <div class="flex items-center gap-6">
                    <button @click="toggleSidebar" class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                    </button>
                    
                    <div class="flex items-center gap-2 text-sm text-gray-400 font-medium">
                        <Link href="/dashboard" class="hover:text-indigo-500 transition-colors">الرئيسية</Link>
                        <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        <span class="text-gray-900 dark:text-white font-bold">{{ title }}</span>
                    </div>
                </div>

    <div class="flex items-center gap-4">
        <!-- Search Bubble -->
        <div class="hidden md:flex items-center bg-gray-100 dark:bg-gray-800 rounded-xl px-4 py-2 border border-transparent focus-within:border-indigo-500/50 focus-within:bg-white dark:focus-within:bg-black transition-all">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input 
                v-model="navbarSearch"
                @keyup.enter="performSearch"
                type="text" 
                id="navbar-search"
                name="q"
                placeholder="بحث سريع..." 
                class="bg-transparent border-none focus:ring-0 text-xs w-48 text-gray-500"
            >
        </div>

        <!-- User Menu -->
                    <div class="flex items-center gap-4 border-r border-gray-100 dark:border-white/5 pr-4 mr-2">
                        <div class="flex flex-col items-start leading-none hidden sm:flex">
                            <span class="text-sm font-black dark:text-white">{{ $page.props.auth.user.name }}</span>
                            <span class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">مسؤول النظام</span>
                        </div>
                        <div class="relative group">
                            <button class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center p-0.5 border border-gray-200 dark:border-white/10 overflow-hidden">
                                <div class="w-full h-full rounded-lg bg-linear-to-tr from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                                    <span class="text-xs font-black">{{ $page.props.auth.user.name.charAt(0) }}</span>
                                </div>
                            </button>
                            
                            <!-- Simple Dropdown Placeholder -->
                            <div class="absolute left-0 top-12 w-48 bg-white dark:bg-[#0f0f0f] rounded-2xl shadow-2xl border border-gray-100 dark:border-white/5 py-2 opacity-0 translate-y-2 pointer-events-none group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-200">
                                <Link :href="route('logout')" method="post" as="button" class="w-full text-right px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors">تسجيل الخروج</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Header Slot Implementation -->
            <header v-if="$slots.header" class="px-8 mt-8">
                <div class="bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 rounded-[2rem] p-6 shadow-sm">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Implementation Section -->
            <main class="p-8 animate-fade-in custom-main">
                <slot />
            </main>
            
            <!-- Footer -->
            <footer class="py-12 border-t border-gray-100 dark:border-white/5 mt-12 px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-gray-400 text-xs font-medium">
                    <p>&copy; {{ new Date().getFullYear() }} Entity App. جميع الحقوق محفوظة.</p>
                    <div class="flex gap-6">
                        <a href="#" class="hover:text-indigo-500 transition-colors">الدعم الفني</a>
                        <a href="#" class="hover:text-indigo-500 transition-colors">سياسة الخصوصية</a>
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
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.2);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.4);
}

.custom-main {
    min-height: calc(100vh - 16rem);
}
</style>

