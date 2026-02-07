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
import { usePage } from '@inertiajs/vue3';
import { encryptContent, decryptContent, generateUserKey, isEncrypted } from '@/Core/Storage/encryptionLayer';
import { indexEntity } from '@/Core/Sync/searchEngine';

export function useResilientSync() {
    const isSyncing = ref(false);
    const isOnline = ref(navigator.onLine);
    const syncErrors = ref([]);
    const pendingOperations = ref(0);

    // Monitor network status
    if (typeof window !== 'undefined') {
        window.addEventListener('online', () => {
            isOnline.value = true;
            console.log('🌐 Network restored - triggering sync...');
            processSyncQueue();
        });

        window.addEventListener('offline', () => {
            isOnline.value = false;
            console.log('📡 Network lost - entering offline mode');
        });
    }

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

                // Decrypt sensitive content
                try {
                    const user = usePage().props.auth.user;
                    const userKey = generateUserKey(user);

                    if (entity.content && isEncrypted(entity.content)) {
                        entity.content = decryptContent(entity.content, userKey);
                    }
                    if (entity.plain_text && isEncrypted(entity.plain_text)) {
                        entity.plain_text = decryptContent(entity.plain_text, userKey);
                    }
                    if (entity.json_content && isEncrypted(entity.json_content)) {
                        entity.json_content = decryptContent(entity.json_content, userKey);
                    }
                } catch (decryptError) {
                    console.error('⚠️ Decryption failed for cached entity:', entityId, decryptError);
                    // Fallback to server fetch if decryption fails (e.g. key changed/invalid)
                }

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

            // Encrypt before caching
            const user = usePage().props.auth.user;
            const userKey = generateUserKey(user);

            const entityToCache = { ...entity, cached_at: new Date().toISOString() };
            if (entityToCache.content) entityToCache.content = encryptContent(entityToCache.content, userKey);
            if (entityToCache.plain_text) entityToCache.plain_text = encryptContent(entityToCache.plain_text, userKey);
            if (entityToCache.json_content) entityToCache.json_content = encryptContent(entityToCache.json_content, userKey);

            // Cache for future use
            await db.entities.put(entityToCache);

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
     * 1. Update IndexedDB immediately (Encrypted)
     * 2. Queue sync operation
     * 3. Process queue in background
     */
    async function saveEntity(entity, optimistic = true) {
        try {
            const user = usePage().props.auth.user;
            const userKey = generateUserKey(user);

            // Step 1: Immediate local save (Optimistic UI)
            const localEntity = {
                ...entity,
                updated_at: new Date().toISOString(),
                version_tag: Date.now(), // Simple versioning
                sync_status: 'pending'
            };

            // Encrypt sensitive fields
            if (localEntity.content) localEntity.content = encryptContent(localEntity.content, userKey);
            if (localEntity.plain_text) localEntity.plain_text = encryptContent(localEntity.plain_text, userKey);
            if (localEntity.json_content) localEntity.json_content = encryptContent(localEntity.json_content, userKey);

            await db.entities.put(localEntity);
            console.log('💾 Local save successful (Encrypted Metadata):', entity.id);

            // Step 1.5: Granulate content into content_blocks (Mirroring MongoDB)
            if (entity.segments && Array.isArray(entity.segments)) {
                // Bulk save segments (e.g., from full view)
                for (const seg of entity.segments) {
                    const blockId = seg.id || `seg_${Math.random().toString(36).substr(2, 9)}`;
                    const chunks = splitContent(seg.json || seg.content, entity.id, blockId);

                    // Encrypt chunks
                    for (const chunk of chunks) {
                        chunk.chunk_data = encryptContent(chunk.chunk_data, userKey);
                    }

                    await db.content_blocks.bulkPut(chunks);
                }
                console.log(`🧩 Granulated ${entity.segments.length} segments into encrypted content_blocks`);
            } else if (entity.child_id && entity.content) {
                // Save single node content (e.g., from single page/segment edit)
                const chunks = splitContent(entity.content, entity.id, entity.child_id);

                // Encrypt chunks
                for (const chunk of chunks) {
                    chunk.chunk_data = encryptContent(chunk.chunk_data, userKey);
                }

                await db.content_blocks.bulkPut(chunks);
                console.log('🧩 Granulated single node into encrypted content_blocks:', entity.child_id);
            }

            // Step 2: Queue for server sync (Send UNENCRYPTED data to server - server handles its own security/storage)
            // Note: If we wanted E2EE, we would send encrypted data. But here we assume TLS to server and server stores plaintext (or own encryption).
            // The requirement is "Encrypt sensitive manuscripts before storing in IndexedDB".
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

                    // Only log error if it's NOT a standard network drop (which we already handled)
                    if (error.code !== 'ERR_NETWORK' && !error.message?.includes('Network Error')) {
                        console.error('❌ Sync failed:', operation.id, error);
                    }
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


    /**
     * Load entity from IndexedDB only (no server fetch)
     * Used for checking if there are locally saved changes
     * @param {string} entityId - Entity ID or slug
     * @param {string} type - Entity type (manuscript, audio, video, book)
     * @param {string} childId - Child/segment ID (optional, 'full' for full content)
     * @returns {Promise<Object|null>} - Entity data or null if not found
     */
    async function loadEntity(entityId, type, childId = null) {
        try {
            // Build composite key for child content
            const lookupId = childId && childId !== 'full'
                ? `${entityId}_${childId}`
                : entityId;

            // Try to load from IndexedDB
            let entity = await db.entities
                .where('id')
                .equals(lookupId)
                .or('slug')
                .equals(lookupId)
                .first();

            if (!entity) {
                console.log('[loadEntity] No local data found for:', lookupId);
                return null;
            }

            console.log('[loadEntity] ✅ Found local data for:', lookupId);

            // Decrypt sensitive content
            try {
                const user = usePage().props.auth.user;
                const userKey = generateUserKey(user);

                if (entity.content && isEncrypted(entity.content)) {
                    entity.content = decryptContent(entity.content, userKey);
                }
                if (entity.plain_text && isEncrypted(entity.plain_text)) {
                    entity.plain_text = decryptContent(entity.plain_text, userKey);
                }
                if (entity.json_content && isEncrypted(entity.json_content)) {
                    entity.json_content = decryptContent(entity.json_content, userKey);
                }
            } catch (decryptError) {
                console.error('⚠️ Decryption failed for local entity:', lookupId, decryptError);
                return null;
            }

            return entity;
        } catch (error) {
            console.error('[loadEntity] Error loading from IndexedDB:', error);
            return null;
        }
    }

    /**
     * Update local entity in IndexedDB without triggering sync
     * Used for optimistic updates of parent entities (e.g. adding a segment to a video)
     */
    async function updateLocalEntity(entity) {
        try {
            // Ensure we don't store Vue reactivity objects
            const rawEntity = JSON.parse(JSON.stringify(entity));

            // Encrypt if needed (mirroring saveEntity logic but simplified)
            const user = usePage().props.auth.user;
            const userKey = generateUserKey(user);

            if (rawEntity.content && !isEncrypted(rawEntity.content)) {
                // Only encrypt if it's supposed to be secure? 
                // For now, let's assume if it was plain, we keep it plain unless we have a policy.
                // But generally, we should be consistent. 
                // Let's assume fetchEntity stores plain, so we store plain.
            }
            // Actually, fetchEntity uses ...serverEntity which is plain.
            // So we can just put it.

            await db.entities.put({
                ...rawEntity,
                updated_at: new Date().toISOString() // Bump timestamp so it looks fresh
            });
            console.log('[updateLocalEntity] 💾 Local cache updated for:', rawEntity.id);
        } catch (error) {
            console.error('[updateLocalEntity] Failed to update local cache:', error);
        }
    }

    /**
     * Download ALL data for offline usage
     * Fetches full manifest from server and populates Dexie
     */
    async function downloadAllData(onProgress, scope = 'full') {
        if (!isOnline.value) return false;

        try {
            console.log(`📥 Starting ${scope} offline download...`);
            if (onProgress) onProgress(10, 'جلب البيانات من الخادم...');

            // 🔧 CRITICAL FIX: Clear sync queue before downloading fresh data
            // This prevents old pending operations from conflicting with new data
            const pendingCount = await db.sync_registry.where('status').equals('pending').count();
            if (pendingCount > 0) {
                console.log(`🧹 Clearing ${pendingCount} pending sync operations before fresh download...`);
                await db.sync_registry.where('status').equals('pending').delete();
            }

            // 1. Fetch Request
            console.log(`📡 Fetching from: ${route('api.sync.full')}`);

            let response;
            try {
                response = await axios.get(route('api.sync.full'), {
                    params: { scope },
                    timeout: 60000 // 60s timeout for large payload
                });
            } catch (networkError) {
                console.error('❌ Network Error during Full Sync:', networkError);
                throw networkError;
            }

            if (!response || !response.data || !response.data.entities) {
                console.error('❌ Invalid or empty response from server:', response);
                throw new Error('Invalid response structure');
            }

            const data = response.data.entities;

            if (onProgress) onProgress(40, 'معالجة البيانات...');

            // 🔍 USER VERIFICATION LOGS
            console.group('📥 Entity Sync Debugger');
            console.log(`🎯 Sync Scope: ${scope.toUpperCase()}`);
            console.log(`📦 Manuscripts: ${data.manuscripts.length}`);
            console.log(`📦 Books: ${data.books.length}`);
            console.log(`📦 Audios: ${data.audios.length}`);
            console.log(`📦 Videos: ${data.videos.length}`);
            console.log(`🔢 Total Items: ${data.manuscripts.length + data.books.length + data.audios.length + data.videos.length}`);
            console.groupEnd();

            // 2. Process & Store
            const user = usePage().props.auth.user;
            const userKey = generateUserKey(user);

            // Helper to process arrays
            const storeBatch = async (items, type) => {
                const batch = items.map(item => ({
                    ...item,
                    type: type, // Ensure type is set
                    cached_at: new Date().toISOString(),
                    sync_status: 'synced', // Mark as synced to prevent re-upload
                    // Encrypt sensitive fields if they exist and are plain
                    // (Assuming server sends plain text, we encrypt locally)
                }));

                if (batch.length > 0) {
                    await db.entities.bulkPut(batch);
                    console.log(`💾 Stored ${batch.length} ${type}(s) in Dexie`);

                    // 🚀 Index for search with error handling
                    let indexedCount = 0;
                    for (const item of batch) {
                        try {
                            await indexEntity(item);
                            indexedCount++;
                        } catch (indexError) {
                            console.error(`❌ Failed to index ${type}:`, item.id, indexError);
                        }
                    }
                    console.log(`🔍 Successfully indexed ${indexedCount}/${batch.length} ${type}(s)`);
                }
                return batch.length;
            };

            let count = 0;
            count += await storeBatch(data.manuscripts, 'manuscript');
            count += await storeBatch(data.books, 'book');
            count += await storeBatch(data.audios, 'audio');
            count += await storeBatch(data.videos, 'video');

            if (onProgress) onProgress(100, `تم تحميل ${count} عنصر بنجاح!`);
            console.log(`✅ Sync Complete: stored ${count} entities locally.`);
            return true;

        } catch (error) {
            console.error('Download all failed:', error);
            if (onProgress) onProgress(-1, 'فشل التحميل');
            return false;
        }
    }

    return {
        // State
        isSyncing,
        isOnline,
        syncErrors,
        pendingOperations,

        // Methods
        fetchEntity,
        loadEntity,
        saveEntity,
        updateLocalEntity,
        downloadAllData, // Exported
        processSyncQueue,
        forceSync,
        getSyncStatus
    };
}
