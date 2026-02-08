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

---

## Phase 17: PWA Extreme Resilience (The Survivalist) ✅

| # | Component | Primary Commit ID | Key Files | Status |
|---|-----------|-------------------|-----------|--------|
| 47 | **CacheFirst Asset Strategy** | `bc79e12` | `sw.js` | ✅ Complete |
| 48 | **Network-Aware Presence** | `bc79e12` | `usePresence.js` | ✅ Complete |
| 49 | **Cross-Port Session Support**| `bc79e12` | `app.php`, `cors.php` | ✅ Complete |
| 50 | **Build Automation** | `bc79e12` | `vite.config.js` | ✅ Complete |

### Survival Achievements
- **Deterministic Loading**: Verified `dexie.js` and App Shell load from cache even when the physical server is terminated (`pkill`).
- **Quiet Failover**: Heartbeat system now intelligently pauses during offline mode, reducing client-side noise.
- **Development Flexibility**: Fixed `401 Unauthorized` / `Inertia JSON` errors by enabling stateful requests across ports (8001 -> 8000).

---

## Phase 18: Integrated Data Management (The Hub) ✅

| # | Component | Primary Commit ID | Key Files | Status |
|---|-----------|-------------------|-----------|--------|
| 51 | **Navbar Data Hub** | `bc79e12` | `Navbar.vue`, `useResilientSync.js` | ✅ Complete |
| 52 | **Logic Consolidation** | `bc79e12` | `useResilientSync.js` | ✅ Complete |
| 53 | **Studio Sync Integration** | `bc79e12` | `StudioLayout.vue` | ✅ Complete |
| 54 | **UX Refinement** | `bc79e12` | `GlobalSyncObserver.vue` | ✅ Complete |
| 55 | **Regression Healing** | `5b68682` | `Navbar.vue`, `Index.vue` | ✅ Complete |

### Hub Achievements
- **Seamless Integration**: Relocated complex data management (backup, restore, storage stats) to a central, accessible Navbar dropdown.
- **Studio Efficiency**: Editors can now trigger manual syncs via a dedicated "Flash" button in the Studio toolbar.
- **Navigation Clarity**: Refined the Assignments page header with a discrete, integrated "Back" button.
- **PWA Restoration**: Restored the "Install App" capability within the Global Data Hub after the UI relocation.
- **Logic Purity**: Deduplicated background storage checks and cleared dead imports for optimal performance.

---

> **Migration Strategy:**
> Existing data will be gradually migrated to IndexedDB:
> 1. New edits are saved to both systems (dual-write)
> 2. Background job migrates historical data
> 3. After 100% migration, remove old system
> 4. Monitor for 2 weeks before final cleanup

---

*Last Updated: 2026-02-08*
*Status: Phase 18 Finalized & Documented*
*Next Review: Post-Deployment Stability Check*

---


