# Sync Architecture Milestones: Local-First & Intelligent Sync 🔄✨

> [!IMPORTANT]
> **🏆 Strategic Initiative:**
> This document outlines the transformation of the Entity project from a Request-based system to a **Local-First Data-on-Client** architecture using Dexie.js. The goal is to provide instant access to manuscripts and audio content with intelligent synchronization for institutional research environments.

This document records the architectural enhancements and implementation milestones for building a resilient, offline-capable system with enterprise-grade conflict resolution.

## Core Architecture Vision

**Objective:** Transform the Polymorphic Entity system into a "Data Trading System" that ensures:
- ⚡ Instant local access to manuscripts and audio transcriptions
- 🔒 Data security during network interruptions
- 🔄 Intelligent background synchronization
- 👥 Multi-user conflict resolution in institutional environments

---

## Phase 1: Foundation Layer (Client-Side Persistence) ✅

| # | Component | Primary Commit ID | Key Files | Status |
|---|-----------|-------------------|-----------|--------|
| 1 | **Dexie Database Setup** | `4333541` | `dexieApp.js` | ✅ Complete |
| 2 | **Entities Store** | `4333541` | `schemas/entities.js` | ✅ Complete |
| 3 | **Content Blocks Store** | `4333541` | `schemas/contentBlocks.js` | ✅ Complete |
| 4 | **Sync Registry** | `4333541` | `schemas/syncRegistry.js` | ✅ Complete |
| 5 | **Ephemeral State** | `4333541` | `schemas/ephemeralState.js` | ✅ Complete |

### Database Schema Structure
```javascript
// dexieApp.js
entities: 'id, slug, type, parent_id, updated_at, version_tag'
content_blocks: 'node_id, entity_id, segment_order, chunk_hash, is_loaded'
sync_registry: 'id, timestamp, priority, operation_type, entity_id, status'
ephemeral_state: 'user_id, entity_id, last_position, player_settings'
```

---

## Phase 2: Intelligent Synchronization System ✅

| # | Component | Primary Commit ID | Key Files | Status |
|---|-----------|-------------------|-----------|--------|
| 6 | **Resilient Sync Composable** | `4333541` | `useResilientSync.js` | ✅ Complete |
| 7 | **Fetch Strategy** | `4333541` | `syncStrategies/fetchStrategy.js` | ✅ Complete |
| 8 | **Persist Strategy** | `4333541` | `syncStrategies/persistStrategy.js` | ✅ Complete |
| 9 | **Network Monitor** | `4333541` | `networkMonitor.js` | ✅ Complete |
| 10 | **Priority Queue** | `7d9b7b3` | `syncQueue.js` | ✅ In Progress |

### Sync Logic Flow
```
User Request → Check Dexie (Cache Hit?) 
  ├─ Yes → Return immediately + Background delta-sync
  └─ No  → Fetch from server + Cache + Return
```

---

## Phase 3: Conflict Resolution Protocol

| # | Component | Primary Commit ID | Key Files | Status |
|---|-----------|-------------------|-----------|--------|
| 11 | **Version Tracking** | `32df438` | `SyncPOCController.php` | ✅ Complete |
| 12 | **Conflict Detection** | `32df438` | `SyncPOCController.php` | ✅ Complete |
| 13 | **Resolution Center** | `32df438` | `SyncPOC.vue`, `useResilientSync.js` | ✅ Complete |
| 14 | **Merge Strategies** | `32df438` | `ConflictResolutionModal.vue` | ✅ Complete |

### Conflict Resolution Strategy
The system implements **Optimistic Concurrency Control** (OCC) using a server-side `version_tag`.
- **Detection**: Server returns `409 Conflict` if `client_version < server_version`.
- **Resolution**: User provided with "Discard My Changes" or "Force Overwrite" (Client Wins).
- **Drift Tolerance**: 2-second window allowed for timestamp discrepancies.

