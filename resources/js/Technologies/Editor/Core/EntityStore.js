import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useEntityStore = defineStore('entity', () => {
    const currentEntity = ref(null)
    const currentContentNode = ref(null)
    const documentTitle = computed(() => currentContentNode.value?.title || 'مستند جديد')

    const isSaving = ref(false)
    const lastSaved = ref(null)

    const hierarchy = ref([])
    const navigation = ref({ prev: null, next: null })

    const loadDocument = (entity, contentNode, hierarchyData = [], navigationData = {}) => {
        currentEntity.value = entity
        currentContentNode.value = contentNode
        hierarchy.value = hierarchyData
        navigation.value = navigationData
    }

    const setTitle = (title) => {
        if (currentContentNode.value) {
            currentContentNode.value.title = title
        }
    }

    const save = async (content, type) => {
        if (!currentEntity.value || isSaving.value) return false

        isSaving.value = true
        try {
            const slug = currentContentNode.value.slug
            const response = await axios.post(`/editor/${type}/${slug}/save`, {
                content: content
            })

            if (response.data.last_saved) {
                lastSaved.value = new Date(response.data.last_saved)
                return true
            }
            return false
        } catch (error) {
            console.error('Save failed', error)
            throw error
        } finally {
            isSaving.value = false
        }
    }

    return {
        currentEntity,
        currentContentNode,
        documentTitle,
        isSaving,
        lastSaved,
        hierarchy,
        navigation,
        loadDocument,
        setTitle,
        save
    }
})
