import { describe, it, expect } from 'vitest';
import { compress, decompress, getCompressionStats } from '../compressionUtils';

describe('Compression Utils', () => {
    describe('compress & decompress', () => {
        it('should compress and decompress string correctly', () => {
            const original = 'Test Content';
            const compressed = compress(original);
            const decompressed = decompress(compressed);

            expect(compressed).not.toBe(original);
            expect(decompressed).toBe(original);
        });

        it('should compress and decompress object correctly', () => {
            const original = { id: 1, title: 'Test Object' };
            const compressed = compress(original);
            const decompressed = decompress(compressed);

            expect(decompressed).toEqual(original);
        });

        it('should handle Arabic text correctly', () => {
            const original = 'محتوى عربي للاختبار';
            const decompressed = decompress(compress(original));
            expect(decompressed).toBe(original);
        });

        it('should handle empty input', () => {
            expect(compress('')).toBe('');
            expect(compress(null)).toBe('');
            expect(decompress(null)).toBeNull();
            expect(decompress('')).toBeNull();
        });

        it('should return null (or defined string) if decompression fails', () => {
            // LZString behavior on garbage input varies, just ensure it doesn't crash
            const result = decompress('invalid string');
            expect(result).toBeDefined();
        });

        it('should respect parseJSON=false', () => {
            const obj = { key: 'value' };
            const compressed = compress(obj);
            const raw = decompress(compressed, false);

            expect(typeof raw).toBe('string');
            expect(raw).toContain('key');
            expect(raw).toContain('value');

            const parsed = JSON.parse(raw);
            expect(parsed).toEqual(obj);
        });
    });

    describe('getCompressionStats', () => {
        it('should calculate stats correctly', () => {
            const original = { data: 'x'.repeat(1000) };
            const compressed = compress(original);
            const stats = getCompressionStats(original, compressed);

            expect(stats).toHaveProperty('originalBytes');
            expect(stats).toHaveProperty('compressedBytes');
            expect(stats).toHaveProperty('ratio');
            expect(stats).toHaveProperty('savedBytes');

            expect(stats.savedBytes).toBeGreaterThan(0);
        });
    });
});
