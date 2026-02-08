import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, '..');
const buildDir = path.join(rootDir, 'public/build');
const publicDir = path.join(rootDir, 'public');

/**
 * PWA Asset Relocator
 * Ensures sw.js and manifest.webmanifest are at the root for proper scoping.
 */
function relocate() {
    console.log('🛡️  [PWA Post-Build] Starting asset relocation...');

    const assets = ['sw.js', 'manifest.webmanifest'];

    assets.forEach(file => {
        const src = path.join(buildDir, file);
        const dest = path.join(publicDir, file);

        if (fs.existsSync(src)) {
            try {
                fs.copyFileSync(src, dest);
                console.log(`✅ [PWA Post-Build] Moved ${file} to public/`);
            } catch (err) {
                console.error(`❌ [PWA Post-Build] Failed to move ${file}:`, err.message);
            }
        } else {
            console.warn(`⚠️  [PWA Post-Build] Source not found: ${file} (Skipping)`);
        }
    });

    console.log('✨ [PWA Post-Build] Cleanup complete.');
}

relocate();
