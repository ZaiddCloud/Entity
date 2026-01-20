
/**
 * Composable for Manuscripter Logic
 * Handles purely functional logic like parsing specific URL formats.
 */
export function useManuscript() {

    /**
     * Safely extracts a clean filename from a URL.
     * Prevents crashes on malformed URLs.
     * @param {String} url 
     * @returns {String}
     */
    const parseFilename = (url) => {
        if (!url || typeof url !== 'string') return 'N/A';

        try {
            const parts = url.split('/');
            const file = parts.pop();
            if (!file) return 'N/A';

            const fileNameParts = file.split('.');
            return fileNameParts[0] || 'N/A';
        } catch (e) {
            console.error('Error parsing manuscript filename:', e);
            return 'N/A';
        }
    };

    /**
     * Calculates new panel widths during a resize event.
     * @param {Number} movementX - Mouse movement X
     * @param {Number} containerWidth - Total container width
     * @param {Number} currentWidth - Current panel width %
     * @param {Number} nextWidth - Next panel width %
     * @returns {Object|null} { newCurrent, newNext } or null if invalid
     */
    const calculateResize = (movementX, containerWidth, currentWidth, nextWidth) => {
        // In RTL, moving Left (negative X) means:
        // The panel to the RIGHT (index) grows.
        // The panel to the LEFT (index+1) shrinks.
        // So: Growth = -movementX
        const deltaPercent = (-movementX / containerWidth) * 100;

        const newCurrent = currentWidth + deltaPercent;
        const newNext = nextWidth - deltaPercent;

        if (newCurrent < 10 || newNext < 10) return null;

        return { newCurrent, newNext };
    };

    return {
        parseFilename,
        calculateResize
    };
}
