/**
 * Editor Settings Configuration
 */

export const editorSettings = {
    // Auto-save settings
    autoSave: {
        enabled: true,
        interval: 30000, // 30 seconds
    },

    // Editor appearance
    appearance: {
        theme: 'light', // 'light' or 'dark'
        fontSize: 18,
        lineHeight: 2,
        fontFamily: 'Amiri',
    },

    // RTL settings
    rtl: {
        enabled: true,
        defaultAlignment: 'right',
    },

    // Toolbar settings
    toolbar: {
        pinned: false,
        autoHide: true,
        autoHideDelay: 2500,
    },

    // Content settings
    content: {
        maxWidth: 850,
        padding: 64,
    },

    // Export settings
    export: {
        includeFootnotes: true,
        includeImages: true,
        includeMetadata: true,
    },

    // Keyboard shortcuts
    shortcuts: {
        enabled: true,
    },

    // Spell check
    spellCheck: {
        enabled: false,
    },
}

export default editorSettings
