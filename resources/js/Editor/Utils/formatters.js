/**
 * Formatting utilities
 */

/**
 * Format date to Arabic locale
 */
export function formatDate(date, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        ...options
    }
    return new Date(date).toLocaleDateString('ar-SA', defaultOptions)
}

/**
 * Format time to Arabic locale
 */
export function formatTime(date, options = {}) {
    const defaultOptions = {
        hour: '2-digit',
        minute: '2-digit',
        ...options
    }
    return new Date(date).toLocaleTimeString('ar-SA', defaultOptions)
}

/**
 * Format file size
 */
export function formatFileSize(bytes) {
    if (bytes === 0) return '0 بايت'

    const k = 1024
    const sizes = ['بايت', 'كيلوبايت', 'ميجابايت', 'جيجابايت']
    const i = Math.floor(Math.log(bytes) / Math.log(k))

    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

/**
 * Truncate text
 */
export function truncate(text, length = 100, suffix = '...') {
    if (text.length <= length) return text
    return text.substring(0, length) + suffix
}

/**
 * Sanitize HTML
 */
export function sanitizeHTML(html) {
    const temp = document.createElement('div')
    temp.textContent = html
    return temp.innerHTML
}

/**
 * Strip HTML tags
 */
export function stripHTML(html) {
    const temp = document.createElement('div')
    temp.innerHTML = html
    return temp.textContent || temp.innerText || ''
}

/**
 * Format number to Arabic
 */
export function formatNumber(number) {
    return new Intl.NumberFormat('ar-SA').format(number)
}
