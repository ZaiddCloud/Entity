<script setup>
import { onMounted, watch, onUnmounted } from 'vue'
import { useManuscriptStore } from '@/Technologies/Store/ManuscriptStore'

// Modular Components
import ManuscriptHeader from './UI/ManuscriptHeader.vue'
import ManuscriptFooter from './UI/ManuscriptFooter.vue'
import DefaultView from './UI/DefaultView.vue'
import GridView from './UI/GridView.vue'
import SingleView from './UI/SingleView.vue'
import CompareView from './UI/CompareView.vue'

const props = defineProps({
    manuscript: Object,
    siblings: { type: Array, default: () => [] },
    activeSlug: { type: String, default: null }
})

const emit = defineEmits(['navigate'])
const store = useManuscriptStore()

// Initialize Store
onMounted(() => {
    store.setResource(props.manuscript, props.siblings, props.activeSlug)
    // Optional: Attach resizing logic for responsiveness if needed
    // window.addEventListener('resize', ...)
})

// Watch props to update store if parent changes them
watch(() => props.activeSlug, (newSlug) => {
    if (newSlug) {
        // Find shot number for slug
        const version = store.allVersions[0]
        if (version && version.pages) {
            const index = version.pages.findIndex(p => p.slug === newSlug)
            if (index !== -1) {
                store.shotNumber = index + 1
            }
        }
    }
})

// Watch Store Shot Changes -> Emit Navigation
watch(() => store.shotNumber, (newShot) => {
    // Logic to emit navigate
    const version = store.allVersions[0]
    if (version && version.pages) {
        const page = version.pages[newShot - 1]
        if (page && page.slug !== props.activeSlug) {
            emit('navigate', page.slug)
        }
    }
})

// Scroll scrollToShot logic when viewMode switches to list
watch(() => store.viewMode, (newMode) => {
    if (newMode === 'list') {
        setTimeout(() => {
            const el = document.getElementById(`shot-${store.shotNumber}`)
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
        }, 100)
    }
})

</script>

<template>
    <div class="w-full h-full bg-stone-900 relative overflow-hidden font-ui text-gray-800 flex flex-col" dir="rtl">
        
        <!-- The Content Layer -->
        <main class="flex-1 relative z-0 bg-[#0c0c0c] overflow-hidden">
            
            <DefaultView v-if="store.viewMode === 'default'" />
            <GridView v-else-if="store.viewMode === 'grid'" />
            
            <!-- List View (Vertical) or Compare View (Horizontal) -->
            <div v-else-if="store.viewMode === 'list'" class="w-full h-full">
                <CompareView v-if="store.isCompareMode" />
                <SingleView v-else />
            </div>

        </main>

        <!-- Overlays -->
        <ManuscriptHeader />
        <ManuscriptFooter />

    </div>
</template>
