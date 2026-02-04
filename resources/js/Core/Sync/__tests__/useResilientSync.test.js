import 'fake-indexeddb/auto'; // Automatically mocks global indexedDB before any other import relying on it
import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest';
import { useResilientSync } from '../useResilientSync';
import db from '@/Core/Database/dexieApp';
import axios from 'axios';

// Mock Axios
vi.mock('axios');

// Mock Inertia usePage for User Auth
vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        props: {
            auth: {
                user: { id: 1, name: 'Test User' }
            }
        }
    }),
    router: {
        on: vi.fn()
    }
}));

// Mock chunkManager purely to avoid complexity in this test? 
// No, let's keep it real since we have encryption logic there too.

import { isEncrypted, decryptContent } from '@/Core/Storage/encryptionLayer';

describe('useResilientSync & Encryption Integration', () => {
    const { fetchEntity, saveEntity, isOnline } = useResilientSync();
    const userKey = '0c6f076c5e7b28236317f22501a355609e2300b86a88cc425e7144e31139423c'; // SHA256 of "entity-secure-storage-1"

    beforeEach(async () => {
        await db.delete();
        await db.open();
        vi.clearAllMocks();
        // Reset online status
        isOnline.value = true;
    });

    afterEach(async () => {
        // Cleanup seems automatic with fake-indexeddb in memory, but db.delete ensures clean slate
    });

    describe('saveEntity', () => {
        it('should encrypt sensitive data before saving to IndexedDB', async () => {
            const sensitiveEntity = {
                id: 1,
                type: 'manuscript',
                title: 'Secret Title',
                content: '<p>Super Secret Content</p>',
                plain_text: 'Super Secret Content',
                json_content: { type: 'doc', text: 'Secret' }
            };

            await saveEntity(sensitiveEntity, true);

            // Verify in DB
            const stored = await db.entities.get(1);

            // Should exist
            expect(stored).toBeDefined();

            // Content fields should be encrypted strings
            expect(isEncrypted(stored.content)).toBe(true);
            expect(stored.content).not.toContain('Super Secret Content');

            expect(isEncrypted(stored.plain_text)).toBe(true);
            expect(stored.plain_text).not.toContain('Super Secret Content');

            expect(isEncrypted(stored.json_content)).toBe(true);
        });

        it('should granule and encrypt content blocks', async () => {
            const entity = {
                id: 2,
                child_id: 'node-1',
                content: 'Part 1'.repeat(1000) // Ensure it's substantial
            };

            await saveEntity(entity);

            const blocks = await db.content_blocks.where('entity_id').equals(2).toArray();
            expect(blocks.length).toBeGreaterThan(0);

            // Check chunk encryption
            expect(isEncrypted(blocks[0].chunk_data)).toBe(true);
        });
    });

    describe('fetchEntity', () => {
        it('should decrypt cached data on retrieval (Offline)', async () => {
            // Setup: Save encrypted data first
            const original = {
                id: 3,
                type: 'manuscript',
                content: 'Cached Content'
            };
            await saveEntity(original);

            // Go offline to prevent background sync
            isOnline.value = false;

            // Fetch (should hit cache)
            const result = await fetchEntity('manuscript', 3);

            // Should be decrypted
            expect(result.content).toBe('Cached Content');
            expect(isEncrypted(result.content)).toBe(false);

            // Ensure no network call
            expect(axios.get).not.toHaveBeenCalled();
        });

        it('should fetch from server if cache miss, then encrypt and cache', async () => {
            const serverEntity = {
                id: 4,
                type: 'manuscript',
                content: 'Server Content'
            };

            axios.get.mockResolvedValueOnce({ data: { entity: serverEntity } });

            const result = await fetchEntity('manuscript', 4);

            expect(result.content).toBe('Server Content');
            expect(axios.get).toHaveBeenCalledWith('/api/entities/manuscript/4');

            // Verify it was cached ENCRYPTED using raw DB access
            const cached = await db.entities.get(4);
            expect(isEncrypted(cached.content)).toBe(true);
        });

        it('should throw error if offline and not in cache', async () => {
            isOnline.value = false;

            await expect(fetchEntity('manuscript', 999))
                .rejects.toThrow(/not cached and network unavailable/i);
        });
    });
});
