import { db } from '@/Core/Database/dexieApp';

/**
 * Quota Manager
 * Handles storage limits and LRU (Least Recently Used) eviction.
 */

// Thresholds
const WARNING_THRESHOLD = 0.7; // 70% used
const CRITICAL_THRESHOLD = 0.8; // 80% used triggers eviction

/**
 * Check current storage usage
 * @returns {Promise<{usage: number, quota: number, percent: number}>}
 */
export async function checkQuota() {
    if (!navigator.storage || !navigator.storage.estimate) {
        return { usage: 0, quota: 0, percent: 0 };
    }

    const estimate = await navigator.storage.estimate();
    const usage = estimate.usage || 0;
    const quota = estimate.quota || 1; // Prevent division by zero
    const percent = usage / quota;

    return {
        usage, // Bytes
        quota, // Bytes
        percent, // 0.0 to 1.0
        usedMB: (usage / 1024 / 1024).toFixed(2),
        quotaMB: (quota / 1024 / 1024).toFixed(2)
    };
}

/**
 * Register access to an entity (for LRU tracking)
 * @param {string|number} entityId
 * @param {number} userId
 */
export async function registerAccess(entityId, userId) {
    if (!entityId || !userId) return;

    try {
        await db.ephemeral_state.put({
            user_id: userId,
            entity_id: entityId,
            last_accessed: Date.now()
        });
    } catch (e) {
        console.warn('Failed to register access', e);
    }
}

/**
 * Enforce Eviction Policy (LRU)
 * Removes oldest SYNCED items if storage is critical.
 * @returns {Promise<object>} Stats on evicted items
 */
export async function enforceEvictionPolicy() {
    const stats = await checkQuota();

    // Only evict if critical threshold reached
    if (stats.percent < CRITICAL_THRESHOLD) {
        return { evicted: 0, freedBytes: 0, reason: 'quota_ok' };
    }

    console.warn(`⚠️ Storage critical (${(stats.percent * 100).toFixed(1)}%). Starting eviction...`);

    // 1. Get all ephemeral states sorted by last_accessed (oldest first)
    // We strictly use ephemeral_state to track "usage". 
    // If an entity isn't in ephemeral_state (never opened), it might be a candidate too,
    // but usually we cache things user opens.
    // NOTE: This assumes ephemeral_state covers mostly cached content.

    // For a more robust approach, we can query entities directly if ephemeral_state is sparse,
    // but ephemeral_state is designed for this.

    const states = await db.ephemeral_state.orderBy('last_accessed').toArray();

    let evictedCount = 0;
    let freedBytesEst = 0;

    for (const state of states) {
        // Stop if we drop below warning threshold (simple heuristic: remove 10% of items)
        // Or just remove chunk by chunk. Let's try to remove up to 50 items per run.
        if (evictedCount >= 50) break;

        const entityId = state.entity_id;

        // CHECK 1: Is it synced? NEVER delete unsynced work.
        // We check the sync registry for pending ops for this entity.
        const pendingOps = await db.sync_registry
            .where('entity_id')
            .equals(entityId)
            .count();

        if (pendingOps > 0) {
            continue; // Skip dirty/unsynced items
        }

        // CHECK 2: Is it pinned? (Future feature, placeholder)
        // if (state.is_pinned) continue;

        // SAFE TO EVICT
        // We remove the content but potentially keep the metadata (entity record) 
        // OR remove entirely. Removing entirely is safer for consistency.
        // Re-fetching metadata is cheap.

        await db.transaction('rw', db.entities, db.content_blocks, db.ephemeral_state, async () => {
            await db.entities.delete(entityId);
            await db.content_blocks.where('entity_id').equals(entityId).delete();
            await db.ephemeral_state.where({ user_id: state.user_id, entity_id: entityId }).delete();
        });

        evictedCount++;
        freedBytesEst += 100 * 1024; // Rough estimate 100KB per entity
    }

    console.log(`🧹 Evicted ${evictedCount} items to free space.`);
    return { evicted: evictedCount, freedBytes: freedBytesEst, reason: 'critical_cleanup' };
}
