/**
 * Search Engine - Offline Discovery Layer
 * Handles word-based indexing and retrieval from IndexedDB.
 */

import db from '../Database/dexieApp';

const STOP_WORDS = new Set(['من', 'إلى', 'في', 'على', 'عن', 'ما', 'هذا', 'هذه', 'the', 'and', 'with', 'for']);

/**
 * Tokenize text into unique words, removing stop words and short terms.
 */
function tokenize(text) {
    if (!text) return [];

    // Normalize: 
    // 1. Lowercase
    // 2. Replace all non-alphanumeric (including Arabic chars) with space
    // 3. Split by whitespace
    const words = text.toLowerCase()
        .replace(/[^\w\u0600-\u06FF\d]/g, ' ') // Keep words, Arabic, and numbers
        .split(/\s+/)
        .filter(w => w.length >= 2 && !STOP_WORDS.has(w));

    return [...new Set(words)];
}

/**
 * Index an entity's metadata for search.
 */
export async function indexEntity(entity) {
    if (!entity || !entity.id) return;

    try {
        // 1. Clear existing index for this entity to avoid duplicates/orphans
        await db.search_index.where('entity_id').equals(entity.id).delete();

        // 2. Extract indexable text
        const textToSearch = [
            entity.title,
            entity.description,
            entity.author,
            entity.slug
        ].join(' ');

        const words = tokenize(textToSearch);

        // 3. Store words in index
        const indexEntries = words.map(word => ({
            word,
            entity_id: entity.id,
            type: entity.type
        }));

        if (indexEntries.length > 0) {
            await db.search_index.bulkPut(indexEntries);
            console.log(`🔍 Indexed [${entity.type}] "${entity.title}" with ${words.length} words.`);
        }
    } catch (e) {
        console.error(`❌ Failed to index entity ${entity.id}:`, e);
    }
}

/**
 * Search entities by query
 * Uses exact word matching with fallback to prefix matching
 */
export async function searchEntities(query, limit = 10) {
    if (!query || query.trim().length === 0) return [];

    const terms = tokenize(query);
    if (terms.length === 0) return [];

    try {
        // Strategy: Try exact matches first, then prefix matches
        const exactMatches = await db.search_index
            .where('word')
            .anyOf(terms)
            .toArray();

        const prefixMatches = await db.search_index
            .where('word')
            .startsWithAnyOf(terms)
            .toArray();

        // Combine and deduplicate
        const allMatches = [...exactMatches, ...prefixMatches];

        // Count occurrences for ranking (exact matches get higher weight)
        const counts = {};
        allMatches.forEach(m => {
            const weight = exactMatches.includes(m) ? 2 : 1;
            counts[m.entity_id] = (counts[m.entity_id] || 0) + weight;
        });

        // Get unique IDs sorted by match count
        const sortedIds = Object.keys(counts).sort((a, b) => counts[b] - counts[a]);

        // Fetch actual entities
        const results = await db.entities
            .where('id')
            .anyOf(sortedIds.slice(0, limit))
            .toArray();

        // Sort results to match the ranking order
        return results.sort((a, b) => {
            return sortedIds.indexOf(a.id) - sortedIds.indexOf(b.id);
        });
    } catch (error) {
        console.error('Search error:', error);
        return [];
    }
}
