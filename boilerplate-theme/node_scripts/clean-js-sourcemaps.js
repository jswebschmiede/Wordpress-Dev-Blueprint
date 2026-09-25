/**
 * Removes *.map files from bundled JS output directories.
 *
 * Usage:
 *   node clean-js-sourcemaps.js                  — theme/js (recursive)
 *   node clean-js-sourcemaps.js <plugin-name>    — plugins/<plugin-name>/build (recursive)
 */

import { readdir, unlink } from 'fs/promises';
import { dirname, join } from 'path';
import { existsSync } from 'fs';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');

const args = process.argv.slice(2);
const pluginName = args.find((arg) => !arg.startsWith('--'));

/**
 * Recursively collects absolute paths of files ending in .map
 * @param {string} dir - Directory to scan
 * @returns {Promise<string[]>}
 */
async function collectMapFiles(dir) {
    const out = [];
    const entries = await readdir(dir, { withFileTypes: true });
    for (const entry of entries) {
        const full = join(dir, entry.name);
        if (entry.isDirectory()) {
            out.push(...(await collectMapFiles(full)));
        } else if (entry.isFile() && entry.name.endsWith('.map')) {
            out.push(full);
        }
    }
    return out;
}

/**
 * Deletes a file with retries for transient lock errors (EBUSY, EPERM).
 * @param {string} file - Absolute file path
 * @param {number} [maxAttempts=5] - Maximum delete attempts
 * @returns {Promise<void>}
 */
async function unlinkWithRetry(file, maxAttempts = 5) {
    for (let attempt = 1; attempt <= maxAttempts; attempt++) {
        try {
            await unlink(file);
            return;
        } catch (err) {
            const retryable =
                err &&
                typeof err === 'object' &&
                'code' in err &&
                (err.code === 'EBUSY' || err.code === 'EPERM');

            if (!retryable || attempt === maxAttempts) {
                throw err;
            }

            await new Promise((resolve) => setTimeout(resolve, 50 * attempt));
        }
    }
}

/**
 * Deletes all *.map files under the given directory and logs a short summary.
 * @param {string} targetDir - Absolute directory path
 * @param {string} label - Human-readable path for log output
 * @returns {Promise<void>}
 */
async function cleanDir(targetDir, label) {
    if (!existsSync(targetDir)) {
        console.log(`clean-js-sourcemaps: skip (missing ${label})`);
        return;
    }

    const maps = await collectMapFiles(targetDir);
    for (const file of maps) {
        await unlinkWithRetry(file);
    }

    if (maps.length > 0) {
        console.log(`clean-js-sourcemaps: removed ${maps.length} file(s) under ${label}`);
    }
}

/**
 * Resolves target from CLI and runs cleanup
 * @returns {Promise<void>}
 */
async function main() {
    if (pluginName) {
        const pluginDir = join(rootDir, 'plugins', pluginName);
        if (!existsSync(pluginDir)) {
            console.error(`❌ Error: Plugin directory does not exist: ${pluginDir}`);
            process.exit(1);
        }
        const buildDir = join(pluginDir, 'build');
        await cleanDir(buildDir, `plugins/${pluginName}/build`);
        return;
    }

    const themeJsDir = join(rootDir, 'theme', 'js');
    await cleanDir(themeJsDir, 'theme/js');
}

main().catch((err) => {
    console.error('clean-js-sourcemaps failed:', err);
    process.exit(1);
});