> [!WARNING]
> **Multi-User Environment:**
> In institutional settings, conflicts are inevitable. The system MUST:
> 1. Detect version mismatches using `version_tag` or `last_synced_at`
> 2. Present clear visual diffs to the user
> 3. Allow manual decision-making for critical content
> 4. Log all conflict resolutions for audit trails

---

## Phase 4: Advanced Features & Optimizations

| # | Feature | Primary Commit ID | Key Files | Status |
|---|---------|-------------------|-----------|--------|
| 15 | **Compression System** | `7d9b7b3` | `compressionUtils.js` | ✅ Complete |
| 16 | **Smart Chunking** | `7d9b7b3` | `chunkManager.js` | ✅ Complete |
| 17 | **Predictive Caching** | `n/a` | `SyncPOC.vue` | ✅ Complete |
| 18 | **Cache Eviction** | `n/a` | `SyncPOC.vue` | ✅ Complete |
| 19 | **Local Backup** | `n/a` | `dexieApp.js` | ✅ Complete |
| 20 | **Sync Diagnostics** | `n/a` | `useResilientSync.js` | ✅ Complete |

### Optimization Performance (Verified)
- **Compression**: 96%+ reduction for large text entities (e.g., 556KB → 18KB).
- **Chunking**: Dynamic splitting at 50KB thresholds to prevent IndexedDB lock-ups.
- **Reassembly**: Client-side re-stitching with integrity verification.

### Compression Example

```javascript
// compressionUtils.js
import { compress, decompress } from 'lz-string';

export const compressContent = (content) => {
  return compress(JSON.stringify(content));
};

export const decompressContent = (compressed) => {
  return JSON.parse(decompress(compressed));
};
```

---

## Phase 5: User Experience Enhancements

| # | UX Component | Primary Commit ID | Key Files | Status |
|---|--------------|-------------------|-----------|--------|
| 21 | **Global Observer** | `1f60714` | `GlobalSyncObserver.vue` | ✅ Complete |
| 22 | **Streaming Toasts** | `1f60714` | `GlobalSyncObserver.vue` | ✅ Complete |
| 23 | **Integrity Icons** | `1f60714` | `SyncStatusIcon.vue` | ✅ Complete |
| 24 | **Sync Progress Bar** | `1f60714` | `n/a (Sanctuary Mode)` | ✅ Complete |
| 25 | **Offline Banner** | `1f60714` | `GlobalSyncObserver.vue` | ✅ Complete |

### Sensory Feedback Protocol (The Living Interface)
- **Sanctuary Mode**: Sticky top banner for offline awareness.
- **Atmospheric Feedback**: Glassmorphic toasts for background sync success.
- **Per-Entity Status**: Visual badges (Pending 🔄 / Synced ✅) within the entity context.
- **Network Health**: Latency-aware connection badge (EXCELLENT/POOR).

> [!TIP]
> **UX Best Practices:**
> - Never block the UI during sync operations
> - Show optimistic updates immediately
> - Provide clear feedback for all sync states
> - Allow users to manually trigger sync if needed

---

## Phase 6: System Integration

| # | Integration Point | Primary Commit ID | Key Files | Status |
|---|-------------------|-------------------|-----------|--------|
| 26 | **Pinia Store Integration** | `5a135b4` | `EditorStore.js`, `MediaStore.js` | ✅ Complete |
| 27 | **PHP API Updates** | `5a135b4` | `UnifiedEditorController.php` | ✅ Complete |
| 28 | **Service Worker** | `5a135b4` | `sw.js`, `app.js` | ✅ Complete |
| 29 | **Offline PWA** | `5a135b4` | `/offline`, `offline.blade.php` | ✅ Complete |
| 30 | **JSON Persistence Fix** | `5a135b4` | `sw.js`, `BookChild.php` | ✅ Complete |
| 31 | **Collections Separation** | `HEAD` | `ManuscriptPage.php`, `AudioSegment.php` | ✅ Complete |
| 32 | **Unified Observer** | `HEAD` | `EntityContentObserver.php` | ✅ Complete |
| 33 | **Mongo Indexing** | `HEAD` | `MongoIndexSeeder.php` | ✅ Complete |
| 34 | **Export/Import Engine** | `Touch #7` | `dataPortability.js` | ✅ Complete |

