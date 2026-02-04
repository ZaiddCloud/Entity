import db from '../dexieApp';
import { encryptContent, generateUserKey, isEncrypted } from '../../Storage/encryptionLayer';

/**
 * Migration: Encrypt Existing Data
 * Iterates through all sensitive tables and encrypts unencrypted content.
 * Should be run once after Phase 7 deployment.
 * 
 * @param {object} user - Authenticated user object
 * @returns {Promise<object>} Stats about migration { migratedEntities, migratedBlocks }
 */
export async function migrateToEncryption(user) {
    if (!user || !user.id) {
        console.error('❌ Migration failed: No authenticated user');
        return;
    }

    console.log('🔄 Starting migration to encrypted storage...');
    const userKey = generateUserKey(user);
    let stats = {
        entities: 0,
        blocks: 0
    };

    try {
        await db.transaction('rw', [db.entities, db.content_blocks], async () => {
            // 1. Migrate Entities
            const entities = await db.entities.toArray();
            for (const entity of entities) {
                let changed = false;

                if (entity.content && !isEncrypted(entity.content)) {
                    entity.content = encryptContent(entity.content, userKey);
                    changed = true;
                }

                if (entity.plain_text && !isEncrypted(entity.plain_text)) {
                    entity.plain_text = encryptContent(entity.plain_text, userKey);
                    changed = true;
                }

                if (entity.json_content && !isEncrypted(entity.json_content)) {
                    entity.json_content = encryptContent(entity.json_content, userKey);
                    changed = true;
                }

                if (changed) {
                    await db.entities.put(entity);
                    stats.entities++;
                }
            }

            // 2. Migrate Content Blocks
            const blocks = await db.content_blocks.toArray();
            for (const block of blocks) {
                if (block.chunk_data && !isEncrypted(block.chunk_data)) {
                    block.chunk_data = encryptContent(block.chunk_data, userKey);
                    await db.content_blocks.put(block);
                    stats.blocks++;
                }
            }
        });

        console.log(`✅ Migration complete. Encrypted ${stats.entities} entities and ${stats.blocks} content blocks.`);
        return stats;

    } catch (error) {
        console.error('❌ Migration failed:', error);
        throw error;
    }
}
