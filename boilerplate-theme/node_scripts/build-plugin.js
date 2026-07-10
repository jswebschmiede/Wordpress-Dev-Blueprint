/**
 * Builds JavaScript entries for plugins inside plugins/.
 */

import * as esbuild from 'esbuild';
import pkg from 'esbuild-plugin-external-global';
import { existsSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const { externalGlobalPlugin } = pkg;
const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const args = process.argv.slice(2);
const pluginName = args.find((arg) => !arg.startsWith('--'));
const isProduction = args.includes('--minify');
const isWatch = args.includes('--watch');

if (!pluginName) {
    console.error('Usage: node node_scripts/build-plugin.js <plugin-name> [--watch] [--minify]');
    process.exit(1);
}

const pluginDir = join(rootDir, 'plugins', pluginName);

if (!existsSync(pluginDir)) {
    console.error(`Plugin directory does not exist: ${pluginDir}`);
    process.exit(1);
}

/**
 * WordPress packages and jQuery mapped to global browser variables.
 *
 * @type {Record<string, string>}
 */
const wpGlobals = {
    '@wordpress/element': 'window.wp.element',
    '@wordpress/i18n': 'window.wp.i18n',
    '@wordpress/data': 'window.wp.data',
    '@wordpress/hooks': 'window.wp.hooks',
    '@wordpress/dom-ready': 'window.wp.domReady',
    '@wordpress/api-fetch': 'window.wp.apiFetch',
    jquery: 'window.jQuery',
    react: 'window.React',
    'react-dom': 'window.ReactDOM',
};

/**
 * Resolves a plugin JavaScript entry path.
 *
 * @param {'admin' | 'frontend'} context - Asset context.
 * @param {string} fileName - Entry file name.
 * @returns {string} Absolute entry path.
 */
function resolvePluginEntry(context, fileName) {
    return join(pluginDir, 'assets', context, 'js', fileName);
}

const entryPoints = [
    resolvePluginEntry('admin', 'dashboard.js'),
    resolvePluginEntry('frontend', 'frontend.js'),
].filter((path) => existsSync(path));

if (entryPoints.length === 0) {
    console.log(`No plugin JS entries found for "${pluginName}", skipping build.`);
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
    outdir: join(pluginDir, 'build'),
    entryNames: '[name]',
    format: 'iife',
    target: 'esnext',
    minify: isProduction,
    sourcemap: !isProduction,
    loader: { '.js': 'jsx' },
    plugins: [externalGlobalPlugin(wpGlobals)],
    logLevel: 'info',
};

/**
 * Builds or watches plugin JavaScript entries.
 *
 * @returns {Promise<void>}
 */
async function build() {
    try {
        if (isWatch) {
            const context = await esbuild.context(buildOptions);
            await context.watch();
            console.log(`Watching plugin "${pluginName}" assets...`);
            return;
        }

        await esbuild.build(buildOptions);
        console.log(`Plugin "${pluginName}" assets built${isProduction ? ' for production' : ''}.`);
        process.exit(0);
    } catch (error) {
        console.error(`Plugin "${pluginName}" build failed:`, error);
        process.exit(1);
    }
}

build();
