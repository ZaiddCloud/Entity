# Sync Architecture Rules & Guidelines

## Core Principles

### 1. Client-Side Truth (Single Source of Truth)
- **Rule:** IndexedDB (Dexie) is the primary data source for the frontend
- **Pattern:** Always read from Dexie first, fallback to server only on cache miss
- **Rationale:** Ensures instant UI responsiveness and offline capability

### 2. Optimistic UI Updates
- **Rule:** Update UI immediately, sync in background
- **Pattern:** 
  ```javascript
  // ✅ Correct
  store.updateEntity(newData);           // Immediate UI update
  syncRegistry.add(operation);           // Queue for background sync
  
  // ❌ Wrong
  await api.updateEntity(newData);       // Blocks UI
  store.updateEntity(newData);
  ```
- **Exception:** Critical operations (delete, publish) should wait for server confirmation

### 3. Delta Synchronization
- **Rule:** Only transfer changed data, not entire entities
- **Implementation:** Use `If-Modified-Since` headers and `last_synced_at` timestamps
- **Benefit:** Reduces bandwidth usage by 70-90% for large manuscripts

---

## Database Schema Conventions

### Naming Standards
- **Tables:** Plural snake_case (`content_blocks`, `sync_registry`)
- **Indexes:** Descriptive, comma-separated (`entity_id, segment_order`)
- **Primary Keys:** Auto-increment `++id` or explicit `id`

### Index Strategy
```javascript
// ✅ Good: Compound indexes for common queries
content_blocks: '[entity_id+segment_order], node_id, is_loaded'

// ❌ Bad: Over-indexing slows writes
content_blocks: 'id, entity_id, segment_order, node_id, created_at, updated_at'
```

### Data Types
- **Timestamps:** Use ISO 8601 strings (`new Date().toISOString()`)
- **Large Content:** Store as compressed strings (LZ-String)
- **Binary Data:** Use Blobs for images/audio chunks

---

## Sync Operation Priorities

### Priority Levels
| Priority | Use Case | Max Retry | Timeout |
|----------|----------|-----------|---------|
| **CRITICAL** | Content edits, deletions | 10 | 30s |
| **HIGH** | Metadata updates, tags | 5 | 15s |
| **MEDIUM** | User preferences, settings | 3 | 10s |
| **LOW** | Analytics, view counts | 1 | 5s |

### Queue Processing
- **Rule:** Process CRITICAL operations first, even if queued later
- **Batch Size:** Max 50 operations per sync cycle
- **Throttling:** Wait 2s between batches to avoid server overload

---

## Conflict Resolution Protocol

### Detection
```javascript
// Server checks version before accepting update
if (request.version_tag < entity.version_tag) {
  return response()->json([
    'conflict' => true,
    'server_version' => entity.toArray(),
    'client_version' => request.all()
  ], 409);
}
```

### Resolution Strategies

#### 1. Auto-Merge (Non-Conflicting Fields)
```javascript
// Example: User A edits title, User B edits tags
const merged = {
  ...serverVersion,
  title: clientVersion.title,  // Take newer title
  tags: serverVersion.tags     // Keep server tags
};
```

#### 2. Manual Resolution (Conflicting Fields)
- **Trigger:** Show ConflictResolutionCenter UI
- **Display:** Side-by-side diff with highlighting
- **Options:** Keep Mine | Keep Theirs | Merge Manually

#### 3. Last-Write-Wins (Low-Priority Data)
- **Use Case:** View counts, last_accessed timestamps
- **Rule:** Always accept the latest timestamp

---

## Storage Management

### Size Limits
- **Per Entity:** Max 10MB (compressed)
- **Total Storage:** Target 500MB, warn at 400MB
- **Chunk Size:** 50KB for text, 500KB for media

### Eviction Policy
```javascript
// Priority order for cleanup:
1. Unpinned entities (user hasn't marked as favorite)
2. Least Recently Used (LRU)
3. Largest entities first
4. Older than 30 days

// Never evict:
- Entities with pending sync operations
- User-pinned entities
- Currently open entities
```

### Compression Rules
- **Always Compress:** Text content > 5KB
- **Never Compress:** Already compressed media (JPEG, MP3)
- **Algorithm:** LZ-String (best for UTF-8 text)

---

## Network Handling

### Online Detection
```javascript
// ✅ Reliable detection
const isOnline = navigator.onLine && (await ping('/api/health'));

// ❌ Unreliable: navigator.onLine can be false positive
const isOnline = navigator.onLine;
```

### Retry Strategy
```javascript
const retryDelays = [1000, 2000, 5000, 10000, 30000]; // Exponential backoff

for (const delay of retryDelays) {
  try {
    await syncOperation();
    break; // Success
  } catch (error) {
    await sleep(delay);
  }
}
```

