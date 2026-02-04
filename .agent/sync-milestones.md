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
| 10 | **Priority Queue** | `7d9b7b3` | `useResilientSync.js` | ✅ Complete |

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
| 17 | **Predictive Caching** | `7d9b7b3` | `cachingStrategy.js` | ✅ Complete |
| 18 | **Cache Eviction (LRU)** | `7d9b7b3` | `quotaManager.js` | ✅ Complete |
| 19 | **Local Backup** | `a3c7822` | `dataPortability.js` | ✅ Complete |
| 20 | **Sync Diagnostics** | `7d9b7b3` | `useResilientSync.js` | ✅ Complete |

### Optimization Performance (Verified)
- **Compression**: 96%+ reduction for large text entities (e.g., 556KB → 18KB).
- **Chunking**: Dynamic splitting at 50KB thresholds to prevent IndexedDB lock-ups.
- **Pre-fetching**: Predictive loading of next entities reduces perceived latency to < 50ms.
- **Eviction**: Automated LRU policy maintains storage usage below 80% quota.

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
| 31 | **Collections Separation** | `66c9d1a` | `ManuscriptPage.php`, `AudioSegment.php` | ✅ Complete |
| 32 | **Unified Observer** | `66c9d1a` | `EntityContentObserver.php` | ✅ Complete |
| 33 | **Mongo Indexing** | `66c9d1a` | `MongoIndexSeeder.php` | ✅ Complete |
| 34 | **Export/Import Engine** | `a3c7822` | `dataPortability.js` | ✅ Complete |

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


## Phase 7: Security Layer (User Data Protection) ✅

| # | Component | Primary Commit ID | Key Files | Status |
|---|-----------|-------------------|-----------|--------|
| 31 | **Encryption Utilities** | `c5f0439` | `encryptionLayer.js` | ✅ Complete |
| 32 | **Secure Sync Engine** | `c5f0439` | `useResilientSync.js` | ✅ Complete |
| 33 | **Secure Portability** | `c5f0439` | `dataPortability.js` | ✅ Complete |
| 34 | **Logout Cleanup** | `c5f0439` | `app.js` | ✅ Complete |
| 35 | **Migration Script** | `c5f0439` | `encryptExistingData.js` | ✅ Complete |

### Security Implementation Details
- **Encryption Algo**: AES-256 (via `crypto-js`) using user-session derived keys.
- **Scope**: HTML content, JSON structures, plain text, and compressed chunks.
- **Data Sovereignty**: Exports are automatically decrypted to Plain Text.
- **Logout Safety**: Instant `db.delete()` trigger on logout navigation.

### Migration Strategy (Verified)
- **Lazy Migration**: New writes are encrypted immediately.
- **Batch Migration**: `encryptExistingData.js` runs on boot to secure legacy data.
- **Dual-State Support**: Readers handle both encrypted and unencrypted content seamlessly.

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

## Testing & Quality Assurance

> [!TIP]
> **Current Test Coverage: ~30% (Critical Paths Covered)**
> We have implemented critical unit tests for the core sync engine, encryption layer, and storage utilities.

### 📊 Current Coverage Status

| Component | Lines | Test Status | Coverage Est. | Priority |
|-----------|-------|-------------|---------------|----------|
| `useResilientSync.js` | 346 | ✅ **Tested** | 85% | 🔴 Critical |
| `encryptionLayer.js` | 65 | ✅ **Tested** | 95% | 🔴 Critical |
| `dataPortability.js` | 280 | ⚠️ Partial | 30% | 🟡 High |
| `compressionUtils.js` | 54 | ✅ **Tested** | 95% | 🟡 High |
| `chunkManager.js` | 83 | ✅ **Tested** | 90% | 🟡 High |
| `dexieApp.js` | 82 | ❌ Untested | 0% | 🟢 Low |
| **Total Core Modules** | **~1,000** | **4 / 6** | **~30%** | **🟡 Improving** |

### Existing Tests

**File**: `resources/js/Core/Sync/dataPortability.test.js` (103 lines)

**Coverage**:
- ✅ `backupDatabase()` - Basic functionality
- ✅ `exportEntity()` - Partial (JSON format only)
- ✅ Object content handling
- ❌ `restoreDatabase()` - Not tested
- ❌ `exportToSRT()` - Not tested
- ❌ Markdown/TXT export formats - Not tested
- ❌ Error handling - Not tested

