import { describe, it, expect } from 'vitest';
import { encryptContent, decryptContent, isEncrypted, generateUserKey } from '../encryptionLayer';

describe('Encryption Layer', () => {
    const userKey = 'test-key-12345';

    describe('encryptContent', () => {
        it('should encrypt string content', () => {
            const original = 'مخطوطة سرية';
            const encrypted = encryptContent(original, userKey);

            expect(encrypted).not.toBe(original);
            expect(isEncrypted(encrypted)).toBe(true);
            // Encrypted string should not contain original text
            expect(encrypted).not.toContain(original);
        });

        it('should encrypt object content', () => {
            const original = { title: 'مخطوطة', pages: 100 };
            const encrypted = encryptContent(original, userKey);

            expect(typeof encrypted).toBe('string');
            expect(isEncrypted(encrypted)).toBe(true);
        });

        it('should return null for null content', () => {
            expect(encryptContent(null, userKey)).toBeNull();
        });

        it('should not double-encrypt', () => {
            const firstPass = encryptContent('test', userKey);
            const secondPass = encryptContent(firstPass, userKey);
            expect(secondPass).toBe(firstPass);
        });
    });

    describe('decryptContent', () => {
        it('should decrypt string content correctly', () => {
            const original = 'مخطوطة سرية';
            const encrypted = encryptContent(original, userKey);
            const decrypted = decryptContent(encrypted, userKey);

            expect(decrypted).toBe(original);
        });

        it('should decrypt object content correctly', () => {
            const original = { title: 'مخطوطة', pages: 100 };
            const encrypted = encryptContent(original, userKey);
            const decrypted = decryptContent(encrypted, userKey);

            expect(decrypted).toEqual(original);
        });

        it('should preserve Arabic text', () => {
            const arabic = 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ';
            const encrypted = encryptContent(arabic, userKey);
            const decrypted = decryptContent(encrypted, userKey);

            expect(decrypted).toBe(arabic);
        });

        it('should throw error with wrong key', () => {
            const encrypted = encryptContent('secret', 'correct-key');

            expect(() => decryptContent(encrypted, 'wrong-key'))
                .toThrow(/decrypt|malformed/i);
            // Error message depends on crypto-js internals usually "Malformed UTF-8 data" or empty string check
        });

        it('should return input if not encrypted', () => {
            const plain = 'not encrypted';
            expect(decryptContent(plain, userKey)).toBe(plain);
        });
    });

    describe('generateUserKey', () => {
        it('should generate consistent keys for same user', () => {
            const user = { id: 1 };
            const key1 = generateUserKey(user);
            const key2 = generateUserKey(user);

            expect(key1).toBe(key2);
        });

        it('should generate different keys for different users', () => {
            const key1 = generateUserKey({ id: 1 });
            const key2 = generateUserKey({ id: 2 });

            expect(key1).not.toBe(key2);
        });
    });
});
