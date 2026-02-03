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
            console.log('💾 Local save successful:', entity.id);

            // Step 2: Queue for server sync
            await db.sync_registry.add({
                timestamp: new Date().toISOString(),
                priority: 'HIGH',
                operation_type: 'UPDATE',
                entity_id: entity.id,
                status: 'pending',
                retry_count: 0,
                payload: entity
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
                    // Mark as failed, will retry later
                    await db.sync_registry.update(operation.id, {
                        status: 'failed',
                        retry_count: operation.retry_count + 1,
                        last_error: error.message
                    });

                    console.error('❌ Sync failed:', operation.id, error);
                }
            }

            console.log('✅ Sync queue processed');

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
        switch (operation.operation_type) {
            case 'UPDATE':
                await axios.put(`/api/entities/${operation.payload.type}/${operation.entity_id}`, operation.payload);
                break;
            case 'CREATE':
                await axios.post('/api/entities', operation.payload);
                break;
            case 'DELETE':
                await axios.delete(`/api/entities/${operation.entity_id}`);
                break;
            default:
                throw new Error(`Unknown operation type: ${operation.operation_type}`);
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
        getSyncStatus
    };
}
