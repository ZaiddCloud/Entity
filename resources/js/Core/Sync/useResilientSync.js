/**
 * Resilient Sync Composable
 * Core synchronization logic for Local-First architecture
 * 
 * Strategy: Client-Side Truth with Optimistic UI
 * - Read from IndexedDB first (instant response)
 * - Sync with server in background
 * - Handle conflicts gracefully
 */

import { ref, computed } from 'vue';
import db from '@/Core/Database/dexieApp';
import axios from 'axios';
import { splitContent } from '@/Core/Storage/chunkManager';

export function useResilientSync() {
    const isSyncing = ref(false);
    const isOnline = ref(navigator.onLine);
    const syncErrors = ref([]);
    const pendingOperations = ref(0);

    // Monitor network status
    window.addEventListener('online', () => {
        isOnline.value = true;
        console.log('🌐 Network restored - triggering sync...');
        processSyncQueue();
    });

    window.addEventListener('offline', () => {
        isOnline.value = false;
        console.log('📡 Network lost - entering offline mode');
    });

    /**
     * Fetch entity with cache-first strategy
     * 1. Check IndexedDB (instant)
     * 2. If not found, fetch from server
     * 3. If found, background delta-sync
     */
    async function fetchEntity(type, entityId) {
        try {
            // Step 1: Try local cache first
            let entity = await db.entities.get(entityId);

            if (entity) {
                console.log('✅ Cache hit for entity:', entityId);

                // Background delta-sync if online
                if (isOnline.value) {
                    backgroundDeltaSync(type, entity);
                }

                return entity;
            }

            // Step 2: Cache miss - fetch from server
            console.log('⬇️ Cache miss - fetching from server:', entityId);

            if (!isOnline.value) {
                throw new Error('Entity not cached and network unavailable');
            }

            const response = await axios.get(`/api/entities/${type}/${entityId}`);
            entity = response.data.entity;

            // Cache for future use
            await db.entities.put({
                ...entity,
                cached_at: new Date().toISOString()
            });

            console.log('💾 Entity cached:', entityId);
            return entity;

        } catch (error) {
            console.error('❌ Fetch error:', error);
            syncErrors.value.push({
                type: 'fetch',
                entityId,
                error: error.message,
                timestamp: new Date().toISOString()
            });
            throw error;
        }
    }

    /**
     * Save entity with optimistic UI
     * 1. Update IndexedDB immediately
     * 2. Queue sync operation
     * 3. Process queue in background
     */
    async function saveEntity(entity, optimistic = true) {
        try {
            // Step 1: Immediate local save (Optimistic UI)
            const localEntity = {
                ...entity,
                updated_at: new Date().toISOString(),
                version_tag: Date.now(), // Simple versioning
                sync_status: 'pending'
            };

            await db.entities.put(localEntity);
            console.log('💾 Local save successful (Entity Metadata):', entity.id);

            // Step 1.5: Granulate content into content_blocks (Mirroring MongoDB)
            if (entity.segments && Array.isArray(entity.segments)) {
                // Bulk save segments (e.g., from full view)
                for (const seg of entity.segments) {
                    const blockId = seg.id || `seg_${Math.random().toString(36).substr(2, 9)}`;
                    const chunks = splitContent(seg.json || seg.content, entity.id, blockId);
                    await db.content_blocks.bulkPut(chunks);
                }
                console.log(`🧩 Granulated ${entity.segments.length} segments into content_blocks`);
            } else if (entity.child_id && entity.content) {
                // Save single node content (e.g., from single page/segment edit)
                const chunks = splitContent(entity.content, entity.id, entity.child_id);
                await db.content_blocks.bulkPut(chunks);
                console.log('🧩 Granulated single node into content_blocks:', entity.child_id);
            }

            // Step 2: Queue for server sync
            await db.sync_registry.add({
                timestamp: new Date().toISOString(),
                priority: entity.priority || 'HIGH',
                operation_type: entity.operation_type || 'UPDATE',
                entity_id: entity.id,
                status: 'pending',
                retry_count: 0,
                payload: {
                    ...entity,
                    _sync_meta: {
                        method: entity.method || 'PUT',
                        url: entity.url || `/api/entities/${entity.type}/${entity.id}`
                    }
                }
            });

            pendingOperations.value++;

            // Step 3: Trigger background sync if online
            if (isOnline.value) {
                processSyncQueue();
            }

            return localEntity;

        } catch (error) {
            console.error('❌ Save error:', error);
            throw error;
        }
    }

    /**
     * Background delta-sync
     * Check if server has newer version
     */
    async function backgroundDeltaSync(type, entity) {
        try {
            const response = await axios.get(`/api/entities/${type}/${entity.id}`, {
                headers: {
                    'If-Modified-Since': entity.updated_at
                }
            });

            // 304 = Not Modified, our cache is fresh
            if (response.status === 304) {
                console.log('✅ Cache is up-to-date:', entity.id);
                return;
            }

            // Server has newer version - update cache
            const serverEntity = response.data.entity;
            await db.entities.put({
                ...serverEntity,
                cached_at: new Date().toISOString()
            });

            console.log('🔄 Cache updated from server:', entity.id);

        } catch (error) {
            // 304 is success (Not Modified)
            if (error.response?.status === 304) {
                console.log('✅ Background sync: Content up to date (304 Not Modified)');
                return;
            }

            // Silent failure for background sync
            console.warn('⚠️ Background sync failed:', error.message);
        }
    }

    /**
     * Process sync queue
     * Upload pending operations to server
     */
    async function processSyncQueue() {
        if (isSyncing.value || !isOnline.value) {
            return;
        }

        isSyncing.value = true;

        try {
            const pending = await db.sync_registry
                .where('status')
                .equals('pending')
                .sortBy('priority');

            console.log(`🔄 Processing ${pending.length} pending operations...`);

            for (const operation of pending) {
                try {
                    // Check online status before each operation
                    if (!isOnline.value) {
                        console.log('📡 Network unavailable, pausing sync queue...');
                        break;
                    }

                    await syncOperation(operation);

                    // Mark as completed
                    await db.sync_registry.update(operation.id, {
                        status: 'completed',
                        completed_at: new Date().toISOString()
                    });

                    // Update entity sync status
                    await db.entities.update(operation.entity_id, {
                        sync_status: 'synced'
                    });

                    pendingOperations.value--;

                } catch (error) {
                    // Smart Error Handling for Network Issues
                    if (error.code === 'ERR_NETWORK' || error.message.includes('Network Error')) {
                        console.log('📡 Network lost during sync. Pausing queue.');
                        isOnline.value = false;
                        break; // Stop processing queue, don't mark as failed, just pending
                    }

                    // For actual logical errors (422, 500), mark as failed
                    // Mark as failed, will retry later
                    await db.sync_registry.update(operation.id, {
                        status: 'failed',
                        retry_count: operation.retry_count + 1,
                        last_error: error.message
                    });

                    console.error('❌ Sync failed:', operation.id, error);
                }
            }

            console.log('✅ Sync queue processed (or paused)');

        } catch (error) {
            console.error('❌ Queue processing error:', error);
        } finally {
            isSyncing.value = false;
        }
    }

    /**
     * Sync single operation to server
     */
    async function syncOperation(operation) {
        const meta = operation.payload._sync_meta || {};
        const method = (meta.method || 'PUT').toLowerCase();
        const url = meta.url || `/api/entities/${operation.payload.type}/${operation.entity_id}`;

        // Remove internal sync meta before sending
        const cleanPayload = { ...operation.payload };
        delete cleanPayload._sync_meta;

        switch (method) {
            case 'post':
                await axios.post(url, cleanPayload);
                break;
            case 'put':
                await axios.put(url, cleanPayload);
                break;
            case 'patch':
                await axios.patch(url, cleanPayload);
                break;
            case 'delete':
                await axios.delete(url);
                break;
            default:
                throw new Error(`Unsupported sync method: ${method}`);
        }
    }

    /**
     * Get sync status for an entity
     */
    async function getSyncStatus(entityId) {
        const entity = await db.entities.get(entityId);
        const pendingOps = await db.sync_registry
            .where({ entity_id: entityId, status: 'pending' })
            .count();

        return {
            isSynced: entity?.sync_status === 'synced' && pendingOps === 0,
            isPending: pendingOps > 0,
            lastSync: entity?.updated_at
        };
    }

    /**
     * Force Synchronization
     * Resets failed operations to pending and triggers queue processing.
     */
    async function forceSync() {
        if (!isOnline.value) {
            console.warn('📡 Cannot force sync while offline');
            return false;
        }

        console.log('🔄 Forced sync initiated...');

        // Reset failed operations to pending
        await db.sync_registry
            .where('status')
            .equals('failed')
            .modify({ status: 'pending', retry_count: 0 });

        await processSyncQueue();
        return true;
    }

    return {
        // State
        isSyncing,
        isOnline,
        syncErrors,
        pendingOperations,

        // Methods
        fetchEntity,
        saveEntity,
        processSyncQueue,
        forceSync,
        getSyncStatus
    };
}
