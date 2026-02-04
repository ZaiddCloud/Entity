/**
 * Data Portability Engine
 * Touch #7: Sovereignty & Portability
 * 
 * Handles database-wide backups and restores, as well as specific entity exports.
 */

import db from '../Database/dexieApp.js';
import LZString from 'lz-string';
import { reassembleChunks } from '../Storage/chunkManager.js';

/**
 * Full Database Backup
 * Exports all Dexie tables into a single compressed JSON file.
 */
export async function backupDatabase() {
    try {
        console.log('📦 Starting database backup...');

        const backupData = {
            version: 1,
            timestamp: new Date().toISOString(),
            tables: {}
        };

        // Collect data from all tables
        const tableNames = db.tables.map(table => table.name);
        for (const tableName of tableNames) {
            backupData.tables[tableName] = await db.table(tableName).toArray();
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

                // Confirm with user (handled by UI, but here we perform the wipe)
                // Transactional clear and refill
                await db.transaction('rw', db.tables, async () => {
                    for (const tableName in backupData.tables) {
                        const table = db.table(tableName);
                        await table.clear();
                        await table.bulkAdd(backupData.tables[tableName]);
                    }
                });

                console.log('✅ Restore completed successfully');
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

        let safeBlocks = [...(blocks || [])];

        if (safeBlocks.length === 0) {
            console.warn('⚠️ No blocks provided for export, attempting fallback to entity.children...');
            if (entity.children && Array.isArray(entity.children)) {
                safeBlocks = entity.children;
                console.log(`✅ Fallback successful: using ${safeBlocks.length} children from entity.`);
            }
        }

        console.log(`📤 Exporting entity (${entity.type || 'unknown'}): ${entity.slug || 'no-slug'} as ${format} (${safeBlocks.length} blocks)...`);

        let output = '';
        const safeSlug = entity.slug || 'export';
        const date = new Date().toISOString().slice(0, 10);
        const extension = format === 'markdown' ? 'md' : format;
        let fileName = `${safeSlug}_${date}.${extension}`;

        if (format === 'json') {
            output = JSON.stringify({ entity, blocks: safeBlocks }, null, 2);
        }
        else if (format === 'markdown' || format === 'text') {
            // Header
            output = `# ${entity.title || 'بدون عنوان'}\n`;
            if (entity.author) output += `**المؤلف:** ${entity.author}\n`;
            output += `**التاريخ:** ${new Date().toLocaleDateString('ar-EG')}\n`;
            output += `\n---\n\n`;
            const entityId = entity.id || entity.slug;

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
                            const reassembled = reassembleChunks(chunks);
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
