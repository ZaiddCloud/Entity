import { compress, decompress } from './compressionUtils';

/**
 * Chunk Manager for Heavy Content
 * Splits large content into 50KB chunks for efficient sync/storage
 */

const DEFAULT_CHUNK_SIZE = 50 * 1024; // 50KB

/**
 * Split content into chunks
 * @param {string|object} content - Content to split
 * @param {string} entityId - Associated entity ID
 * @param {string} nodeId - Node ID (e.g. content block ID)
 * @param {number} startOrder - Starting order index
 * @returns {Array} Array of chunk objects ready for Dexie
 */
export function splitContent(content, entityId, nodeId, startOrder = 0) {
    const stringContent = typeof content === 'string' ? content : JSON.stringify(content);
    const totalLength = stringContent.length;
    const chunks = [];

    let offset = 0;
    let order = startOrder;

    while (offset < totalLength) {
        const chunkData = stringContent.slice(offset, offset + DEFAULT_CHUNK_SIZE);
        const compressedChunk = compress(chunkData);

        chunks.push({
            node_id: nodeId,
            entity_id: entityId,
            segment_order: order++,
            chunk_hash: customHash(chunkData), // Simple integrity check
            chunk_data: compressedChunk,
            is_loaded: true, // We have it locally
            original_size: chunkData.length
        });

        offset += DEFAULT_CHUNK_SIZE;
    }

    return chunks;
}

/**
 * Reassemble chunks into full content
 * @param {Array} chunks - Array of chunk objects (must be ordered!)
 * @returns {string|object} Reassembled content
 */
export function reassembleChunks(chunks) {
    // Sort just in case
    const sortedChunks = [...chunks].sort((a, b) => a.segment_order - b.segment_order);

    let fullString = '';

    for (const chunk of sortedChunks) {
        const part = decompress(chunk.chunk_data);
        if (part) {
            fullString += part;
        }
    }

    try {
        return JSON.parse(fullString);
    } catch (e) {
        return fullString;
    }
}

/**
 * Simple hash function for integrity check (Java String.hashCode equivalent)
 * For production, consider SHA-256 via crypto.subtle
 */
function customHash(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        const char = str.charCodeAt(i);
        hash = (hash << 5) - hash + char;
        hash |= 0; // Convert to 32bit integer
    }
    return hash.toString(16);
}
