/**
 * Builds optional frontend view scripts for blocks.
 */

import * as esbuild from 'esbuild';
import { existsSync, readdirSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const blocksDir = join(rootDir, 'blocks');
const outDir = join(rootDir, 'theme', 'js', 'blocks-view');
const isProduction = process.argv.includes('--minify');
const isWatch = process.argv.includes('--watch');

/**
 * Finds block view script entries named view.js.
 *
 * @returns {string[]} Absolute entry paths.
 */
function findViewEntries() {
    if (!existsSync(blocksDir)) {
        return [];
    }

    return readdirSync(blocksDir, { withFileTypes: true })
        .filter((entry) => entry.isDirectory() && !entry.name.startsWith('_'))
        .map((entry) => join(blocksDir, entry.name, 'view.js'))
        .filter((entryPath) => existsSync(entryPath));
}

const entryPoints = findViewEntries();

if (entryPoints.length === 0) {
    console.log('No block view scripts found, skipping build.');
    process.exit(0);
}

/**
 * Build configuration.
 *
 * @type {esbuild.BuildOptions}
 */
const buildOptions = {
    entryPoints,
    bundle: true,
    outdir: outDir,
    outbase: blocksDir,
    entryNames: '[dir]',
    format: 'iife',
    target: 'esnext',
    minify: isProduction,
    sourcemap: !isProduction,
    loader: { '.js': 'jsx' },
    logLevel: 'info',
};

/**
 * Builds or watches block view scripts.
 *
 * @returns {Promise<void>}
 */
async function build() {
    try {
        if (isWatch) {
            const context = await esbuild.context(buildOptions);
            await context.watch();
            console.log('Watching boilerplate block view scripts...');
            return;
        }

        await esbuild.build(buildOptions);
        console.log(`Boilerplate block view scripts built${isProduction ? ' for production' : ''}.`);
        process.exit(0);
    } catch (error) {
        console.error('Boilerplate block view build failed:', error);
        process.exit(1);
    }
}

build();
