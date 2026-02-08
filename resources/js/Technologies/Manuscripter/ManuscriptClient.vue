<script setup>
import { onMounted, watch, onUnmounted } from 'vue'
import { useManuscriptStore } from '@/Technologies/Store/ManuscriptStore'
import SoftLockWarning from '@/Technologies/Common/UI/SoftLockWarning.vue'

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
    activeChildId: { type: String, default: null }
})

const emit = defineEmits(['navigate', 'navigate-full'])
const store = useManuscriptStore()

// Initialize Store
onMounted(async () => {
    store.setResource(props.manuscript, props.siblings, props.activeChildId)
    await store.initSync()
})

// Watch props to update store if parent changes them
watch(() => props.activeChildId, (newId) => {
    if (newId) {
        // Find shot number for ID
        const version = store.allVersions[0]
        if (version && version.pages) {
            const index = version.pages.findIndex(p => (p._id || p.id) === newId)
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
        const pageId = page?._id || page?.id
        
        // Prevent auto-navigation on initial load if in Full View (null activeChildId)
        // We assume Shot 1 is the default visual state and shouldn't trigger a route change
        if (!props.activeChildId && newShot === 1) {
            return
        }

        if (pageId && pageId !== props.activeChildId) {
            emit('navigate', pageId)
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

            <!-- Soft Lock Warning -->
            <SoftLockWarning 
                v-if="store.activeSlug"
                :section-id="store.activeSlug"
                :soft-lock="store._softLock"
                class="fixed bottom-20 left-1/2 transform -translate-x-1/2 z-50 pointer-events-none"
            />
        </main>

        <!-- Overlays -->
        <ManuscriptHeader />
        <ManuscriptFooter />

    </div>
</template>