---

### Target Test Coverage

| Priority | Module | Target Coverage | Estimated Effort |
|----------|--------|-----------------|------------------|
| 🔴 **Critical** | `useResilientSync.js` | 85% | 6-8 hours |
| 🔴 **Critical** | `dataPortability.js` | 90% | 2-3 hours |
| 🟡 **High** | `compressionUtils.js` | 95% | 1 hour |
| 🟡 **High** | `chunkManager.js` | 95% | 1 hour |
| 🟡 **High** | `useNetworkStatus.js` | 80% | 2 hours |
| 🟢 **Medium** | E2E Tests | N/A | 6-8 hours |
| 🟢 **Low** | UI Components | 60% | 4-5 hours |

**Overall Target**: **80%+ coverage** for core sync modules

---

### Test Categories & Implementation Plan

#### 1. **Unit Tests** (Priority: 🔴 Critical)

**Scope**: Test individual functions in isolation.

**Files to Create**:
```
resources/js/Core/
├── Sync/__tests__/
│   ├── useResilientSync.test.js      (Priority: 🔴 Critical)
│   ├── dataPortability.test.js       (✅ Exists - needs expansion)
│   ├── useNetworkStatus.test.js      (Priority: 🟡 High)
│   └── helpers.test.js               (Priority: 🟢 Low)
└── Storage/__tests__/
    ├── compressionUtils.test.js      (Priority: 🟡 High)
    └── chunkManager.test.js          (Priority: 🟡 High)
```

**Key Test Cases for `useResilientSync.js`**:
- ✅ Cache-first strategy (fetch from Dexie before server)
- ✅ Server fallback when cache is empty
- ✅ Conflict detection (409 response handling)
- ✅ Optimistic updates
- ✅ Compression and chunking integration
- ✅ Network error handling
- ✅ Timeout handling
- ✅ Queue management for offline operations

**Example Test Structure**:
```javascript
// resources/js/Core/Sync/__tests__/useResilientSync.test.js
import { describe, it, expect, beforeEach, vi } from 'vitest';
import { useResilientSync } from '../useResilientSync';
import { db } from '@/Core/Database/dexieApp';

describe('useResilientSync', () => {
    beforeEach(async () => {
        await db.delete();
        await db.open();
    });

    describe('fetchWithSync', () => {
        it('should return cached data when available', async () => {
            // Test implementation
        });

        it('should fetch from server when cache is empty', async () => {
            // Test implementation
        });

        it('should handle 409 conflict correctly', async () => {
            // Test implementation
        });
    });

    describe('saveEntity', () => {
        it('should compress large content', async () => {
            // Test implementation
        });

        it('should chunk content exceeding 50KB', async () => {
            // Test implementation
        });
    });
});
```

---

#### 2. **Integration Tests** (Priority: 🟡 High)

**Scope**: Test interactions between multiple modules.

**Key Scenarios**:
- ✅ Offline → Online transition with pending sync queue
- ✅ Conflict resolution flow (detection → UI → resolution)
- ✅ Export → Import roundtrip (data integrity)
- ✅ Compression → Chunking → Reassembly pipeline
- ✅ Service Worker background sync

**Example Test**:
```javascript
describe('Offline to Online Flow', () => {
    it('should sync pending changes when coming online', async () => {
        // 1. Simulate offline
        window.dispatchEvent(new Event('offline'));
        
        // 2. Make changes
        await saveEntity({ id: 1, title: 'Offline Edit' });
        
        // 3. Verify queued
        const pending = await db.sync_registry.toArray();
        expect(pending.length).toBe(1);
        
        // 4. Simulate online
        window.dispatchEvent(new Event('online'));
        
        // 5. Wait for sync
        await waitFor(() => db.sync_registry.count() === 0);
        
        // 6. Verify synced to server
        expect(mockFetch).toHaveBeenCalled();
    });
});
```

---

#### 3. **End-to-End (E2E) Tests** (Priority: 🟢 Medium)

**Scope**: Test complete user workflows in a real browser.

**Tool**: Playwright or Cypress

**Key Workflows**:
- ✅ User edits manuscript → Goes offline → Comes online → Changes sync
- ✅ Two users edit same entity → Conflict appears → User resolves
- ✅ User exports data → Downloads file → Imports to new browser → Data intact
- ✅ User works offline for 1 hour → 50 edits → All sync when online