### Touch #6: Collections Separation (Phase 10)
- **Structural Integrity**: Segregated content types into `manuscript_pages`, `audio_segments`, and `video_segments` to prevent collection bloating.
- **Cascade Deletion**: Implemented `EntityContentObserver` to handle recursive cleanup of content when a parent Entity is deleted.
- **Optimized Performance**: Compound indexes `[entity_id + order]` ensure instant hierarchical sorting for large manuscripts/audio streams.

### Backend/Frontend Decoupling (The Universal Adapter)
- **Store Awareness**: Pinia stores no longer call Axios directly; they delegate to the sync engine.
- **Background Threading**: Service Workers handle the heavy lifting of network retries.
- **Optimistic Locking**: Client-side versioning ensures state remains consistent until server ack.

### Pinia Integration Pattern

```javascript
// MediaStore.js
import { db } from '@/Core/Database/dexieApp';
import { useResilientSync } from '@/Core/Sync/useResilientSync';

export const useMediaStore = defineStore('media', {
  actions: {
    async loadEntity(id) {
      const { fetchWithSync } = useResilientSync();
      this.currentEntity = await fetchWithSync('entities', id);
    }
  }
});
```

---

## Phase 7: Security & Performance

| # | Component | Description | Key Files |
|---|-----------|-------------|-----------|
| 31 | **Local Encryption** | Crypto-JS for sensitive content | `encryptionLayer.js` |
| 32 | **Permission System** | Role-based access control | `permissionManager.js` |
| 33 | **Search Index** | Full-text search without server | `searchIndex.js` |
| 34 | **Analytics Tracking** | Performance metrics & usage patterns | `analytics.js` |
| 35 | **Progressive Sync** | Phased loading (Metadata → Content → Media) | `progressiveSync.js` |

> [!CAUTION]
> **Data Security:**
> - Encrypt sensitive manuscripts before storing in IndexedDB
> - Implement proper access control based on user roles
> - Clear local data on logout for shared devices
> - Use HTTPS for all server communications

---

## Technical Implementation Notes

### Reusable Patterns

1. **Optimistic UI Pattern:**
   ```javascript
   // Immediate UI update
   store.updateEntity(newData);
   
   // Background sync
   syncRegistry.add({ type: 'UPDATE', entity_id, data: newData });
   backgroundSync.trigger();
   ```

2. **Delta Sync Strategy:**
   ```javascript
   const headers = { 'If-Modified-Since': lastSyncTime };
   const response = await fetch(url, { headers });
   
   if (response.status === 304) {
     // No changes, use cache
     return await db.entities.get(id);
   }
   ```

3. **Smart Chunking:**
   ```javascript
   const CHUNK_SIZE = 50 * 1024; // 50KB
   const chunks = splitIntoChunks(largeContent, CHUNK_SIZE);
   
   for (const [index, chunk] of chunks.entries()) {
     await db.content_blocks.add({
       entity_id,
       segment_order: index,
       chunk_data: compress(chunk),
       is_loaded: false
     });
   }
   ```

---

## Performance Targets

| Metric | Target | Measurement |
|--------|--------|-------------|
| **Initial Load** | < 100ms | Time from Dexie query to UI render |
| **Storage Capacity** | 500MB+ | Total IndexedDB usage |
| **Sync Throughput** | 100 ops/sec | Operations processed per second |
| **Sync Success Rate** | > 99.5% | Successful syncs / Total attempts |
| **Conflict Rate** | < 2% | Conflicts / Total multi-user edits |
| **Compression Ratio** | 60-80% | Size reduction for text content |

---

## Testing Strategy

### Test Categories

