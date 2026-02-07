/**
 * Composable for Soft Locking (Section-level editing warnings)
 */

import { ref } from 'vue';
import axios from 'axios';

export function useSoftLock() {
    const lockedSections = ref(new Map());
    const checkInterval = ref(null);

    /**
     * Check if a section is locked by another user
     */
    const checkSectionLock = async (entityType, entitySlug, sectionId) => {
        try {
            const response = await axios.get(`/api/presence/${entityType}/${entitySlug}/section-lock`, {
                params: { section_id: sectionId }
            });

            const lockInfo = {
                locked: response.data.locked,
                lockedBy: response.data.locked_by,
                isCurrentUser: response.data.is_current_user
            };

            lockedSections.value.set(sectionId, lockInfo);
            return lockInfo;
        } catch (error) {
            console.warn('Failed to check section lock:', error);
            return { locked: false, lockedBy: null, isCurrentUser: false };
        }
    };

    /**
     * Start monitoring a section for locks
     */
    const startMonitoring = (entityType, entitySlug, sectionIds, intervalMs = 5000) => {
        stopMonitoring(); // Clear any existing interval

        checkInterval.value = setInterval(async () => {
            for (const sectionId of sectionIds) {
                await checkSectionLock(entityType, entitySlug, sectionId);
            }
        }, intervalMs);
    };

    /**
     * Stop monitoring
     */
    const stopMonitoring = () => {
        if (checkInterval.value) {
            clearInterval(checkInterval.value);
            checkInterval.value = null;
        }
    };

    /**
     * Get lock status for a specific section
     */
    const getSectionLockStatus = (sectionId) => {
        return lockedSections.value.get(sectionId) || { locked: false, lockedBy: null, isCurrentUser: false };
    };

    /**
     * Check if current user should see a warning for a section
     */
    const shouldShowWarning = (sectionId) => {
        const lock = getSectionLockStatus(sectionId);
        return lock.locked && !lock.isCurrentUser;
    };

    return {
        lockedSections,
        checkSectionLock,
        startMonitoring,
        stopMonitoring,
        getSectionLockStatus,
        shouldShowWarning
    };
}