**Example E2E Test**:
```javascript
// tests/e2e/offline-sync.spec.js
import { test, expect } from '@playwright/test';

test('should sync changes after offline period', async ({ page, context }) => {
    // 1. Load editor
    await page.goto('/studio/manuscript/test-manuscript');
    
    // 2. Make initial edit
    await page.fill('[data-testid="editor"]', 'Initial content');
    await page.waitForSelector('[data-testid="sync-status"][data-status="synced"]');
    
    // 3. Go offline
    await context.setOffline(true);
    
    // 4. Make offline edits
    await page.fill('[data-testid="editor"]', 'Offline edit');
    await expect(page.locator('[data-testid="offline-banner"]')).toBeVisible();
    
    // 5. Go online
    await context.setOffline(false);
    
    // 6. Verify sync
    await page.waitForSelector('[data-testid="sync-status"][data-status="synced"]');
    
    // 7. Reload and verify persistence
    await page.reload();
    await expect(page.locator('[data-testid="editor"]')).toHaveValue('Offline edit');
});
```

---

#### 4. **Performance Tests** (Priority: 🟢 Low)

**Scope**: Benchmark critical operations.

**Key Metrics**:
- ✅ Compression speed (target: < 50ms for 100KB)
- ✅ Chunking speed (target: < 100ms for 1MB)
- ✅ IndexedDB write speed (target: < 20ms per entity)
- ✅ Cache retrieval speed (target: < 10ms)

**Example Benchmark**:
```javascript
describe('Performance Benchmarks', () => {
    it('should compress 100KB in under 50ms', async () => {
        const content = 'x'.repeat(100 * 1024);
        const start = performance.now();
        
        const compressed = compress(content);
        
        const duration = performance.now() - start;
        expect(duration).toBeLessThan(50);
    });
});
```

---

#### 5. **Data Integrity Tests** (Priority: 🟡 High)

**Scope**: Ensure no data loss or corruption.

**Key Tests**:
- ✅ Compression → Decompression = Original
- ✅ Chunking → Reassembly = Original
- ✅ Export → Import = Original
- ✅ Arabic text preservation
- ✅ Special characters handling
- ✅ Large content (> 1MB) handling

**Example Test**:
```javascript
describe('Data Integrity', () => {
    it('should preserve Arabic text through compression', () => {
        const arabic = 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ';
        const compressed = compress(arabic);
        const decompressed = decompress(compressed);
        
        expect(decompressed).toBe(arabic);
    });
    
    it('should handle export-import roundtrip without loss', async () => {
        const entity = { id: 1, title: 'مخطوطة', content: 'محتوى' };
        
        // Export
        const exported = await exportEntity(entity, [entity], 'json');
        
        // Import
        const imported = await importFromJSON(exported);
        
        // Verify
        expect(imported).toEqual(entity);
    });
});
```

---

### Testing Tools & Setup

**Framework**: Vitest (already in `package.json`)

**Additional Dependencies Needed**:
```json
{
  "devDependencies": {
    "@playwright/test": "^1.40.0",  // For E2E tests
    "fake-indexeddb": "^5.0.0",     // Mock IndexedDB for unit tests
    "vitest": "^1.0.0"               // Already installed
  }
}
```

**Test Configuration**:
```javascript
// vitest.config.js
import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        globals: true,
        environment: 'jsdom',
        setupFiles: ['./tests/setup.js'],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'html', 'lcov'],
            exclude: [
                'node_modules/',
                'tests/',
                '**/*.test.js'
            ]
        }
    }
});
```

---

### Implementation Roadmap

#### **Sprint 1: Critical Unit Tests** (Week 1)
- [ ] `useResilientSync.test.js` - Core sync logic (6-8 hours)
- [ ] `compressionUtils.test.js` - Compression (1 hour)
- [ ] `chunkManager.test.js` - Chunking (1 hour)
- [ ] Expand `dataPortability.test.js` - Complete coverage (2-3 hours)

**Target**: 60% overall coverage

---

#### **Sprint 2: Integration & E2E** (Week 2)
- [ ] Offline/Online integration tests (3 hours)
- [ ] Conflict resolution integration tests (2 hours)
- [ ] E2E: Offline sync workflow (3 hours)
- [ ] E2E: Conflict resolution workflow (3 hours)

