<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import db from '@/Core/Database/dexieApp';

const recentEntities = ref([]);
const isLoading = ref(true);

onMounted(async () => {
    try {
        // Fetch top 5 recently updated entities from local storage
        recentEntities.value = await db.entities
            .orderBy('updated_at')
            .reverse()
            .limit(5)
            .toArray();
    } catch (error) {
        console.error('Failed to fetch local entities:', error);
    } finally {
        isLoading.value = false;
    }
});

const getIcon = (type) => {
    switch (type) {
        case 'book': return 'ri-book-3-line';
        case 'manuscript': return 'ri-file-text-line';
        case 'audio': return 'ri-mic-line';
        case 'video': return 'ri-video-line';
        default: return 'ri-database-2-line';
    }
};

const getUrl = (entity) => {
    return `/studio/${entity.type}/${entity.slug}`;
};
</script>

<template>
    <Head title="الملاذ الرقمي | Entity Sanctuary" />

    <div class="min-h-screen bg-[#020617] flex flex-col items-center justify-center p-6 text-center overflow-hidden relative selection:bg-blue-500/30">
        <!-- Ambient Background Effects (Cyber-Nautical) -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
            <div class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] bg-blue-500/10 blur-[120px] rounded-full"></div>
            <div class="absolute -bottom-[20%] -right-[10%] w-[60%] h-[60%] bg-orange-500/10 blur-[120px] rounded-full"></div>
        </div>

        <!-- Main Content Card -->
        <div class="z-10 max-w-2xl w-full">
            <!-- Animated Icon Wrapper -->
            <div class="relative group mb-8 inline-block">
                <div class="absolute inset-0 bg-blue-500/20 blur-xl rounded-full scale-150 animate-pulse"></div>
                <div class="relative w-24 h-24 bg-slate-900 border border-blue-500/30 rounded-3xl flex items-center justify-center shadow-2xl">
                    <i class="ri-shield-flash-line text-5xl text-blue-400"></i>
                </div>
            </div>

            <!-- Typography -->
            <h1 class="text-4xl font-bold text-white mb-4 tracking-tight">
                أنت في <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-orange-400">الملاذ الرقمي</span>
            </h1>
            
            <p class="text-slate-400 text-lg mb-8 leading-relaxed font-light">
                لقد انقطع الاتصال بالعالم الخارجي، لكن علمك لا يزال بين يديك. 
                بياناتك محفوظة بأمان تام في الذاكرة المحلية لجهازك.
            </p>

            <!-- Local Knowledge Section -->
            <div class="mb-10 text-right">
                <div class="flex items-center gap-2 mb-4 justify-end text-sm font-medium text-blue-400 uppercase tracking-wider">
                    <span>مواصلة العمل محلياً</span>
                    <i class="ri-history-line"></i>
                </div>
                
                <div v-if="isLoading" class="space-y-3">
                    <div v-for="i in 3" :key="i" class="h-16 bg-slate-900/50 rounded-2xl animate-pulse border border-slate-800"></div>
                </div>

                <div v-else-if="recentEntities.length > 0" class="grid gap-3">
                    <Link 
                        v-for="entity in recentEntities" 
                        :key="entity.id"
                        :href="getUrl(entity)"
                        class="flex items-center gap-4 p-4 bg-slate-900/40 hover:bg-slate-800/60 border border-blue-500/10 hover:border-blue-500/30 rounded-2xl transition-all duration-300 group"
                    >
                        <div class="text-xs text-slate-500 font-mono">
                            {{ new Date(entity.updated_at).toLocaleDateString('ar-EG') }}
                        </div>
                        <div class="flex-1 text-white font-medium truncate">{{ entity.title }}</div>
                        <div class="w-10 h-10 bg-blue-500/10 rounded-xl flex items-center justify-center text-blue-400 group-hover:bg-blue-500 group-hover:text-slate-950 transition-colors">
                            <i :class="getIcon(entity.type)" class="text-xl"></i>
                        </div>
                    </Link>
                </div>

                <div v-else class="p-8 bg-slate-900/20 border border-dashed border-slate-800 rounded-3xl text-slate-500 text-center">
                    لا يوجد بيانات محفوظة محلياً بعد.
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-4">
                <Link 
                    href="/dashboard" 
                    class="py-4 bg-blue-500 hover:bg-blue-400 text-slate-950 font-bold rounded-2xl transition-all duration-300 shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2 group"
                >
                    <i class="ri-dashboard-3-line text-xl transition-transform group-hover:scale-110"></i>
                    لوحة التحكم
                </Link>
                
                <button 
                    @click="window.location.reload()" 
                    class="py-4 bg-slate-900 hover:bg-slate-800 text-orange-400 border border-orange-500/30 font-semibold rounded-2xl transition-all duration-300 flex items-center justify-center gap-2"
                >
                    <i class="ri-refresh-line"></i>
                    إعادة الاتصال
                </button>
            </div>

            <!-- Footer Metrics -->
            <div class="mt-12 pt-8 border-t border-slate-800 flex justify-between items-center text-[10px] text-slate-500 uppercase tracking-[2px]">
                <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 bg-orange-500 rounded-full animate-ping"></span>
                    Sanctum Protocol v2.0
                </div>
                <div class="font-mono text-blue-400">CONNECTED TO LOCAL TRUTH</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes pulse-slow {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.1; transform: scale(1.1); }
}
</style>

<style scoped>
@keyframes pulse-slow {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.1; transform: scale(1.1); }
}
</style>
