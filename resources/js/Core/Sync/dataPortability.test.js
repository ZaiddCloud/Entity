import { describe, it, expect, vi, beforeEach } from 'vitest';
import { backupDatabase, exportEntity } from '@/Core/Sync/dataPortability';
import db from '@/Core/Database/dexieApp';

// Mock LZString and Blobs if needed, but let's see if they work in jsdom
vi.mock('lz-string', () => ({
    default: {
        compressToUTF16: (s) => s,
        decompressFromUTF16: (s) => s
    }
}));

// Mock Blob and URL
global.Blob = class {
    constructor(content, options) {
        this.content = content;
        this.options = options;
    }
};
global.URL = {
    createObjectURL: vi.fn(),
    revokeObjectURL: vi.fn()
};

// Mock document.createElement
document.body.appendChild = vi.fn();
document.body.removeChild = vi.fn();

describe('Data Portability Engine', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('backupDatabase should collect data from all tables', async () => {
        // Mock db.tables
        const mockTable = {
            name: 'entities',
            toArray: vi.fn().mockResolvedValue([{ id: 1, title: 'Test' }])
        };
        vi.spyOn(db, 'tables', 'get').mockReturnValue([mockTable]);
        vi.spyOn(db, 'table').mockReturnValue(mockTable);
        const blobSpy = vi.spyOn(global, 'Blob');

        const result = await backupDatabase();

        expect(result).toBe(true);
        expect(mockTable.toArray).toHaveBeenCalled();
        expect(blobSpy).toHaveBeenCalled();
    });

    it('exportEntity should format markdown correctly', async () => {
        const entity = { type: 'book', slug: 'test-book', title: 'Test Book' };
        const blocks = [{ title: 'Page 1', content: '<p>Hello</p>', plain_text: 'Hello' }];
        const blobSpy = vi.spyOn(global, 'Blob');

        const result = await exportEntity(entity, blocks, 'markdown');

        expect(result).toBe(true);
        expect(blobSpy).toHaveBeenCalled();
        const call = blobSpy.mock.calls[0][0][0];
        expect(call).toContain('# Test Book');
        expect(call).toContain('## Page 1');
        expect(call).toContain('Hello');
    });

    it('exportEntity should fetch missing content from Dexie content_blocks', async () => {
        const entity = { id: 'test-entity', type: 'manuscript', slug: 'm1', title: 'Manuscript' };
        // This block has no content or plain_text
        const blocks = [{ id: 'b1', title: 'Block 1' }];

        // Mock chain: db.content_blocks.where({...}).toArray()
        const mockWhere = vi.fn().mockReturnValue({
            toArray: vi.fn().mockResolvedValue([
                { segment_order: 0, chunk_data: 'Reassembled Content' }
            ])
        });
        vi.spyOn(db.content_blocks, 'where').mockImplementation(mockWhere);

        // For the sake of test, we assume reassembleChunks returns the data
        // as long as we have lz-string mocked correctly (identity)

        const blobSpy = vi.spyOn(global, 'Blob');
        await exportEntity(entity, blocks, 'markdown');

        expect(blobSpy).toHaveBeenCalled();
        const call = blobSpy.mock.calls[0][0][0];
        expect(call).toContain('Reassembled Content');
        expect(mockWhere).toHaveBeenCalledWith({ entity_id: 'test-entity', node_id: 'b1' });
    });

    it('exportEntity should handle Object content without crashing', async () => {
        const entity = { id: 'test-entity', type: 'book', slug: 'b1', title: 'Book' };
        const blocks = [{ id: 'b1', title: 'JSON Block', content: { type: 'doc', content: [] } }];

        const blobSpy = vi.spyOn(global, 'Blob');
        const result = await exportEntity(entity, blocks, 'markdown');

        expect(result).toBe(true);
        expect(blobSpy).toHaveBeenCalled();
        const call = blobSpy.mock.calls[0][0][0];
        expect(call).toContain('{"type":"doc","content":[]}');
    });
});