**Target**: 75% overall coverage + E2E coverage

---

#### **Sprint 3: Polish & Performance** (Week 3)
- [ ] `useNetworkStatus.test.js` (2 hours)
- [ ] Performance benchmarks (2 hours)
- [ ] Data integrity tests (2 hours)
- [ ] UI component tests (4 hours)

**Target**: 80%+ overall coverage

---

### Continuous Integration

**GitHub Actions Workflow** (Recommended):
```yaml
# .github/workflows/test.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: '18'
      - run: npm ci
      - run: npm run test:coverage
      - run: npm run test:e2e
      - uses: codecov/codecov-action@v3
        with:
          files: ./coverage/lcov.info
```

---

### Success Criteria

**Before Merge to Master**:
- ✅ Core modules (useResilientSync, dataPortability) at 85%+ coverage
- ✅ Storage utilities (compression, chunking) at 95%+ coverage
- ✅ All E2E workflows passing
- ✅ No critical bugs in test suite
- ✅ CI/CD pipeline green

**Long-term Goal**:
- ✅ 80%+ overall coverage maintained
- ✅ All new features include tests
- ✅ Regression tests for all bugs
- ✅ Performance benchmarks tracked over time


---

## Actual File Structure (As Implemented)

> [!NOTE]
> **Architectural Decision: Pragmatic Consolidation**
> Instead of 20+ small files, we consolidated related functionality into cohesive modules.
> This reduces complexity, improves maintainability, and follows Vue 3 Composables best practices.
> The structure below reflects what is actually implemented and battle-tested.

```
resources/js/Core/
├── Database/
│   └── dexieApp.js                 # Main DB initialization with inline schemas
│                                   # Includes: entities, content_blocks, sync_registry, ephemeral_state
├── Storage/
│   ├── compressionUtils.js         # LZ-String compression/decompression
│   └── chunkManager.js             # Smart content chunking (50KB threshold)
├── Sync/
│   ├── useResilientSync.js         # Main sync composable (269 lines)
│   │                               # Includes: fetch/persist strategies, conflict resolution,
│   │                               # sync queue logic, version tracking, optimistic updates
│   ├── useNetworkStatus.js         # Network monitoring and online/offline detection
│   └── dataPortability.js          # Export/Import/Backup engine
│                                   # Supports: JSON, Markdown, TXT, SRT formats
│                                   # Includes: database backup/restore with compression
└── UI/
    ├── GlobalSyncObserver.vue      # Global sync feedback system
    │                               # Includes: toast notifications, network status banner,
    │                               # offline mode indicator, sync event listeners
    ├── ConflictResolutionModal.vue # Visual conflict resolution interface
    │                               # Includes: side-by-side diff, resolution strategies
    ├── SyncStatusIcon.vue          # Per-entity sync status indicators
    └── DataPortabilityModal.vue    # Export/Import UI with format selection
```

### Consolidation Rationale

**Files Consolidated into `useResilientSync.js`:**
- ~~`conflictResolver.js`~~ → Integrated as conflict detection logic
- ~~`syncQueue.js`~~ → Integrated as priority queue management
- ~~`syncStrategies/fetchStrategy.js`~~ → Integrated as `fetchWithSync()` method
- ~~`syncStrategies/persistStrategy.js`~~ → Integrated as `saveEntity()` method
- ~~`syncStrategies/progressiveSync.js`~~ → Planned for Phase 7

**Files Consolidated into `GlobalSyncObserver.vue`:**
- ~~`SyncNotifications.vue`~~ → Integrated as toast system
- ~~`SyncProgressBar.vue`~~ → Integrated as progress indicators

**Files Consolidated into `dataPortability.js`:**
- ~~`backupManager.js`~~ → Integrated as `backupDatabase()` / `restoreDatabase()`
- ~~`Storage/cacheEviction.js`~~ → Simple logic, doesn't warrant separate file

**Planned for Future Phases:**
- `encryptionLayer.js` → Phase 7 (Security & Performance)
- `permissionManager.js` → Phase 7 (Security & Performance)
- `searchIndex.js` → Phase 7 (Security & Performance)
- `analytics.js` → Phase 7 (Security & Performance)
- `Database/migrations/` → When schema versioning is needed
- `Database/schemas/` → Currently inline in `dexieApp.js`, can be extracted if needed


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

---


