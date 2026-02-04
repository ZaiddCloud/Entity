/**
 * Data Portability Engine
 * Touch #7: Sovereignty & Portability
 * 
 * Handles database-wide backups and restores, as well as specific entity exports.
 */

import db from '../Database/dexieApp.js';
import LZString from 'lz-string';
import { reassembleChunks } from '../Storage/chunkManager.js';
import { usePage } from '@inertiajs/vue3';
import { encryptContent, decryptContent, generateUserKey, isEncrypted } from '@/Core/Storage/encryptionLayer';

/**
 * Full Database Backup
 * Exports all Dexie tables into a single compressed JSON file.
 * NOTE: Exports are UNENCRYPTED (Plain Text) so users can own their data.
 */
export async function backupDatabase() {
    try {
        console.log('📦 Starting database backup...');

        const user = usePage().props.auth.user;
        const userKey = generateUserKey(user);

        const backupData = {
            version: 1,
            timestamp: new Date().toISOString(),
            tables: {}
        };

        // Collect data from all tables
        const tableNames = db.tables.map(table => table.name);
        for (const tableName of tableNames) {
            const tempRows = await db.table(tableName).toArray();

            // Decrypt sensitive data before export
            if (tableName === 'entities') {
                backupData.tables[tableName] = tempRows.map(row => ({
                    ...row,
                    content: row.content && isEncrypted(row.content) ? decryptContent(row.content, userKey) : row.content,
                    plain_text: row.plain_text && isEncrypted(row.plain_text) ? decryptContent(row.plain_text, userKey) : row.plain_text,
                    json_content: row.json_content && isEncrypted(row.json_content) ? decryptContent(row.json_content, userKey) : row.json_content
                }));
            } else if (tableName === 'content_blocks') {
                backupData.tables[tableName] = tempRows.map(row => ({
                    ...row,
                    chunk_data: row.chunk_data && isEncrypted(row.chunk_data) ? decryptContent(row.chunk_data, userKey) : row.chunk_data
                }));
            } else {
                backupData.tables[tableName] = tempRows;
            }
        }

        const jsonString = JSON.stringify(backupData);
        const compressed = LZString.compressToUTF16(jsonString);

        // Create download link
        const blob = new Blob([compressed], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const date = new Date().toISOString().slice(0, 10);

        const link = document.createElement('a');
        link.href = url;
        link.download = `Entity_Backup_${date}.entbak`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        console.log('✅ Backup completed successfully');
        return true;
    } catch (error) {
        console.error('❌ Backup failed:', error);
        throw error;
    }
}

/**
 * Restore Database from Backup
 * Imports data from a .entbak file.
 * Automatically ENCRYPTS data before storing in IndexedDB.
 */
export async function restoreDatabase(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onload = async (e) => {
            try {
                console.log('🔄 Starting database restore...');
                const compressed = e.target.result;
                const jsonString = LZString.decompressFromUTF16(compressed);

                if (!jsonString) {
                    throw new Error('Invalid or corrupted backup file');
                }

                const backupData = JSON.parse(jsonString);

                // Basic validation
                if (!backupData.tables || typeof backupData.tables !== 'object') {
                    throw new Error('Invalid backup format');
                }

                const user = usePage().props.auth.user;
                const userKey = generateUserKey(user);

                // Transactional clear and refill
                await db.transaction('rw', db.tables, async () => {
                    for (const tableName in backupData.tables) {
                        const table = db.table(tableName);
                        let rows = backupData.tables[tableName];

                        // Encrypt sensitive data before restoring
                        if (tableName === 'entities') {
                            rows = rows.map(row => ({
                                ...row,
                                content: row.content ? encryptContent(row.content, userKey) : null,
                                plain_text: row.plain_text ? encryptContent(row.plain_text, userKey) : null,
                                json_content: row.json_content ? encryptContent(row.json_content, userKey) : null,
                            }));
                        } else if (tableName === 'content_blocks') {
                            rows = rows.map(row => ({
                                ...row,
                                chunk_data: row.chunk_data ? encryptContent(row.chunk_data, userKey) : null
                            }));
                        }

                        await table.clear();
                        await table.bulkAdd(rows);
                    }
                });

                console.log('✅ Restore completed successfully (Encrypted)');
                resolve(true);
            } catch (error) {
                console.error('❌ Restore failed:', error);
                reject(error);
            }
        };

        reader.onerror = () => reject(new Error('File reading failed'));
        reader.readAsText(file);
    });
}

