/**
 * Dexie Database Configuration
 * Local-First Architecture - Client-Side Persistence Layer
 * 
 * This is the foundation of the offline-capable Entity system.
 * All frontend data flows through this IndexedDB instance.
 */

import Dexie from 'dexie';

// Initialize Dexie database
export const db = new Dexie('EntityLocalDB');

// Define database schema (version 1)
db.version(1).stores({
    // Entities: Core metadata storage
    // Indexes: id (primary), slug, type, parent_id, updated_at
    entities: 'id, slug, type, parent_id, updated_at, version_tag',

    // Content Blocks: Large content storage (Tiptap JSON, MongoDB transcripts)
    // Indexes: node_id (primary), entity_id, segment_order
    content_blocks: 'node_id, entity_id, segment_order, chunk_hash, is_loaded',

    // Sync Registry: Pending operations queue
    // Indexes: id (primary), timestamp, priority, status
    sync_registry: '++id, timestamp, priority, operation_type, entity_id, status, retry_count',

    // Ephemeral State: Transient UI state (playback positions, settings)
    // Indexes: composite key [user_id+entity_id]
    ephemeral_state: '[user_id+entity_id], user_id, entity_id, last_accessed'
});

// Database lifecycle hooks
db.on('ready', () => {
    console.log('✅ EntityLocalDB initialized successfully');
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
    console.log('🗑️ All local data cleared');
}
