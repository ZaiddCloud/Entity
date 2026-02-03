/**
 * Sync POC Demo Component
 * Demonstrates Local-First architecture capabilities
 * 
 * Features:
 * - Load entity from cache/server
 * - Edit and save locally (optimistic UI)
 * - Sync status indicators
 * - Offline mode testing
 */

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useResilientSync } from '@/Core/Sync/useResilientSync';
import db, { getDatabaseStats } from '@/Core/Database/dexieApp';

const {
    isSyncing,
    isOnline,
    syncErrors,
    pendingOperations,
    fetchEntity,
    saveEntity,
    processSyncQueue,
    getSyncStatus
} = useResilientSync();

// Demo state
const entityType = ref('book'); // Default to book
const entityId = ref(1); // Test with entity ID 1
const entity = ref(null);
const isLoading = ref(false);
const editMode = ref(false);
const editedTitle = ref('');
const syncStatus = ref(null);
const dbStats = ref(null);

// Computed
const syncStatusIcon = computed(() => {
    if (!isOnline.value) return '📡';
    if (isSyncing.value) return '🔄';
    if (pendingOperations.value > 0) return '📥';
    return '✅';
});

const syncStatusText = computed(() => {
    if (!isOnline.value) return 'Offline Mode';
    if (isSyncing.value) return 'Syncing...';
    if (pendingOperations.value > 0) return `${pendingOperations.value} pending`;
    return 'All Synced';
});

// Load entity
async function loadEntity() {
    isLoading.value = true;
    try {
        entity.value = await fetchEntity(entityType.value, entityId.value);
        editedTitle.value = entity.value.title || '';
        
        // Get sync status
        syncStatus.value = await getSyncStatus(entityId.value);
        
        console.log('Entity loaded:', entity.value);
    } catch (error) {
        console.error('Failed to load entity:', error);
        alert('Failed to load entity: ' + error.message);
    } finally {
        isLoading.value = false;
    }
}

// Save changes
async function saveChanges() {
    try {
        const updated = {
            ...entity.value,
            title: editedTitle.value
        };
        
        await saveEntity(updated);
        entity.value = updated;
        editMode.value = false;
        
        // Update sync status
        syncStatus.value = await getSyncStatus(entityId.value);
        
        alert('✅ Saved locally! Will sync when online.');
    } catch (error) {
        console.error('Save failed:', error);
        alert('Failed to save: ' + error.message);
    }
}

// Manual sync trigger
async function manualSync() {
    try {
        await processSyncQueue();
        syncStatus.value = await getSyncStatus(entityId.value);
        alert('✅ Sync completed!');
    } catch (error) {
        console.error('Sync failed:', error);
        alert('Sync failed: ' + error.message);
    }
}

// Load database stats
async function loadDbStats() {
    dbStats.value = await getDatabaseStats();
}

// Pick random entity from server
async function pickRandom() {
    isLoading.value = true;
    try {
        const response = await axios.get(`/api/entities/random/${entityType.value}`);
        entityId.value = response.data.id;
        await loadEntity();
    } catch (error) {
        console.error('Failed to get random entity:', error);
        alert('Failed to pick random: ' + (error.response?.data?.error || error.message));
    } finally {
        isLoading.value = false;
    }
}

// Simulate offline mode
function toggleOffline() {
    if (navigator.onLine) {
        alert('To test offline mode, use DevTools > Network > Offline');
    }
}

// Clear cache
async function clearCache() {
    if (confirm('Clear all local data?')) {
        await db.entities.clear();
        await db.sync_registry.clear();
        entity.value = null;
        await loadDbStats();
        alert('Cache cleared!');
    }
}

