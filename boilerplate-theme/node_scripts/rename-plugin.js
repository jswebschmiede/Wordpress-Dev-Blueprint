/**
 * Replaces boilerplate plugin placeholders and renames the plugin directory/files.
 */

import { existsSync, readFileSync, renameSync, writeFileSync } from 'fs';
import { basename, dirname, join } from 'path';
import { fileURLToPath } from 'url';
import {
    applyReplacements,
    collectTextFiles,
    defaultSkippedDirectories,
    slugToConstantPrefix,
    slugToNamespace,
    slugToTitle,
    validateSlug,
} from './rename-shared.js';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const args = process.argv.slice(2);
const isDryRun = args.includes('--dry-run');
const oldSlugFlagIndex = args.indexOf('--old-slug');
const oldSlug =
    oldSlugFlagIndex !== -1 && args[oldSlugFlagIndex + 1] && !args[oldSlugFlagIndex + 1].startsWith('--')
        ? args[oldSlugFlagIndex + 1]
        : 'boilerplate-plugin';
const slug = args.find((arg) => !arg.startsWith('--') && arg !== oldSlug);

const usageMessage =
    'Usage: node node_scripts/rename-plugin.js <slug> [--old-slug boilerplate-plugin] [--dry-run]';

/**
 * Gets plugin-specific replacement values.
 *
 * @param {string} slugValue - Plugin slug.
 * @returns {Record<string, string>} Replacement map.
 */
function getReplacements(slugValue) {
    const namespace = slugToNamespace(slugValue);
    const title = slugToTitle(slugValue);
    const constantPrefix = slugToConstantPrefix(slugValue);

    return {
        'Boilerplate Plugin': title,
        BOILERPLATE_PLUGIN_: constantPrefix,
        BoilerplatePlugin: namespace,
        'boilerplate-plugin': slugValue,
        boilerplate_plugin: slugValue.replaceAll('-', '_'),
    };
}

/**
 * Root package files that reference the plugin slug outside plugins/.
 *
 * @returns {string[]} Absolute file paths.
 */
function getRootReferenceFiles() {
    const candidates = [
        join(rootDir, 'package.json'),
        join(rootDir, 'composer.json'),
        join(rootDir, 'phpcs.xml'),
        join(rootDir, 'rector.php'),
        join(rootDir, 'README.md'),
        join(rootDir, 'docs', 'DEVELOPMENT.md'),
    ];

    return candidates.filter((file) => existsSync(file));
}

/**
 * Renames plugin files and directory after content replacements.
 *
 * @param {string} oldPluginDir - Current plugin directory.
 * @param {string} newPluginDir - Target plugin directory.
 * @param {string} namespace - Main plugin class basename.
 * @returns {void}
 */
function renamePluginPaths(oldPluginDir, newPluginDir, namespace) {
    const oldMainFile = join(oldPluginDir, `${oldSlug}.php`);
    const newMainFile = join(oldPluginDir, `${slug}.php`);
    const oldMainClassFile = join(oldPluginDir, 'includes', 'BoilerplatePlugin.php');
    const newMainClassFile = join(oldPluginDir, 'includes', `${namespace}.php`);

    if (existsSync(oldMainClassFile)) {
        renameSync(oldMainClassFile, newMainClassFile);
        console.log(`Renamed class file: ${basename(oldMainClassFile)} -> ${basename(newMainClassFile)}`);
    }

    if (existsSync(oldMainFile)) {
        renameSync(oldMainFile, newMainFile);
        console.log(`Renamed bootstrap file: ${basename(oldMainFile)} -> ${basename(newMainFile)}`);
    }

    if (oldPluginDir !== newPluginDir) {
        renameSync(oldPluginDir, newPluginDir);
        console.log(`Renamed plugin directory: ${basename(oldPluginDir)} -> ${basename(newPluginDir)}`);
    }
}

validateSlug(slug, usageMessage);

if (slug === oldSlug) {
    console.error('New slug must differ from the old slug.');
    process.exit(1);
}

const oldPluginDir = join(rootDir, 'plugins', oldSlug);
const newPluginDir = join(rootDir, 'plugins', slug);

if (!existsSync(oldPluginDir)) {
    console.error(`Plugin directory not found: ${oldPluginDir}`);
    process.exit(1);
}

if (existsSync(newPluginDir) && oldPluginDir !== newPluginDir) {
    console.error(`Target plugin directory already exists: ${newPluginDir}`);
    process.exit(1);
}

const replacements = getReplacements(slug);
const files = [
    ...new Set([...collectTextFiles(oldPluginDir), ...getRootReferenceFiles()]),
];
const changedFiles = [];

console.log('Plugin rename values:');
console.log(`Old Slug: ${oldSlug}`);
console.log(`New Slug/Text Domain: ${slug}`);
console.log(`PHP Namespace Segment: ${replacements.BoilerplatePlugin}`);
console.log(`Plugin Name: ${replacements['Boilerplate Plugin']}`);
console.log(`Constant Prefix: ${replacements.BOILERPLATE_PLUGIN_}`);

for (const file of files) {
    const originalContent = readFileSync(file, 'utf8');
    const updatedContent = applyReplacements(originalContent, replacements);

    if (originalContent === updatedContent) {
        continue;
    }

    changedFiles.push(file);

    if (!isDryRun) {
        writeFileSync(file, updatedContent, 'utf8');
    }
}

if (!isDryRun) {
    renamePluginPaths(oldPluginDir, newPluginDir, replacements.BoilerplatePlugin);
} else {
    console.log('Dry run only. Directory and bootstrap files were not renamed.');
}

if (isDryRun) {
    console.log('Dry run only. No files were changed.');
}

console.log(`Affected files: ${changedFiles.length}`);
for (const file of changedFiles) {
    console.log(file);
}

console.log('');
console.log('Next steps:');
console.log(`- Run composer install --working-dir=plugins/${slug} to regenerate vendor-prefixed/`);
console.log('- Update wp-content symlink or deployment path to the new plugin slug');
