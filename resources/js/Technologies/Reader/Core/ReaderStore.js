import { defineStore } from 'pinia';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

export const useReaderStore = defineStore('reader', {
    state: () => ({
        currentNode: null,
        entity: null,
        type: null,
        hierarchy: [],
        fontSize: parseInt(localStorage.getItem('reader_font_size')) || 18,
        theme: localStorage.getItem('reader_theme') || 'light',
        isFullscreen: false,
        isTocOpen: true,
        isSearchOpen: false,
        isFullView: false,
        activeChildId: null,
        scrollProgress: 0,
        bookmarks: JSON.parse(localStorage.getItem('reader_bookmarks')) || [],
        isLoading: false,
    }),

    getters: {
        activeNodeIndex: (state) => {
            if (!state.currentNode || !state.hierarchy.length) return -1;
            return state.hierarchy.findIndex(node => (node._id || node.id) === state.activeChildId);
        },
        prevNode: (state) => {
            const index = state.activeNodeIndex;
            return index > 0 ? state.hierarchy[index - 1] : null;
        },
        nextNode: (state) => {
            const index = state.activeNodeIndex;
            return index >= 0 && index < state.hierarchy.length - 1 ? state.hierarchy[index + 1] : null;
        }
    },

    actions: {
        init(props) {
            this.entity = props.entity;
            this.type = props.type;
            this.hierarchy = props.hierarchy || [];
            this.isFullView = props.isFullView || false;
            this.activeChildId = props.activeChildId;

            // Find current node in hierarchy
            const node = this.activeChildId
                ? this.hierarchy.find(n => (n._id || n.id) === this.activeChildId)
                : this.hierarchy[0];

            this.currentNode = node ? {
                id: node._id || node.id,
                slug: node.slug,
                title: node.title,
            } : null;
        },

        navigate(id = null) {
            this.isLoading = true;
            router.visit(route('reader.show', {
                type: this.type,
                slug: this.entity.slug,
                childId: id
            }), {
                preserveState: true,
                onSuccess: () => {
                    this.isLoading = false;
                }
            });
        },

        setFontSize(size) {
            this.fontSize = Math.min(Math.max(size, 12), 32);
            localStorage.setItem('reader_font_size', this.fontSize);
        },

        setTheme(theme) {
            this.theme = theme;
            localStorage.setItem('reader_theme', theme);
        },

        toggleMedia() {
            this.isMediaVisible = !this.isMediaVisible;
        },

        toggleToc() {
            this.isTocOpen = !this.isTocOpen;
            if (this.isTocOpen) this.isSearchOpen = false;
        },

        toggleSearch() {
            this.isSearchOpen = !this.isSearchOpen;
            if (this.isSearchOpen) this.isTocOpen = false;
        },

        toggleFullscreen() {
            this.isFullscreen = !this.isFullscreen;
        },

        async savePosition(nodeSlug, scrollOffset, timestamp = null) {
            try {
                await axios.post(route('reader.save-position'), {
                    entity_id: this.entity.id,
                    entity_type: this.entity.type || this.entity.get_morph_class, // depends on what's passed
                    node_slug: nodeSlug,
                    scroll_offset: scrollOffset,
                    timestamp: timestamp
                });
            } catch (error) {
                console.error('Failed to save reading position:', error);
            }
        },

        toggleBookmark(slug) {
            const index = this.bookmarks.indexOf(slug);
            if (index === -1) {
                this.bookmarks.push(slug);
            } else {
                this.bookmarks.splice(index, 1);
            }
            localStorage.setItem('reader_bookmarks', JSON.stringify(this.bookmarks));
        }
    }
});
