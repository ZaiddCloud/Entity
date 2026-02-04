import { describe, it, expect, vi, beforeEach } from 'vitest';
import { checkQuota, enforceEvictionPolicy } from '../quotaManager';
import { db } from '@/Core/Database/dexieApp';

// Mock Dexie
vi.mock('@/Core/Database/dexieApp', () => ({
    db: {
        ephemeral_state: {
            orderBy: vi.fn().mockReturnThis(),
            toArray: vi.fn(),
            where: vi.fn().mockReturnThis(),
            delete: vi.fn()
        },
        sync_registry: {
            where: vi.fn().mockReturnThis(),
            equals: vi.fn().mockReturnThis(),
            count: vi.fn()
        },
        entities: {
            delete: vi.fn()
        },
        content_blocks: {
            where: vi.fn().mockReturnThis(),
            equals: vi.fn().mockReturnThis(),
            delete: vi.fn()
        },
        transaction: vi.fn((...args) => {
            const cb = args[args.length - 1];
            return typeof cb === 'function' ? cb() : Promise.resolve();
        })
    }
}));

describe('QuotaManager', () => {
    beforeEach(() => {
        vi.clearAllMocks();

        // Mock navigator.storage
        global.navigator.storage = {
            estimate: vi.fn().mockResolvedValue({ usage: 100, quota: 1000 })
        };
    });

    describe('checkQuota', () => {
        it('should return correct usage statistics', async () => {
            const stats = await checkQuota();
            expect(stats.usage).toBe(100);
            expect(stats.quota).toBe(1000);
            expect(stats.percent).toBe(0.1);
            expect(stats.usedMB).toBe('0.00');
        });

        it('should handle missing navigator.storage gracefully', async () => {
            delete global.navigator.storage;
            const stats = await checkQuota();
            expect(stats.usage).toBe(0);
            expect(stats.percent).toBe(0);
        });
    });

    describe('enforceEvictionPolicy', () => {
        it('should not evict if below critical threshold', async () => {
            // 10% usage (Critical is 80%)
            navigator.storage.estimate.mockResolvedValue({ usage: 100, quota: 1000 });

            const result = await enforceEvictionPolicy();
            expect(result.reason).toBe('quota_ok');
            expect(db.entities.delete).not.toHaveBeenCalled();
        });

        it('should evict synced items when above critical threshold', async () => {
            // 90% usage
            navigator.storage.estimate.mockResolvedValue({ usage: 900, quota: 1000 });

            // Mock oldest items
            db.ephemeral_state.toArray.mockResolvedValue([
                { entity_id: 'old_synced', user_id: 1, last_accessed: 100 },
                { entity_id: 'dirty_item', user_id: 1, last_accessed: 200 }
            ]);

            // Mock sync status: 'old_synced' has 0 pending, 'dirty_item' has 1 pending
            db.sync_registry.count.mockImplementation(({ equals }) => {
                // This is a bit simplified for the mock
                return Promise.resolve(0);
            });

            // Refined mock for count
            db.sync_registry.where.mockReturnThis();
            db.sync_registry.equals.mockImplementation((val) => {
                return {
                    count: () => Promise.resolve(val === 'dirty_item' ? 1 : 0)
                };
            });

            const result = await enforceEvictionPolicy();

            expect(result.evicted).toBe(1);
            expect(db.entities.delete).toHaveBeenCalledWith('old_synced');
            expect(db.entities.delete).not.toHaveBeenCalledWith('dirty_item');
        });
    });
});
