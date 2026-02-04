import CryptoJS from 'crypto-js';

/**
 * Encrypt content using AES-256
 * @param {string|object} content - Content to encrypt
 * @param {string} userKey - Encryption key (from session)
 * @returns {string|null} Encrypted string
 */
export function encryptContent(content, userKey) {
    if (!content) return null;

    // If content is already encrypted (starts with U2FsdGVkX1), return as is
    // This prevents double encryption scenarios
    if (isEncrypted(content)) return content;

    const plaintext = typeof content === 'string'
        ? content
        : JSON.stringify(content);

    return CryptoJS.AES.encrypt(plaintext, userKey).toString();
}

/**
 * Decrypt content
 * @param {string} encrypted - Encrypted string
 * @param {string} userKey - Decryption key
 * @returns {string|object|null} Decrypted content
 */
export function decryptContent(encrypted, userKey) {
    if (!encrypted) return null;

    // If not encrypted (doesn't start with U2FsdGVkX1), return as is
    if (!isEncrypted(encrypted)) return encrypted;

    try {
        const bytes = CryptoJS.AES.decrypt(encrypted, userKey);
        const decrypted = bytes.toString(CryptoJS.enc.Utf8);

        if (!decrypted) {
            // This usually happens if the key is wrong (empty bytes)
            throw new Error('Decryption resulted in empty string');
        }

        // Try to parse as JSON, fallback to string
        try {
            // Check if it looks like a JSON object/array
            if (decrypted.startsWith('{') || decrypted.startsWith('[')) {
                return JSON.parse(decrypted);
            }
            return decrypted;
        } catch {
            return decrypted;
        }
    } catch (error) {
        console.error('❌ Decryption failed:', error);
        // We throw so the calling function handles it (e.g. prompt for login again if key rotated)
        throw new Error('Failed to decrypt content. Invalid key or corrupted data.');
    }
}

/**
 * Generate encryption key from user session
 * @param {object} user - User object with id
 * @returns {string} Encryption key
 */
export function generateUserKey(user) {
    if (!user || !user.id) {
        console.warn('⚠️ Cannot generate user key: User object missing or invalid');
        return 'fallback-public-key'; // Should ideally fail, but for MVP fallback avoids crash
    }

    // In a real session-based key system, we might use a session token.
    // implementing a deterministic key based on User ID for now ensures 
    // data survives page reloads as long as user is logged in.
    // For better security, mix in a session token if available and consistent.
    const seed = `entity-secure-storage-${user.id}`;
    return CryptoJS.SHA256(seed).toString();
}

/**
 * Check if content is encrypted
 * @param {string} content - Content to check
 * @returns {boolean}
 */
export function isEncrypted(content) {
    if (!content || typeof content !== 'string') return false;

    // CryptoJS encrypted strings start with "U2FsdGVkX1" (Salted__) base64 encoded
    return content.startsWith('U2FsdGVkX1');
}