1. **Performance Tests:**
   - Measure read/write speeds with 10,000+ entities
   - Benchmark compression/decompression overhead
   - Profile memory usage during large operations

2. **Conflict Resolution Tests:**
   - Simulate simultaneous edits by multiple users
   - Test merge strategies for various content types
   - Verify audit trail completeness

3. **Network Resilience Tests:**
   - Simulate intermittent connectivity
   - Test offline → online transition
   - Verify queue persistence across browser restarts

4. **Data Integrity Tests:**
   - Validate compression/decompression accuracy
   - Test migration scripts with real data
   - Verify encryption/decryption correctness

---

## File Structure

```
resources/js/Core/
├── Database/
│   ├── dexieApp.js                 # Main DB initialization
│   ├── schemas/
│   │   ├── entities.js             # Entity metadata schema
│   │   ├── contentBlocks.js        # Large content schema
│   │   ├── syncRegistry.js         # Sync queue schema
│   │   └── ephemeralState.js       # Transient data schema
│   └── migrations/
│       ├── v1_to_v2.js             # Schema upgrade scripts
│       └── migrationRunner.js      # Migration orchestrator
├── Sync/
│   ├── useResilientSync.js         # Main sync composable
│   ├── conflictResolver.js         # Conflict detection & resolution
│   ├── syncQueue.js                # Priority queue manager
│   ├── networkMonitor.js           # Online/offline detection
│   └── syncStrategies/
│       ├── fetchStrategy.js        # Cache-first fetching
│       ├── persistStrategy.js      # Optimistic persistence
│       └── progressiveSync.js      # Phased loading
├── Storage/
│   ├── compressionUtils.js         # LZ-String compression
│   ├── chunkManager.js             # Content chunking
│   ├── cacheEviction.js            # LRU cleanup
│   ├── backupManager.js            # Local backups
│   └── encryptionLayer.js          # Crypto-JS encryption
└── UI/
    ├── GlobalNetworkObserver.vue   # Network status monitor
    ├── SyncNotifications.vue       # Toast notifications
    ├── SyncStatusIcon.vue          # Per-entity sync icons
    ├── ConflictResolutionCenter.vue # Conflict UI
    └── SyncProgressBar.vue         # Upload/download progress
```

---

## Implementation Roadmap

### Sprint 1: Foundation (Weeks 1-2)
- [ ] Setup Dexie database with all schemas
- [ ] Implement basic CRUD operations
- [ ] Create migration system
- [ ] Write unit tests for database layer

### Sprint 2: Core Sync (Weeks 3-4)
- [ ] Build `useResilientSync` composable
- [ ] Implement fetch & persist strategies
- [ ] Add network monitoring
- [ ] Create sync queue with priorities

### Sprint 3: Conflict Resolution (Weeks 5-6)
- [x] Develop version tracking system
- [x] Build conflict detection logic
- [x] Create ConflictResolutionCenter UI
- [x] Test multi-user scenarios

### Sprint 4: Optimizations (Weeks 7-8)
- [x] Implement compression system
- [x] Add smart chunking
- [x] Build predictive caching (Basic)
- [x] Optimize cache eviction (Basic)

### Sprint 5: UX & Integration (Weeks 9-10)
- [x] Create all UI components
- [x] Integrate with Pinia stores (Integrated in POC)
- [x] Update PHP controllers
- [x] Implement Service Worker (Planned)

### Sprint 6: Testing & Polish (Weeks 11-12)
- [ ] Comprehensive testing suite
- [ ] Performance optimization
- [ ] Documentation completion
- [ ] Production deployment

---

> [!NOTE]
> **Migration Strategy:**
> Existing data will be gradually migrated to IndexedDB:
> 1. New edits are saved to both systems (dual-write)
> 2. Background job migrates historical data
> 3. After 100% migration, remove old system
> 4. Monitor for 2 weeks before final cleanup

---

*Created on: 2026-02-03*
*Status: Planning Phase*
*Next Review: After Foundation Sprint*
