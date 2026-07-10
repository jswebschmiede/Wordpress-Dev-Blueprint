/**
 * Copies block.json files from blocks/ to theme/blocks/.
 */

import { cpSync, existsSync, mkdirSync, readdirSync, statSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const blocksSource = join(rootDir, 'blocks');
const blocksTarget = join(rootDir, 'theme', 'blocks');

/**
 * Copies all block metadata files into the deployable theme directory.
 *
 * @returns {void}
 */
function copyBlockFiles() {
    if (!existsSync(blocksSource)) {
        console.error('Source blocks directory not found:', blocksSource);
        process.exit(1);
    }

    if (!existsSync(blocksTarget)) {
        mkdirSync(blocksTarget, { recursive: true });
    }

    const blockDirs = readdirSync(blocksSource).filter((name) => {
        const fullPath = join(blocksSource, name);
        return statSync(fullPath).isDirectory() && !name.startsWith('_');
    });

    let copiedCount = 0;

    for (const blockName of blockDirs) {
        const sourceBlockJson = join(blocksSource, blockName, 'block.json');
        const targetBlockDir = join(blocksTarget, blockName);
        const targetBlockJson = join(targetBlockDir, 'block.json');

        if (!existsSync(sourceBlockJson)) {
            console.warn(`No block.json found for block: ${blockName}`);
            continue;
        }

        if (!existsSync(targetBlockDir)) {
            mkdirSync(targetBlockDir, { recursive: true });
        }

        cpSync(sourceBlockJson, targetBlockJson);
        copiedCount++;
        console.log(`Copied ${blockName}/block.json`);
    }

    console.log(`Copied ${copiedCount} block.json file(s) to theme/blocks/`);
}

copyBlockFiles();
