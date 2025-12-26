<template>
    <div class="min-h-screen flex items-center justify-center bg-[#0a0a0a] relative overflow-hidden font-sans text-[#EDEDEC]">
        <!-- Background Elements -->
        <div class="absolute inset-0 z-0">
            <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-purple-900/20 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-blue-900/20 rounded-full blur-[120px]"></div>
        </div>

        <Head title="Register" />

        <div class="relative z-10 w-full max-w-md p-8">
            <!-- Glass Card -->
            <div class="backdrop-blur-xl bg-white/5 border border-white/10 rounded-2xl shadow-2xl p-8 transform transition-all hover:border-white/20">
                
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent mb-2">
                        Create Account
                    </h1>
                    <p class="text-gray-400 text-sm">
                        Join Entity to manage your digital assets
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-300 ml-1">Full Name</label>
                        <div class="relative group">
                            <input 
                                id="name" 
                                type="text" 
                                v-model="form.name" 
                                required 
                                autofocus 
                                autocomplete="name"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-transparent transition-all group-hover:border-white/20"
                                placeholder="John Doe" 
                            />
                        </div>
                        <div v-if="form.errors.name" class="text-red-400 text-xs ml-1">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-300 ml-1">Email</label>
                        <div class="relative group">
                            <input 
                                id="email" 
                                type="email" 
                                v-model="form.email" 
                                required 
                                autocomplete="username"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-transparent transition-all group-hover:border-white/20"
                                placeholder="name@example.com" 
                            />
                        </div>
                        <div v-if="form.errors.email" class="text-red-400 text-xs ml-1">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-300 ml-1">Password</label>
                        <div class="relative group">
                            <input 
                                id="password" 
                                type="password" 
                                v-model="form.password" 
                                required 
                                autocomplete="new-password"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-transparent transition-all group-hover:border-white/20"
                                placeholder="••••••••" 
                            />
                        </div>
                        <div v-if="form.errors.password" class="text-red-400 text-xs ml-1">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-300 ml-1">Confirm Password</label>
                        <div class="relative group">
                            <input 
                                id="password_confirmation" 
                                type="password" 
                                v-model="form.password_confirmation" 
                                required 
                                autocomplete="new-password"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-transparent transition-all group-hover:border-white/20"
                                placeholder="••••••••" 
                            />
                        </div>
                        <div v-if="form.errors.password_confirmation" class="text-red-400 text-xs ml-1">
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-medium py-3 rounded-xl shadow-lg hover:shadow-purple-500/25 transform transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        <span v-if="!form.processing">Get Started</span>
                        <span v-else class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating Account...
                        </span>
                    </button>

                    <!-- Login Link -->
                    <div class="text-center text-sm text-gray-400">
                        Already have an account? 
                        <a :href="route('login')" class="text-purple-400 hover:text-purple-300 transition-colors">
                            Log in
                        </a>
                    </div>
                    
                </form>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6 text-sm text-gray-500">
                <p>&copy; {{ new Date().getFullYear() }} Entity App. All rights reserved.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<style scoped>
/* Custom animations if needed outside tailwind */
</style>
