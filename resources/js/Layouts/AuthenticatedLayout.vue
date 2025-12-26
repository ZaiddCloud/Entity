<script setup>
import { ref } from 'vue';
import { Link, Head } from '@inertiajs/vue3';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-[#050505] text-[#ededec]" dir="rtl">
        <Head :title="title" />
        
        <!-- Navigation -->
        <nav class="bg-white dark:bg-[#0a0a0a] border-b border-gray-200 dark:border-white/5 sticky top-0 z-40 backdrop-blur-md bg-opacity-80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center space-x-8 space-x-reverse">
                        <!-- Logo -->
                        <Link href="/" class="flex items-center space-x-2 space-x-reverse">
                            <div class="w-8 h-8 rounded-lg bg-linear-to-br from-blue-600 to-purple-600 flex items-center justify-center shadow-lg shadow-purple-500/20">
                                <span class="text-white font-bold text-sm">E</span>
                            </div>
                            <span class="font-bold text-lg tracking-tight dark:text-white">Entity</span>
                        </Link>

                        <!-- Navigation Links -->
                        <div class="hidden sm:flex items-center space-x-6 space-x-reverse">
                            <Link :href="route('dashboard')" :class="route().current('dashboard') ? 'text-purple-500 font-semibold' : 'text-gray-500 hover:text-purple-400 transition-colors'" class="text-sm">
                                لوحة التحكم
                            </Link>
                            <Link :href="route('books.index')" :class="route().current('books.*') ? 'text-purple-500 font-semibold' : 'text-gray-500 hover:text-purple-400 transition-colors'" class="text-sm">
                                الكتب
                            </Link>
                            <Link :href="route('videos.index')" :class="route().current('videos.*') ? 'text-purple-500 font-semibold' : 'text-gray-500 hover:text-purple-400 transition-colors'" class="text-sm">
                                الفيديو
                            </Link>
                            <Link :href="route('audios.index')" :class="route().current('audios.*') ? 'text-purple-500 font-semibold' : 'text-gray-500 hover:text-purple-400 transition-colors'" class="text-sm">
                                الصوتيات
                            </Link>
                            <Link :href="route('manuscripts.index')" :class="route().current('manuscripts.*') ? 'text-purple-500 font-semibold' : 'text-gray-500 hover:text-purple-400 transition-colors'" class="text-sm">
                                المخطوطات
                            </Link>
                        </div>
                    </div>

                    <!-- User Actions -->
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <div class="text-sm text-gray-400 ml-4 hidden md:block">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <Link :href="route('logout')" method="post" as="button" class="text-sm text-gray-500 hover:text-red-400 transition-colors">
                            تسجيل الخروج
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="bg-white dark:bg-[#0a0a0a] border-b border-gray-100 dark:border-white/5" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="animate-fade-in">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>

        <footer class="py-12 border-t border-gray-200 dark:border-white/5 mt-12 bg-white dark:bg-[#050505]">
            <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
                &copy; {{ new Date().getFullYear() }} Entity App. جميع الحقوق محفوظة.
            </div>
        </footer>
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
</style>

