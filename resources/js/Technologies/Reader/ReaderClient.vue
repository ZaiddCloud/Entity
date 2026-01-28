<script setup>
import { ref, onMounted, onUnmounted, provide, computed, watch } from 'vue';
import { useReaderStore } from './Core/ReaderStore';
import { useTheme } from './Core/useTheme';
import ContentView from './UI/ContentView.vue';
import TableOfContents from './UI/TableOfContents.vue';
import ReadingControls from './UI/ReadingControls.vue';
import ProgressBar from './UI/ProgressBar.vue';
import MediaSync from './UI/MediaSync.vue';
import SearchPanel from './UI/SearchPanel.vue';
import PlayerClient from '@/Technologies/Player/PlayerClient.vue';

const props = defineProps({
    type: String,
    entity: Object,
    content: Object,
    html_content: String,
    isFullView: Boolean,
    activeChildId: String,
    activeSlug: String, // Keep for legacy if needed, but primarily use activeChildId
    hierarchy: Array,
    readingPosition: Object,
    title: String,
    siblings_content: {
        type: Array,
        default: () => []
    },
});

const store = useReaderStore();
const { currentThemeClasses } = useTheme();
const isSettingsOpen = ref(false);
const currentTime = ref(0);
const playerRef = ref(null);

// Initialize Store
onMounted(() => {
    store.init(props);
    
    // Restore position if available
    if (props.readingPosition) {
        // Implementation for scroll restoration will be in ContentView
    }

    // Add keyboard listeners
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const handleKeydown = (e) => {
    if (e.key === 'ArrowRight' && store.nextNode) {
        store.navigate(store.nextNode._id || store.nextNode.id);
    } else if (e.key === 'ArrowLeft' && store.prevNode) {
        store.navigate(store.prevNode._id || store.prevNode.id);
    } else if (e.key === 'f' || e.key === 'F') {
        store.toggleFullscreen();
    } else if (e.key === 't' || e.key === 'T') {
        store.toggleToc();
    } else if (e.key === '/') {
        e.preventDefault();
        store.toggleSearch();
    } else if (e.key === ',' && (e.metaKey || e.ctrlKey)) {
        isSettingsOpen.value = !isSettingsOpen.value;
    }
};

const handleSearchResult = (result) => {
    if ((result.id || result._id) !== props.activeChildId) {
        store.navigate(result.id || result._id);
    }
    
    if (result.timestamp !== null) {
        handleSeek(result.timestamp);
    }
    
    // store.isSearchOpen = false; // Kept open per user request
};

// Provide state to children
provide('readerStore', store);
provide('themeClasses', currentThemeClasses);

const handleTimeUpdate = (time) => {
    currentTime.value = time;
};

const handleSeek = (time) => {
    if (playerRef.value) {
        playerRef.value.seek(time);
    }
};

// Sync player when navigating via TOC/URL
watch(() => props.activeSlug, (newSlug) => {
    if (!newSlug || props.type === 'book' || props.type === 'manuscript') return;
    
    const node = props.hierarchy.find(n => n.slug === newSlug);
    if (node && node.start_time !== undefined) {
        // Only seek if we're not already within this segment's range 
        // (to avoid loops or jumping while naturally playing)
        const isAlreadyInSegment = currentTime.value >= node.start_time && currentTime.value <= (node.end_time || node.start_time + 1);
        
        if (!isAlreadyInSegment) {
            handleSeek(node.start_time);
        }
    }
}, { immediate: true });

</script>

<template>
    <div 
        :class="['h-screen flex flex-col transition-colors duration-300', currentThemeClasses.bg, currentThemeClasses.text]"
        :dir="'rtl'"
    >
        <!-- Header HUD -->
        <header :class="['sticky top-0 z-30 border-b px-4 h-14 flex items-center justify-between backdrop-blur-md bg-opacity-80', currentThemeClasses.bg, currentThemeClasses.border]">
            <div class="flex items-center gap-4">
                <button @click="$inertia.get(route(`${props.type}s.show`, props.entity.slug))" class="p-2 hover:bg-black/5 rounded-full transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </button>
                <div class="overflow-hidden">
                    <h1 class="font-bold text-lg truncate max-w-[200px] sm:max-w-md">{{ entity.title }}</h1>
                    <div class="flex items-center gap-2">
                        <span v-if="props.isFullView" class="text-[10px] bg-amber-500/10 text-amber-600 px-1.5 py-0.5 rounded border border-amber-500/20 font-bold uppercase">كامل المحتوى</span>
                        <p class="text-xs opacity-60 truncate">{{ props.isFullView ? 'استعراض شامل' : store.currentNode?.title }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Search Toggle -->
                <button @click="store.toggleSearch" :class="['p-2 rounded-lg transition-colors', store.isSearchOpen ? 'bg-blue-500 text-white' : 'hover:bg-black/5']">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>

                <!-- TOC Toggle -->
                <button @click="store.toggleToc" :class="['p-2 rounded-lg transition-colors', store.isTocOpen ? 'bg-blue-500 text-white' : 'hover:bg-black/5']">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                
                <!-- Settings Button -->
                <button @click="isSettingsOpen = true" class="p-2 hover:bg-black/5 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
                <!-- Toggle Media/Focus Mode -->
                <button 
                    v-if="['manuscript', 'audio', 'video'].includes(props.type)"
                    @click="store.toggleMedia" 
                    :class="['p-2 rounded-lg transition-colors', !store.isMediaVisible ? 'bg-blue-500 text-white' : 'hover:bg-black/5']"
                    title="Focus Mode"
                >
                    <svg v-if="!store.isMediaVisible" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>

            <!-- Progress Bar HUD -->
            <div class="absolute bottom-0 left-0 w-full translate-y-1/2 px-4 pointer-events-auto">
                <ProgressBar />
            </div>
        </header>

        <!-- Main Layout Section -->
        <div class="flex-1 flex overflow-hidden relative">
            <!-- Sidebar TOC & Search (Now part of flex layout, not absolute) -->
            <aside 
                v-if="store.isTocOpen" 
                class="w-80 flex-shrink-0 z-20 h-full transition-all duration-300"
            >
               <TableOfContents @close="store.toggleToc" />
            </aside>

            <aside 
                v-else-if="store.isSearchOpen" 
                class="w-80 flex-shrink-0 z-20 h-full transition-all duration-300"
            >
               <SearchPanel @close="store.toggleSearch" @select="handleSearchResult" />
            </aside>

            <!-- Main Content Area (Independent scroll) -->
            <main class="flex-1 overflow-y-auto relative custom-scrollbar" id="reader-viewport">
                <!-- Video/Audio Transcript Sync -->
                <MediaSync 
                    v-if="['audio', 'video'].includes(props.type)"
                    :current-time="currentTime"
                    :hierarchy="props.hierarchy"
                    :active-slug="props.activeSlug"
                    @seek="handleSeek"
                />

                <!-- Standard Content (Books/Manuscripts) -->
                <ContentView 
                    v-else
                    :content="props.content"
                    :html="props.html_content"
                    :font-size="store.fontSize"
                />

                <!-- Integrated Player (for A/V) -->
                <div v-show="store.isMediaVisible">
                    <!-- Manuscript Vertical Scroll -->
                    <div v-if="props.type === 'manuscript'" class="flex flex-col gap-12 pb-20">
                        <div 
                            v-if="props.siblings_content && props.siblings_content.length"
                            v-for="(page, index) in props.siblings_content" 
                            :key="page.id"
                            :id="`node-${page.slug}`"
                            class="min-h-screen border-b border-gray-100 dark:border-white/5 last:border-0"
                        >
                            <ContentView 
                                :content="page.content"
                                :html="page.html_content"
                                :font-size="store.fontSize"
                            />
                        </div>
                        <!-- Fallback for single page if no siblings provided -->
                        <ContentView 
                            v-else
                            :content="props.content"
                            :html="props.html_content"
                            :font-size="store.fontSize"
                        />
                    </div>

                    <PlayerClient 
                        v-else-if="['audio', 'video'].includes(props.type)"
                        ref="playerRef"
                        :media="props.entity"
                        :active-slug="props.activeSlug"
                        :type="props.type"
                        :is-embedded="true"
                        @timeupdate="handleTimeUpdate"
                    />
                </div>
            </main>
        </div>

        <!-- Reading Controls Modal -->
        <ReadingControls 
            :is-open="isSettingsOpen" 
            @close="isSettingsOpen = false" 
        />

        <!-- Footer Navigation -->
        <footer :class="['h-16 border-t flex items-center justify-between px-6 shrink-0', currentThemeClasses.bg, currentThemeClasses.border]">
            <button 
                @click="store.prevNode && store.navigate(store.prevNode._id || store.prevNode.id)"
                :disabled="!store.prevNode"
                class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-black/5 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
                <span>السابق</span>
            </button>

            <div class="hidden sm:flex items-center gap-4 text-sm opacity-60">
                <span>{{ store.activeNodeIndex + 1 }} من {{ store.hierarchy.length }}</span>
            </div>

            <button 
                @click="store.nextNode && store.navigate(store.nextNode._id || store.nextNode.id)"
                :disabled="!store.nextNode"
                class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-black/5 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
            >
                <span>التالي</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </footer>
    </div>
</template>

<style scoped>
.slide-rtl-enter-active,
.slide-rtl-leave-active {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-rtl-enter-from,
.slide-rtl-leave-to {
  transform: translateX(100%);
}

.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 4px;
}

.theme-dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
}
</style>