export async function exportEntity(entity, blocks, format = 'markdown') {
    try {
        if (!entity) throw new Error('Entity is missing');

        const user = usePage().props.auth.user;
        const userKey = generateUserKey(user);

        // Ensure entity fields are decrypted
        const safeEntity = { ...entity };
        if (safeEntity.content && isEncrypted(safeEntity.content)) safeEntity.content = decryptContent(safeEntity.content, userKey);
        if (safeEntity.plain_text && isEncrypted(safeEntity.plain_text)) safeEntity.plain_text = decryptContent(safeEntity.plain_text, userKey);
        // JSON Export might need json_content but usually uses blocks logic below
        if (safeEntity.json_content && isEncrypted(safeEntity.json_content)) safeEntity.json_content = decryptContent(safeEntity.json_content, userKey);


        let safeBlocks = [...(blocks || [])];

        if (safeBlocks.length === 0) {
            console.warn('⚠️ No blocks provided for export, attempting fallback to entity.children...');
            if (entity.children && Array.isArray(entity.children)) {
                safeBlocks = entity.children;
                console.log(`✅ Fallback successful: using ${safeBlocks.length} children from entity.`);
            }
        }

        // Decrypt blocks
        safeBlocks = safeBlocks.map(block => ({
            ...block,
            content: block.content && isEncrypted(block.content) ? decryptContent(block.content, userKey) : block.content,
            plain_text: block.plain_text && isEncrypted(block.plain_text) ? decryptContent(block.plain_text, userKey) : block.plain_text
            // chunk_data is usually internal, but if exported, decrypt it?
            // Export usually needs readable content.
        }));


        console.log(`📤 Exporting entity (${entity.type || 'unknown'}): ${entity.slug || 'no-slug'} as ${format} (${safeBlocks.length} blocks)...`);

        let output = '';
        const safeSlug = entity.slug || 'export';
        const date = new Date().toISOString().slice(0, 10);
        const extension = format === 'markdown' ? 'md' : format;
        let fileName = `${safeSlug}_${date}.${extension}`;

        if (format === 'json') {
            output = JSON.stringify({ entity: safeEntity, blocks: safeBlocks }, null, 2);
        }
        else if (format === 'markdown' || format === 'text') {
            // Header
            output = `# ${safeEntity.title || 'بدون عنوان'}\n`;
            if (safeEntity.author) output += `**المؤلف:** ${safeEntity.author}\n`;
            output += `**التاريخ:** ${new Date().toLocaleDateString('ar-EG')}\n`;
            output += `\n---\n\n`;
            const entityId = safeEntity.id || safeEntity.slug;

            for (let i = 0; i < safeBlocks.length; i++) {
                const block = safeBlocks[i];
                const title = block.title || `الجزء ${i + 1}`;
                const blockId = block.id || block._id;

                // Fetch missing content from content_blocks if needed
                let content = block.content || block.plain_text || '';

                if (!content) {
                    const blockId = block.id || block._id;
                    if (blockId) {
                        const chunks = await db.content_blocks
                            .where({ entity_id: entityId, node_id: blockId })
                            .toArray();

                        if (chunks.length > 0) {
                            // Verify chunks are encrypted and decrypt them
                            // reassembleChunks expects compressed data chunks.
                            // If they are encrypted, we must decrypt them first.
                            const decryptedChunks = chunks.map(c => ({
                                ...c,
                                chunk_data: c.chunk_data && isEncrypted(c.chunk_data) ? decryptContent(c.chunk_data, userKey) : c.chunk_data
                            }));

                            const reassembled = reassembleChunks(decryptedChunks);
                            content = ensureString(reassembled);
                        }
                    }
                } else {
                    content = ensureString(content);
                }

                if (format === 'markdown') {
                    output += `## ${title}\n\n`;
                    // Basic HTML stripping for markdown export
                    output += `${content.replace(/<[^>]*>/g, '')}\n\n`;
                    output += `\n---\n\n`;
                } else {
                    output += `[${title}]\n`;
                    output += `${content.replace(/<[^>]*>/g, '')}\n\n`;
                }
            }

            if (output.length < 20) {
                console.warn('⚠️ Export result is suspiciously small');
            }

            output += `\n*تم التصدير عبر نظام الكيان (Entity Monolith)*`;
        }

        // Trigger download
        const blob = new Blob([output], { type: 'text/plain;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        return true;
    } catch (error) {
        console.error('❌ Export failed:', error);
        throw error;
    }
}

/**
 * Export Audio/Video Transcription as SRT
 */
export async function exportToSRT(entity, segments) {
    try {
        if (!segments || !Array.isArray(segments) || segments.length === 0) {
            console.warn('⚠️ No segments provided for SRT export');
            return false;
        }

        let srtContent = '';

        segments.forEach((segment, index) => {
            // Support both 'start' (MediaStore) and 'start_time' (Legacy/DB)
            const startTime = Number(segment.start !== undefined ? segment.start : (segment.start_time || 0));

            // Ensure endTime is valid and > startTime
            let rawEnd = Number(segment.end !== undefined ? segment.end : (segment.end_time || 0));
            const endTime = (rawEnd > startTime) ? rawEnd : (startTime + 5); // Default 5s if missing or invalid

            const startStr = formatSRTTime(startTime);
            const endStr = formatSRTTime(endTime);

            // Format Content: [Title] Body
            const title = segment.label || segment.title || '';
            const rawBody = ensureString(segment.content || segment.plain_text || '');
            const body = rawBody.replace(/<[^>]*>/g, '').trim();

            let displayContent = body;
            if (title && body) {
                // If body doesn't already start with the title, prepend it
                if (!body.toLowerCase().startsWith(title.toLowerCase())) {
                    displayContent = `[${title}] ${body}`;
                }
            } else if (title && !body) {
                displayContent = title;
            }

            srtContent += `${index + 1}\n`;
            srtContent += `${startStr} --> ${endStr}\n`;
            srtContent += `${displayContent}\n\n`;
        });

        const blob = new Blob([srtContent], { type: 'text/plain;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;

        // Use human-readable title for filename, fallback to slug
        const baseName = entity.title || entity.slug || 'export';
        const safeName = baseName.replace(/[/\\?%*:|"<>]/g, '-'); // Basic filesystem safety
        link.download = `${safeName}.srt`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        return true;
    } catch (error) {
        console.error('❌ SRT Export failed:', error);
        throw error;
    }
}

function formatSRTTime(seconds) {
    const date = new Date(0);
    date.setSeconds(seconds);
    const ms = Math.floor((seconds % 1) * 1000);
    return date.toISOString().substr(11, 8) + ',' + ms.toString().padStart(3, '0');
}

/**
 * Ensures content is a string for processing
 */
function ensureString(content) {
    if (content === null || content === undefined) return '';
    if (typeof content === 'string') return content;
    try {
        return JSON.stringify(content);
    } catch (e) {
        return String(content);
    }
}