onMounted(() => {
    loadDbStats();
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-8">
        <!-- Header -->
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <h1 class="text-4xl font-bold mb-2 bg-gradient-to-r from-emerald-400 to-lime-400 bg-clip-text text-transparent">
                    🔄 Local-First Sync POC
                </h1>
                <p class="text-slate-400">
                    Proof of Concept: Offline-capable Entity Management
                </p>
            </div>

            <!-- Network Status Banner -->
            <div 
                class="mb-6 p-4 rounded-xl border-2 transition-all"
                :class="isOnline 
                    ? 'bg-emerald-500/10 border-emerald-500/30' 
                    : 'bg-red-500/10 border-red-500/30'"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ syncStatusIcon }}</span>
                        <div>
                            <div class="font-semibold">{{ syncStatusText }}</div>
                            <div class="text-sm text-slate-400">
                                {{ isOnline ? 'Connected to server' : 'Working offline' }}
                            </div>
                        </div>
                    </div>
                    
                    <button 
                        v-if="isOnline && pendingOperations > 0"
                        @click="manualSync"
                        class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 rounded-lg transition-colors"
                    >
                        Sync Now
                    </button>
                </div>
            </div>

            <!-- Database Stats -->
            <div v-if="dbStats" class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700">
                    <div class="text-2xl font-bold text-emerald-400">{{ dbStats.entities }}</div>
                    <div class="text-sm text-slate-400">Cached Entities</div>
                </div>
                <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700">
                    <div class="text-2xl font-bold text-lime-400">{{ dbStats.contentBlocks }}</div>
                    <div class="text-sm text-slate-400">Content Blocks</div>
                </div>
                <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700">
                    <div class="text-2xl font-bold text-yellow-400">{{ dbStats.pendingSync }}</div>
                    <div class="text-sm text-slate-400">Pending Sync</div>
                </div>
                <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700">
                    <button 
                        @click="loadDbStats"
                        class="text-sm text-blue-400 hover:text-blue-300"
                    >
                        🔄 Refresh
                    </button>
                </div>
            </div>

            <!-- Entity Loader -->
            <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700 mb-6">
                <h2 class="text-xl font-semibold mb-4">Load Entity</h2>
                <div class="flex gap-3 mb-3">
                    <select 
                        v-model="entityType"
                        class="px-4 py-2 bg-slate-900 border border-slate-600 rounded-lg focus:border-emerald-500 focus:outline-none"
                    >
                        <option value="book">📚 Book</option>
                        <option value="audio">🎵 Audio</option>
                        <option value="video">🎬 Video</option>
                        <option value="manuscript">📜 Manuscript</option>
                    </select>
                    <input 
                        v-model="entityId"
                        type="text"
                        placeholder="Entity UUID"
                        class="flex-1 px-4 py-2 bg-slate-900 border border-slate-600 rounded-lg focus:border-emerald-500 focus:outline-none font-mono text-sm"
                    />
                    <button 
                        @click="pickRandom"
                        class="px-4 py-2 bg-purple-500 hover:bg-purple-600 rounded-lg transition-colors"
                        title="Pick Random Entity"
                    >
                        🎲
                    </button>
                    <button 
                        @click="loadEntity"
                        :disabled="isLoading"
                        class="px-6 py-2 bg-emerald-500 hover:bg-emerald-600 disabled:bg-slate-600 rounded-lg transition-colors"
                    >
                        {{ isLoading ? 'Loading...' : 'Load' }}
                    </button>
                </div>
            </div>

            <!-- Entity Display -->
            <div v-if="entity" class="bg-slate-800/50 p-6 rounded-xl border border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Entity Details</h2>
                    <div class="flex gap-2">
                        <span 
                            v-if="syncStatus"
                            class="px-3 py-1 rounded-full text-sm"
                            :class="syncStatus.isSynced 
                                ? 'bg-emerald-500/20 text-emerald-400' 
                                : 'bg-yellow-500/20 text-yellow-400'"
                        >
                            {{ syncStatus.isSynced ? '✅ Synced' : '📥 Local Only' }}
                        </span>
                    </div>
                </div>

                <!-- View Mode -->
                <div v-if="!editMode" class="space-y-3">
                    <div>
                        <label class="text-sm text-slate-400">ID</label>
                        <div class="text-lg">{{ entity.id }}</div>
                    </div>
                    <div>
                        <label class="text-sm text-slate-400">Title</label>
                        <div class="text-lg">{{ entity.title }}</div>
                    </div>
                    <div>
                        <label class="text-sm text-slate-400">Type</label>
                        <div class="text-lg">{{ entity.type }}</div>
                    </div>
                    <div>
                        <label class="text-sm text-slate-400">Last Updated</label>
                        <div class="text-sm">{{ entity.updated_at }}</div>
                    </div>
                    
                    <button 
                        @click="editMode = true"
                        class="mt-4 px-6 py-2 bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors"
                    >
                        ✏️ Edit
                    </button>
                </div>

                <!-- Edit Mode -->
                <div v-else class="space-y-3">
                    <div>
                        <label class="text-sm text-slate-400">Title</label>
                        <input 
                            v-model="editedTitle"
                            type="text"
                            class="w-full px-4 py-2 bg-slate-900 border border-slate-600 rounded-lg focus:border-emerald-500 focus:outline-none"
                        />
                    </div>
                    
                    <div class="flex gap-3 mt-4">
                        <button 
                            @click="saveChanges"
                            class="px-6 py-2 bg-emerald-500 hover:bg-emerald-600 rounded-lg transition-colors"
                        >
                            💾 Save Locally
                        </button>
                        <button 
                            @click="editMode = false"
                            class="px-6 py-2 bg-slate-600 hover:bg-slate-700 rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Debug Actions -->
            <div class="mt-6 p-4 bg-slate-800/30 rounded-xl border border-slate-700">
                <h3 class="text-sm font-semibold mb-3 text-slate-400">Debug Actions</h3>
                <div class="flex gap-3">
                    <button 
                        @click="toggleOffline"
                        class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-sm transition-colors"
                    >
                        📡 Test Offline
                    </button>
                    <button 
                        @click="clearCache"
                        class="px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg text-sm transition-colors"
                    >
                        🗑️ Clear Cache
                    </button>
                </div>
            </div>

            <!-- Sync Errors -->
            <div v-if="syncErrors.length > 0" class="mt-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
                <h3 class="font-semibold mb-2 text-red-400">Sync Errors</h3>
                <div v-for="(error, index) in syncErrors" :key="index" class="text-sm text-red-300">
                    {{ error.timestamp }}: {{ error.error }}
                </div>
            </div>
        </div>
    </div>
</template>
