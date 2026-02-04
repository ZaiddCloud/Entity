import { db } from '@/Core/Database/dexieApp';

/**
 * Predictive Caching Strategy
 * Pre-fetches entities based on current context (e.g., next page in a book).
 */

/**
 * Predict likely next entities based on the current one.
 * @param {Object} current 
 * @returns {Promise<Array>} List of entity IDs/types to prefetch
 */
export async function predictNextMoves(current) {
    if (!current || !current.id) return [];

    const predictions = [];

    // 1. Manuscript/Book sequence prediction
    if (current.navigation && current.navigation.next) {
        const nextId = current.navigation.next.id || current.navigation.next.slug;
        const type = current.type || current.editorMode; // Fallback

        if (nextId && type) {
            predictions.push({ id: nextId, type });
        }
    }

    // 2. Fragmented content prediction (Manuscript Pages)
    if (current.parent_id && typeof current.order === 'number') {
        const nextOrder = current.order + 1;

        // Try to find if we already have the next one cached
        const nextSibling = await db.entities
            .where({
                parent_id: current.parent_id,
                segment_order: nextOrder
            })
            .first();

        if (!nextSibling) {
            console.log(`🔮 Predicting next content in sequence: order ${nextOrder}`);
            // Note: Without the specific ID, we can't fetch. 
            // We rely on the navigation data provided by the backend usually.
        }
    }

    return predictions;
}

/**
 * Prefetch a list of entities in the background.
 */
export function prefetchEntities(entities) {
    if (!entities || entities.length === 0) return;

    const performPrefetch = async () => {
        // Dynamic import to avoid circular dependency
        const { useResilientSync } = await import('@/Core/Sync/useResilientSync');
        const { fetchEntity } = useResilientSync();

        for (const entity of entities) {
            try {
                console.log(`📡 Pre-fetching entity: ${entity.id} (${entity.type})`);
                await fetchEntity(entity.type, entity.id);
            } catch (e) {
                console.warn(`Failed to prefetch ${entity.id}`, e);
            }
        }
    };

    if (window.requestIdleCallback) {
        window.requestIdleCallback(() => performPrefetch(), { timeout: 5000 });
    } else {
        setTimeout(performPrefetch, 2000);
    }
}
