import { useEditorStore } from '@/Technologies/Store/EditorStore'
import { router } from '@inertiajs/vue3'

export function useEditorNavigation() {
    const store = useEditorStore()

    const goToPrev = () => {
        if (store.navigation.prev) {
            // Assuming the navigation object contains urls or slugs
            // If it's a URL:
            // router.visit(store.navigation.prev)
            // Or if we need to construct it:
            router.visit(store.navigation.prev)
        }
    }

    const goToNext = () => {
        if (store.navigation.next) {
            router.visit(store.navigation.next)
        }
    }

    return {
        goToPrev,
        goToNext
    }
}
