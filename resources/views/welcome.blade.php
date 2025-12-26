<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Entity') }}</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/js/app.js'])
    </head>
    <body class="antialiased bg-[#050505] text-[#ededec] font-sans selection:bg-purple-500 selection:text-white overflow-x-hidden">
        
        <!-- Navigation -->
        <nav class="fixed top-0 w-full z-50 border-b border-white/5 bg-[#050505]/80 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-linear-to-br from-blue-600 to-purple-600 flex items-center justify-center shadow-lg shadow-purple-500/20">
                            <span class="text-white font-bold text-xl">E</span>
                        </div>
                        <span class="font-bold text-2xl tracking-tight">Entity</span>
                    </div>

                    <!-- Auth Links -->
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ url('/books') }}" class="px-4 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-colors">Dashboard</a>
                                @else
                                    <a href="{{ route('login') }}" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-white text-black hover:bg-gray-200 transition-all shadow-lg hover:shadow-white/10">Log in</a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="ml-4 px-6 py-2.5 rounded-xl text-sm font-semibold border border-white/20 hover:bg-white/10 transition-all text-white">Sign up</a>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative min-h-screen flex items-center justify-center pt-20">
            <!-- Background Gradients -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-[20%] -left-[10%] w-[70vw] h-[70vw] bg-purple-900/10 rounded-full blur-[120px] mix-blend-screen opacity-50 animate-blob"></div>
                <div class="absolute top-[20%] -right-[10%] w-[70vw] h-[70vw] bg-blue-900/10 rounded-full blur-[120px] mix-blend-screen opacity-50 animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-[20%] left-[20%] w-[70vw] h-[70vw] bg-indigo-900/10 rounded-full blur-[120px] mix-blend-screen opacity-50 animate-blob animation-delay-4000"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center px-4 py-2 rounded-full border border-white/10 bg-white/5 backdrop-blur-sm mb-8 animate-fade-in-up">
                    <span class="flex h-2 w-2 rounded-full bg-green-400 mr-2"></span>
                    <span class="text-xs font-medium tracking-wide uppercase text-gray-300">v1.0 is now live</span>
                </div>
                
                <h1 class="text-5xl md:text-7xl font-bold tracking-tight mb-8 animate-fade-in-up animation-delay-100">
                    <span class="block mb-2">Manage your Digital</span>
                    <span class="bg-linear-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">Creative Assets</span>
                </h1>
                
                <p class="mt-6 max-w-2xl mx-auto text-xl text-gray-400 mb-12 animate-fade-in-up animation-delay-200 leading-relaxed">
                    A powerful, unified platform for Books, Videos, Audio, and Manuscripts. 
                    Organize, track, and showcase your content with an interface designed for the future.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 animate-fade-in-up animation-delay-300">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-4 bg-linear-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 rounded-xl font-bold text-lg shadow-lg shadow-purple-900/40 transition-all hover:scale-105">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-linear-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 rounded-xl font-bold text-lg shadow-lg shadow-purple-900/40 transition-all hover:scale-105">
                            Get Started
                        </a>
                        <a href="#features" class="w-full sm:w-auto px-8 py-4 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl font-semibold text-lg backdrop-blur-sm transition-all text-white">
                            Learn more
                        </a>
                    @endauth
                </div>

                <!-- Floating UI Mockup/Graphic -->
                <div class="mt-20 relative animate-fade-in-up animation-delay-500">
                    <div class="absolute -inset-1 bg-linear-to-r from-blue-500 to-purple-600 rounded-2xl blur opacity-20"></div>
                    <div class="relative bg-[#0F0F11] border border-white/10 rounded-2xl shadow-2xl overflow-hidden aspect-video mx-auto max-w-5xl group">
                         <!-- Simplified Dashboard UI Mockup using CSS -->
                        <div class="absolute top-0 left-0 w-64 h-full border-r border-white/5 bg-[#0a0a0a] hidden md:block p-6 space-y-4">
                            <div class="h-8 w-32 bg-white/10 rounded mb-8"></div>
                            <div class="h-4 w-full bg-white/5 rounded"></div>
                            <div class="h-4 w-full bg-white/5 rounded"></div>
                            <div class="h-4 w-full bg-white/5 rounded"></div>
                        </div>
                        <div class="md:ml-64 p-8">
                            <div class="flex justify-between items-center mb-8">
                                <div class="h-8 w-48 bg-white/10 rounded"></div>
                                <div class="flex gap-4">
                                    <div class="h-8 w-8 bg-white/10 rounded-full"></div>
                                    <div class="h-8 w-8 bg-white/10 rounded-full"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-6">
                                <div class="aspect-3/4 bg-white/5 rounded-xl border border-white/5 hover:border-purple-500/50 transition-colors"></div>
                                <div class="aspect-3/4 bg-white/5 rounded-xl border border-white/5 hover:border-purple-500/50 transition-colors"></div>
                                <div class="aspect-3/4 bg-white/5 rounded-xl border border-white/5 hover:border-purple-500/50 transition-colors"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Grid -->
        <div id="features" class="py-24 relative bg-black/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="p-8 rounded-2xl bg-white/5 border border-white/5 hover:border-blue-500/30 transition-all hover:-translate-y-1">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center mb-6 text-blue-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Multi-Format Support</h3>
                        <p class="text-gray-400 leading-relaxed">
                            Seamlessly handle Books, Videos, Audio tracks, and Manuscripts in one centralized hub.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-8 rounded-2xl bg-white/5 border border-white/5 hover:border-purple-500/30 transition-all hover:-translate-y-1">
                        <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center mb-6 text-purple-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Native Media Handling</h3>
                        <p class="text-gray-400 leading-relaxed">
                            Built-in file upload, storage management, and streaming support for all your media assets.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-8 rounded-2xl bg-white/5 border border-white/5 hover:border-pink-500/30 transition-all hover:-translate-y-1">
                        <div class="w-12 h-12 bg-pink-500/20 rounded-lg flex items-center justify-center mb-6 text-pink-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Secure & Scalable</h3>
                        <p class="text-gray-400 leading-relaxed">
                            Enterprise-grade security with robust authentication and access control for your digital library.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <footer class="border-t border-white/5 py-12 bg-black">
            <div class="max-w-7xl mx-auto px-4 text-center text-gray-600">
                <p>&copy; {{ date('Y') }} Entity Application. Crafted with precision.</p>
            </div>
        </footer>

        <style>
            .animate-blob {
                animation: blob 7s infinite;
            }
            .animation-delay-2000 {
                animation-delay: 2s;
            }
            .animation-delay-4000 {
                animation-delay: 4s;
            }
            @keyframes blob {
                0% { transform: translate(0px, 0px) scale(1); }
                33% { transform: translate(30px, -50px) scale(1.1); }
                66% { transform: translate(-20px, 20px) scale(0.9); }
                100% { transform: translate(0px, 0px) scale(1); }
            }
            .animate-fade-in-up {
                animation: fadeInUp 0.8s ease-out forwards;
                opacity: 0;
                transform: translateY(20px);
            }
            .animation-delay-100 { animation-delay: 0.1s; }
            .animation-delay-200 { animation-delay: 0.2s; }
            .animation-delay-300 { animation-delay: 0.3s; }
            .animation-delay-500 { animation-delay: 0.5s; }
            
            @keyframes fadeInUp {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    </body>
</html>
