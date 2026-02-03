import LZString from 'lz-string';

/**
 * Compression Utility for Local-First Sync
 * Uses LZ-String (UTF16) for storage-safe compression
 */

/**
 * Compress data string
 * @param {string|object} data - Data to compress
 * @returns {string} Compressed string (UTF16)
 */
export function compress(data) {
    if (!data) return '';
    const stringData = typeof data === 'string' ? data : JSON.stringify(data);
    return LZString.compressToUTF16(stringData);
}

/**
 * Decompress data string
 * @param {string} compressed - Compressed string (UTF16)
 * @returns {string|object} Decompressed data (auto-parsed if JSON)
 */
export function decompress(compressed) {
    if (!compressed) return null;
    const decompressed = LZString.decompressFromUTF16(compressed);

    if (!decompressed) return null;

    // Try parsing JSON, return string if fails
    try {
        return JSON.parse(decompressed);
    } catch (e) {
        return decompressed;
    }
}

/**
 * Get compression stats
 * @param {object} original 
 * @param {string} compressed 
 */
export function getCompressionStats(original, compressed) {
    const originalSize = new Blob([JSON.stringify(original)]).size;
    const compressedSize = new Blob([compressed]).size;
    const ratio = ((1 - (compressedSize / originalSize)) * 100).toFixed(2);

    return {
        originalBytes: originalSize,
        compressedBytes: compressedSize,
        ratio: `${ratio}%`,
        savedBytes: originalSize - compressedSize
    };
}
