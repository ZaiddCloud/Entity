/**
 * Dexie Database Configuration
 * Local-First Architecture - Client-Side Persistence Layer
 * 
 * This is the foundation of the offline-capable Entity system.
 * All frontend data flows through this IndexedDB instance.
 */

import Dexie from 'dexie';

// Initialize Dexie database
export const db = new Dexie('EntityLocalDB_v2');

// Define database schema (version 1)
db.version(1).stores({
    // Entities: Core metadata storage
    // Indexes: id (primary), slug, type, parent_id, updated_at
    entities: 'id, slug, type, parent_id, updated_at, version_tag',

    // Content Blocks: Large content storage (Tiptap JSON, MongoDB transcripts)
    // Indexes: [node_id+segment_order] (compound primary), entity_id
    content_blocks: '[node_id+segment_order], entity_id, chunk_hash, is_loaded',

    // Sync Registry: Pending operations queue
    // Indexes: id (primary), timestamp, priority, status
    sync_registry: '++id, timestamp, priority, operation_type, entity_id, status, [entity_id+status], retry_count',

    // Ephemeral State: Transient UI state (playback positions, settings)
    // Indexes: composite key [user_id+entity_id]
    // Ephemeral State: Transient UI state (playback positions, settings)
    // Indexes: composite key [user_id+entity_id]
    ephemeral_state: '[user_id+entity_id], user_id, entity_id, last_accessed'
});

// Version 2: Update content_blocks to use compound primary key
db.version(2).stores({
    content_blocks: '[node_id+segment_order], entity_id, chunk_hash, is_loaded'
});

// Version 3: Search Index for Offline Discovery
db.version(3).stores({
    // Full-text search index (Word-to-Entity mapping)
    // Primary key: auto-increment, Indexes: word, entity_id
    search_index: '++id, word, entity_id, type, [word+entity_id]'
});

// Database lifecycle hooks
db.on('ready', async () => {
    console.log('✅ EntityLocalDB initialized successfully');

    // Verify search_index table exists
    try {
        const tableExists = db.tables.some(table => table.name === 'search_index');
        if (!tableExists) {
            console.warn('⚠️ search_index table missing! Database needs upgrade.');
            console.log('🔄 Please clear IndexedDB and reload, or increment version.');
            return;
        }

        const indexCount = await db.search_index.count();
        const entityCount = await db.entities.count();

        console.log(`🔍 Search index contains ${indexCount} entries`);
        console.log(`📦 Entities table contains ${entityCount} items`);

        // 🔧 AUTO-REINDEX: If we have entities but no search index, rebuild it
        if (entityCount > 0 && indexCount === 0) {
            console.warn('⚠️ Search index is empty but entities exist. Auto-reindexing...');

            // Import indexEntity dynamically to avoid circular dependency
            const { indexEntity } = await import('../Sync/searchEngine.js');

            const allEntities = await db.entities.toArray();
            let indexed = 0;

            for (const entity of allEntities) {
                try {
                    await indexEntity(entity);
                    indexed++;
                } catch (error) {
                    console.error(`Failed to index ${entity.type}:`, entity.id, error);
                }
            }

            console.log(`✅ Auto-reindexed ${indexed}/${allEntities.length} entities`);
        }
    } catch (error) {
        console.error('❌ Error checking search_index:', error);
    }
});

db.on('populate', () => {
    console.log('🔄 First-time database setup...');
    // Initial data seeding can go here if needed
});

db.on('blocked', () => {
    console.warn('⚠️ Database upgrade blocked - close other tabs');
});

// Export database instance
export default db;

/**
 * Database Statistics Helper
 * Useful for monitoring storage usage
 */
export async function getDatabaseStats() {
    const stats = {
        entities: await db.entities.count(),
        contentBlocks: await db.content_blocks.count(),
        pendingSync: await db.sync_registry.where('status').equals('pending').count(),
        totalSize: 0 // Will be calculated if needed
    };

    return stats;
}

/**
 * Clear all local data (use with caution!)
 * Typically called on logout
 */
export async function clearAllData() {
    await db.entities.clear();
    await db.content_blocks.clear();
    await db.sync_registry.clear();
    await db.ephemeral_state.clear();
    await db.search_index.clear();
    console.log('🗑️ All local data cleared');
}
