import { defineStore } from 'pinia'

export const useManuscriptStore = defineStore('manuscript', {
    state: () => ({
        // Data
        manuscript: null,
        siblings: [],

        // Navigation State
        shotNumber: 1,
        activeSlug: null,

        // View State
        viewMode: 'list', // 'list', 'grid', 'default'
        isCompareMode: false,
        selectedVersionIds: [],
        panelWidths: [],

        // Viewer State (Pan/Zoom)
        zoomLevel: 1.0,
        isPanning: false,

        // Responsive
        windowWidth: 1024
    }),

    getters: {
        // Computed Versions List (Merges Main + Siblings)
        allVersions: (state) => {
            if (!state.manuscript) return [];

            return [
                {
                    id: state.manuscript.id,
                    name: state.manuscript.catalog_number || state.manuscript.code || 'الأصل',
                    manuscript: state.manuscript,
                    pages: state.manuscript.children || []
                },
                ...(state.siblings || []).map(s => ({
                    id: s.id,
                    name: s.catalog_number || s.code || s.title || 'نسخة أخرى',
                    manuscript: s,
                    pages: s.children || []
                }))
            ];
        },

        // Filtered Versions for Display
        displayedVersions() {
            return this.allVersions.filter(v => this.selectedVersionIds.includes(v.id));
        },

        // Max pages across selected versions
        totalPages() {
            if (this.displayedVersions.length === 0) return 0;
            return Math.max(...this.displayedVersions.map(v => v.pages.length));
        },

        // Get URL for current shot of a specific version
        getPageUrl: (state) => (shotIndex, version) => {
            if (!version || !version.pages || version.pages.length === 0) return '';
            const page = version.pages[shotIndex - 1];
            return page ? page.image_url : '';
        }
    },

    actions: {
        setResource(manuscript, siblings = [], initialSlug = null) {
            this.manuscript = manuscript;
            this.siblings = siblings;
            this.activeSlug = initialSlug;

            // Initialize selection (Default to Main)
            if (this.selectedVersionIds.length === 0 && manuscript) {
                this.selectedVersionIds = [manuscript.id];
            }

            // Set initial shot if slug provided
            if (initialSlug && this.allVersions[0]?.pages) {
                const pageIndex = this.allVersions[0].pages.findIndex(p => p.slug === initialSlug);
                if (pageIndex !== -1) {
                    this.shotNumber = pageIndex + 1;
                }
            }
        },

        setShot(number) {
            if (number < 1 || number > this.totalPages) return;
            this.shotNumber = number;
        },

        // Toggle Version (Compare Mode Logic)
        toggleVersionSelection(versionId) {
            if (!this.isCompareMode) {
                // Single Mode: Direct switch
                this.selectedVersionIds = [versionId];
            } else {
                // Compare Mode: Toggle
                const index = this.selectedVersionIds.indexOf(versionId);
                if (index === -1) {
                    this.selectedVersionIds.push(versionId);
                } else if (this.selectedVersionIds.length > 1) {
                    // Prevent deselecting last one
                    this.selectedVersionIds.splice(index, 1);
                }
            }

            // Reset widths distribution
            this.distributeWidths();
        },

        distributeWidths() {
            const count = this.displayedVersions.length;
            if (count > 0) {
                this.panelWidths = new Array(count).fill(100 / count);
            }
        },

        setCompareMode(value) {
            this.isCompareMode = value;
            if (!value) {
                // Determine which version to keep (usually the first selected or main)
                const toKeep = this.selectedVersionIds[0] || this.manuscript?.id;
                this.selectedVersionIds = [toKeep];
            } else {
                this.distributeWidths();
            }
        }
    }
})
