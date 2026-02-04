import { describe, it, expect, vi, beforeEach } from 'vitest';
import { predictNextMoves } from '../cachingStrategy';
import { db } from '@/Core/Database/dexieApp';

// Mock Dexie
vi.mock('@/Core/Database/dexieApp', () => ({
    db: {
        entities: {
            where: vi.fn().mockReturnThis(),
            first: vi.fn()
        }
    }
}));

describe('CachingStrategy', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    describe('predictNextMoves', () => {
        it('should predict the next entity from navigation data', async () => {
            const current = {
                id: 'page-1',
                type: 'manuscript',
                navigation: {
                    next: { id: 'page-2', slug: 'page-2-slug' }
                }
            };

            const predictions = await predictNextMoves(current);
            expect(predictions).toHaveLength(1);
            expect(predictions[0].id).toBe('page-2');
        });

        it('should fallback to slug if ID is missing in navigation', async () => {
            const current = {
                id: 'page-1',
                type: 'manuscript',
                navigation: {
                    next: { slug: 'next-slug' }
                }
            };

            const predictions = await predictNextMoves(current);
            expect(predictions[0].id).toBe('next-slug');
        });

        it('should return empty if no navigation or common patterns found', async () => {
            const current = { id: 'page-1', type: 'manuscript' };
            const predictions = await predictNextMoves(current);
            expect(predictions).toHaveLength(0);
        });
    });
});
