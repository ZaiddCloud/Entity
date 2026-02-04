import { describe, it, expect } from 'vitest';
import { splitContent, reassembleChunks } from '../chunkManager';
import { decompress } from '../compressionUtils';

describe('Chunk Manager', () => {
    describe('splitContent', () => {
        it('should split large content into chunks', () => {
            // Create content larger than 50KB (default chunk size)
            // 50KB = 51200 chars approx
            const content = 'a'.repeat(60000);
            const chunks = splitContent(content, 1, 'node-1');

            expect(chunks.length).toBeGreaterThan(1);
            expect(chunks[0].chunk_data).toBeDefined();
            // Verify chunks are compressed strings
            expect(typeof chunks[0].chunk_data).toBe('string');
        });

        it('should assign correct metadata to chunks', () => {
            const content = 'test';
            const chunks = splitContent(content, 101, 'node-1', 5);

            expect(chunks).toHaveLength(1);
            expect(chunks[0].entity_id).toBe(101);
            expect(chunks[0].node_id).toBe('node-1');
            expect(chunks[0].segment_order).toBe(5);
        });

        it('should handle JSON content', () => {
            const content = { key: 'val'.repeat(100) };
            const chunks = splitContent(content, 1, 'node-1');

            // Reassemble to check integrity
            const reassembled = reassembleChunks(chunks);
            expect(reassembled).toEqual(content);
        });
    });

    describe('reassembleChunks', () => {
        it('should reassemble chunks correctly', () => {
            const original = 'a'.repeat(100000);
            const chunks = splitContent(original, 1, 'n1');
            const reassembled = reassembleChunks(chunks);

            expect(reassembled).toBe(original);
        });

        it('should sort chunks by order before reassembling', () => {
            const original = 'part1part2part3';
            // Manually creating mock chunks to test ordering logic explicitly
            // Assuming splitContent uses compression, we mock that behavior or use real split and shuffle
            // Let's use real split and shuffle for robustness
            const chunks = splitContent(original, 1, 'n1'); // Might be too small to split?
            // Force split by mocking constant?? No, rely on splitContent logic or manually creating compressed chunks
            // splitContent has hardcoded 50KB size. Hard to unit test sorting without huge strings or mocking.
            // Let's trust reassembleChunks logic:
            // const sortedChunks = [...chunks].sort((a, b) => a.segment_order - b.segment_order);

            // Let's force smaller chunks if possible? No param for size.
            // We'll test with huge string to ensure multiple chunks.
            const bigOriginal = 'A'.repeat(60000) + 'B'.repeat(60000); // > 100KB -> 3 chunks
            const chunks2 = splitContent(bigOriginal, 1, 'n1');
            expect(chunks2.length).toBeGreaterThan(2);

            // Shuffle chunks
            const shuffled = [chunks2[1], chunks2[0], chunks2[2]].filter(c => c);
            const reassembled = reassembleChunks(shuffled);

            expect(reassembled).toBe(bigOriginal);
        });
    });
});
