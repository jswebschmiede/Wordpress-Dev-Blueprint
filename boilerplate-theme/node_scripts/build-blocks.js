/**
 * Builds the custom Gutenberg block editor bundle.
 */

import * as esbuild from 'esbuild';
import pkg from 'esbuild-plugin-external-global';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const { externalGlobalPlugin } = pkg;
const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const isProduction = process.argv.includes('--minify');
const isWatch = process.argv.includes('--watch');

/**
 * WordPress packages mapped to global editor variables.
 *
 * @type {Record<string, string>}
 */
const wpGlobals = {
    '@wordpress/blocks': 'window.wp.blocks',
    '@wordpress/element': 'window.wp.element',
    '@wordpress/block-editor': 'window.wp.blockEditor',
    '@wordpress/components': 'window.wp.components',
    '@wordpress/i18n': 'window.wp.i18n',
    '@wordpress/editor': 'window.wp.editor',
    '@wordpress/data': 'window.wp.data',
    '@wordpress/compose': 'window.wp.compose',
    '@wordpress/hooks': 'window.wp.hooks',
    '@wordpress/primitives': 'window.wp.primitives',
    '@wordpress/dom-ready': 'window.wp.domReady',
    '@wordpress/icons': 'window.wp.icons',
    '@wordpress/api-fetch': 'window.wp.apiFetch',
    '@wordpress/core-data': 'window.wp.coreData',
    '@wordpress/html-entities': 'window.wp.htmlEntities',
    '@wordpress/server-side-render': 'window.wp.serverSideRender',
    react: 'window.React',
    'react-dom': 'window.ReactDOM',
};

/**
 * Build configuration.
 *
 * @type {esbuild.BuildOptions}
 */
const buildOptions = {
    entryPoints: [join(rootDir, 'javascript', 'blocks.js')],
    bundle: true,
    outfile: join(rootDir, 'theme', 'js', 'blocks.min.js'),
    format: 'iife',
    target: 'esnext',
    minify: isProduction,
    sourcemap: !isProduction,
    loader: { '.js': 'jsx' },
    plugins: [externalGlobalPlugin(wpGlobals)],
    logLevel: 'info',
};

/**
 * Builds or watches the block editor bundle.
 *
 * @returns {Promise<void>}
 */
async function build() {
    try {
        if (isWatch) {
            const context = await esbuild.context(buildOptions);
            await context.watch();
            console.log('Watching boilerplate blocks...');
            return;
        }

        await esbuild.build(buildOptions);
        console.log(`Boilerplate blocks built${isProduction ? ' for production' : ''}.`);
        process.exit(0);
    } catch (error) {
        console.error('Boilerplate block build failed:', error);
        process.exit(1);
    }
}

build();
