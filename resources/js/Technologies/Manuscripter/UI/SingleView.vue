<script setup>
import { nextTick, watch } from 'vue'
import { useManuscriptStore } from '@/Technologies/Store/ManuscriptStore'

const store = useManuscriptStore()

// Scroll to shot functionality logic
// The parent normally handled scrollToShot for view switches.
// But since this is the component that holds the list, it should handle the scrolling.
// We can expose a method or watch store.shotNumber (but we don't want to scroll on every single click maybe? or maybe we do?)
// In spec: "Double Click -> Switches to list view & scrolls to shot."
// The store.viewMode switch mounts this component. On mount, we should check if we need to scroll.

const scrollToShot = (i) => {
    const el = document.getElementById(`shot-${i}`)
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    }
}

// Watch for shot number changes to scroll (optional UX choice, spec implies it works on view switch)
// The spec says "Scroll to specific shot in vertical reading mode". 
// Let's expose this capability or check on mount.

</script>

<template>
    <div class="w-full h-full overflow-y-auto custom-scrollbar">
        <div class="flex flex-col items-center gap-6 py-8 px-4 md:px-0 pt-20 pb-20">
            <div
                v-for="i in store.totalPages"
                :id="`shot-${i}`"
                :key="i"
                class="w-full max-w-3xl relative group shadow-2xl bg-black"
            >
                <img
                    :src="store.getPageUrl(i, store.displayedVersions[0])"
                    loading="lazy"
                    class="w-full h-auto object-contain transition-opacity opacity-90 group-hover:opacity-100"
                >
                
                <!-- Minimal Overlay -->
                <div class="absolute bottom-4 right-4 bg-black/40 backdrop-blur px-2 py-1 rounded text-white/60 text-xs font-mono opacity-0 group-hover:opacity-100 transition-opacity">
                    #{{ i }}
                </div>
            </div>
        </div>
    </div>
</template>