### Offline Queue Persistence
- **Rule:** Sync registry MUST survive browser restarts
- **Implementation:** Dexie automatically persists IndexedDB
- **Validation:** Test by closing browser mid-sync

---

## Integration with Existing Systems

### Pinia Store Pattern
```javascript
// ✅ Correct: Sync-aware store
export const useMediaStore = defineStore('media', {
  state: () => ({
    entities: [],
    syncStatus: 'idle' // idle | syncing | error
  }),
  
  actions: {
    async loadEntity(id) {
      // 1. Try local first
      let entity = await db.entities.get(id);
      
      // 2. Fetch from server if missing
      if (!entity) {
        entity = await api.fetchEntity(id);
        await db.entities.put(entity);
      }
      
      // 3. Background delta-sync
      this.syncEntity(id);
      
      return entity;
    }
  }
});
```

### PHP Controller Updates
```php
// Add sync metadata to responses
public function show(Entity $entity): JsonResponse
{
    return response()->json([
        'entity' => $entity,
        'sync_metadata' => [
            'version_tag' => $entity->version_tag,
            'last_modified' => $entity->updated_at->toIso8601String(),
            'checksum' => md5($entity->toJson())
        ]
    ]);
}
```

---

## Security Considerations

### Encryption
- **Rule:** Encrypt sensitive manuscripts before storing locally
- **Algorithm:** AES-256 via CryptoJS
- **Key Management:** Derive from user session token (never store plaintext keys)

### Access Control
```javascript
// Check permissions before allowing local edits
const canEdit = await checkPermission(entity.id, 'edit');
if (!canEdit) {
  throw new Error('Insufficient permissions');
}
```

### Data Cleanup
- **On Logout:** Clear all IndexedDB data
- **On Session Expire:** Encrypt local data, require re-authentication
- **Shared Devices:** Implement auto-lock after 15min inactivity

---

## Performance Optimization

### Lazy Loading
```javascript
// ✅ Load content chunks on-demand
const loadChunk = async (segmentOrder) => {
  const chunk = await db.content_blocks
    .where({ entity_id, segment_order })
    .first();
  
  if (!chunk.is_loaded) {
    chunk.data = await fetchChunkFromServer(chunk.node_id);
    chunk.is_loaded = true;
    await db.content_blocks.put(chunk);
  }
  
  return decompress(chunk.data);
};
```

### Batch Operations
```javascript
// ✅ Bulk insert for better performance
await db.content_blocks.bulkPut(chunks);

// ❌ Slow: Individual inserts
for (const chunk of chunks) {
  await db.content_blocks.put(chunk);
}
```

### Index Optimization
- **Rule:** Only index fields used in `where()` queries
- **Monitor:** Use Dexie's `db.on('ready')` to log index usage
- **Refactor:** Remove unused indexes after 1 month

---

## Testing Requirements

### Unit Tests
- ✅ All sync strategies must have 90%+ coverage
- ✅ Mock network failures (offline, slow, timeout)
- ✅ Test compression/decompression accuracy

### Integration Tests
- ✅ Multi-user conflict scenarios
- ✅ Large dataset performance (10,000+ entities)
- ✅ Migration scripts with real production data

### E2E Tests
- ✅ Complete offline → edit → online → sync flow
- ✅ Browser restart during sync
- ✅ Concurrent edits from multiple tabs

---

## Monitoring & Debugging

### Logging Strategy
```javascript
// Log all sync operations
syncLogger.info('Sync started', {
  operation: 'UPDATE',
  entity_id: 123,
  priority: 'CRITICAL',
  queue_size: 5
});
```

### Performance Metrics
- **Track:** Sync duration, queue depth, conflict rate
- **Alert:** If sync takes > 10s or queue > 100 operations
- **Dashboard:** Real-time sync health in dev tools

### Error Handling
```javascript
// ✅ Graceful degradation
try {
  await syncToServer(operation);
} catch (error) {
  logger.error('Sync failed', { error, operation });
  
  // Retry later, don't crash the app
  await db.sync_registry.update(operation.id, {
    status: 'failed',
    retry_count: operation.retry_count + 1,
    last_error: error.message
  });
}
```

---

## Migration Checklist

Before deploying sync architecture:

- [ ] Backup production database
- [ ] Test migration script on staging
- [ ] Implement dual-write period (2 weeks)
- [ ] Monitor error rates daily
- [ ] Prepare rollback plan
- [ ] Train support team on conflict resolution
- [ ] Update user documentation
- [ ] Load test with 1000+ concurrent users

---

**Last Updated:** 2026-02-03  
**Owner:** Core Architecture Team  
**Review Cycle:** Monthly
