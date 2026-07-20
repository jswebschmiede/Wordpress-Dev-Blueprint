/**
 * Replaces boilerplate plugin placeholders and renames the plugin directory/files.
 */

import { existsSync, readFileSync, renameSync, writeFileSync } from 'fs';
import { basename, dirname, join } from 'path';
import { fileURLToPath } from 'url';
import {
    applyReplacements,
    collectTextFiles,
    companyToNamespace,
    companyToVendorSlug,
    defaultSkippedDirectories,
    slugToConstantPrefix,
    slugToNamespace,
    slugToPlaceholderConstantPrefix,
    slugToTitle,
    validateCompany,
    validateSlug,
} from './rename-shared.js';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const args = process.argv.slice(2);
const isDryRun = args.includes('--dry-run');

const usageMessage = `Usage: node node_scripts/rename-plugin.js <slug> --company <company> [--plugin <plugin-dir>] [--old-slug <placeholder-slug>] [--namespace <Namespace>] [--dry-run]

Required:
  <slug>              New plugin slug / text domain
  --company <name>    Company (kebab-case or PascalCase)

Optional:
  --plugin <dir>      Plugin folder under plugins/ (default: --old-slug)
  --old-slug <slug>   Placeholder slug in file contents (default: boilerplate-plugin)
  --namespace <Name>  PHP namespace segment (default: derived from <slug>)
  --dry-run           Preview changes without writing`;

/**
 * Reads a CLI flag value.
 *
 * @param {string} flag - Flag name including leading dashes.
 * @returns {string | undefined} Flag value when present.
 */
function getFlagValue(flag) {
    const index = args.indexOf(flag);

    if (index === -1) {
        return undefined;
    }

    const value = args[index + 1];

    if (!value || value.startsWith('--')) {
        return undefined;
    }

    return value;
}

/**
 * Collects flag tokens that were consumed so they are not treated as the slug.
 *
 * @returns {Set<string>} Consumed argument tokens.
 */
function getConsumedFlagTokens() {
    const consumed = new Set(['--dry-run']);
    const flagsWithValue = ['--company', '--plugin', '--old-slug', '--namespace'];

    for (const flag of flagsWithValue) {
        const index = args.indexOf(flag);

        if (index === -1) {
            continue;
        }

        consumed.add(flag);

        if (args[index + 1] && !args[index + 1].startsWith('--')) {
            consumed.add(args[index + 1]);
        }
    }

    return consumed;
}

/**
 * Prints a missing-parameter error and exits.
 *
 * @param {string[]} missing - Missing required parameter labels.
 * @returns {never}
 */
function exitMissingParameters(missing) {
    console.error(`Missing required parameters: ${missing.join(', ')}`);
    console.error('');
    console.error(usageMessage);
    process.exit(1);
}

/**
 * Validates an optional explicit PHP namespace segment.
 *
 * @param {string | undefined} value - Requested namespace.
 * @returns {void}
 */
function validateNamespace(value) {
    if (value === undefined) {
        return;
    }

    if (!/^[A-Z][A-Za-z0-9]*$/.test(value)) {
        console.error('Namespace must be PascalCase (e.g. MvgAktuell).');
        console.error('');
        console.error(usageMessage);
        process.exit(1);
    }
}

/**
 * Builds replacement values from old placeholders to the new project names.
 *
 * @param {string} slugValue - New plugin slug.
 * @param {string} oldSlugValue - Placeholder slug currently in files.
 * @param {string} companyValue - Company name.
 * @param {string} namespaceValue - PHP namespace segment.
 * @param {string} pluginDirValue - Current plugin directory name.
 * @returns {Record<string, string>} Replacement map.
 */
function getReplacements(slugValue, oldSlugValue, companyValue, namespaceValue, pluginDirValue) {
    const oldTitle = slugToTitle(oldSlugValue);
    const oldNamespace = slugToNamespace(oldSlugValue);
    const oldConstantPrefix = slugToPlaceholderConstantPrefix(oldSlugValue);
    const title = slugToTitle(slugValue);
    const constantPrefix = slugToConstantPrefix(slugValue);
    const companyNamespace = companyToNamespace(companyValue);
    const companyVendor = companyToVendorSlug(companyValue);

    /** @type {Record<string, string>} */
    const replacements = {
        'https://companyname.example': `https://${companyVendor}.example`,
        [oldTitle]: title,
        [oldConstantPrefix]: constantPrefix,
        [oldNamespace]: namespaceValue,
        [oldSlugValue]: slugValue,
        [oldSlugValue.replaceAll('-', '_')]: slugValue.replaceAll('-', '_'),
        CompanyName: companyNamespace,
        companyname: companyVendor,
    };

    if (pluginDirValue !== oldSlugValue && pluginDirValue !== slugValue) {
        replacements[pluginDirValue] = slugValue;
        replacements[pluginDirValue.replaceAll('-', '_')] = slugValue.replaceAll('-', '_');
    }

    return replacements;
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
 * @param {string} oldSlugValue - Placeholder slug used for the bootstrap PHP filename.
 * @param {string} slugValue - New plugin slug.
 * @param {string} oldNamespace - Previous main class basename.
 * @param {string} newNamespace - New main class basename.
 * @returns {void}
 */
function renamePluginPaths(oldPluginDir, newPluginDir, oldSlugValue, slugValue, oldNamespace, newNamespace) {
    const oldMainFile = join(oldPluginDir, `${oldSlugValue}.php`);
    const newMainFile = join(oldPluginDir, `${slugValue}.php`);
    const oldMainClassFile = join(oldPluginDir, 'includes', `${oldNamespace}.php`);
    const newMainClassFile = join(oldPluginDir, 'includes', `${newNamespace}.php`);

    if (existsSync(oldMainClassFile) && oldMainClassFile !== newMainClassFile) {
        renameSync(oldMainClassFile, newMainClassFile);
        console.log(`Renamed class file: ${basename(oldMainClassFile)} -> ${basename(newMainClassFile)}`);
    }

    if (existsSync(oldMainFile) && oldMainFile !== newMainFile) {
        renameSync(oldMainFile, newMainFile);
        console.log(`Renamed bootstrap file: ${basename(oldMainFile)} -> ${basename(newMainFile)}`);
    }

    if (oldPluginDir !== newPluginDir) {
        renameSync(oldPluginDir, newPluginDir);
        console.log(`Renamed plugin directory: ${basename(oldPluginDir)} -> ${basename(newPluginDir)}`);
    }
}

if (args.length === 0) {
    exitMissingParameters(['<slug>', '--company']);
}

const company = getFlagValue('--company');
const oldSlug = getFlagValue('--old-slug') ?? 'boilerplate-plugin';
const pluginDirName = getFlagValue('--plugin') ?? oldSlug;
const namespaceFlag = getFlagValue('--namespace');
const consumedTokens = getConsumedFlagTokens();
const slug = args.find((arg) => !arg.startsWith('--') && !consumedTokens.has(arg));

/** @type {string[]} */
const missing = [];

if (!slug) {
    missing.push('<slug>');
}

if (!company) {
    missing.push('--company');
}

if (missing.length > 0) {
    exitMissingParameters(missing);
}

validateSlug(slug, usageMessage);
validateCompany(company, usageMessage);
validateNamespace(namespaceFlag);

const namespace = namespaceFlag ?? slugToNamespace(slug);

if (slug === pluginDirName) {
    console.error('New slug must differ from the plugin directory name.');
    process.exit(1);
}

const oldPluginDir = join(rootDir, 'plugins', pluginDirName);
const newPluginDir = join(rootDir, 'plugins', slug);

if (!existsSync(oldPluginDir)) {
    console.error(`Plugin directory not found: ${oldPluginDir}`);
    process.exit(1);
}

if (existsSync(newPluginDir) && oldPluginDir !== newPluginDir) {
    console.error(`Target plugin directory already exists: ${newPluginDir}`);
    process.exit(1);
}

const oldNamespace = slugToNamespace(oldSlug);
const pluginReplacements = getReplacements(slug, oldSlug, company, namespace, pluginDirName);
const rootReplacements = {
    [pluginDirName]: slug,
    [pluginDirName.replaceAll('-', '_')]: slug.replaceAll('-', '_'),
};
const pluginFiles = collectTextFiles(oldPluginDir);
const rootFiles = getRootReferenceFiles();
const changedFiles = [];

console.log('Plugin rename values:');
console.log(`Plugin directory: ${pluginDirName}`);
console.log(`Old placeholder slug: ${oldSlug}`);
console.log(`New Slug/Text Domain: ${slug}`);
console.log(`Company Namespace: ${pluginReplacements.CompanyName}`);
console.log(`Composer Vendor: ${pluginReplacements.companyname}`);
console.log(`PHP Namespace Segment: ${namespace}`);
console.log(`Plugin Name: ${slugToTitle(slug)}`);
console.log(`Constant Prefix: ${slugToConstantPrefix(slug)}`);

/**
 * Writes replacements for a file list.
 *
 * @param {string[]} fileList - Absolute file paths.
 * @param {Record<string, string>} replacements - Replacement map.
 * @returns {void}
 */
function applyReplacementsToFiles(fileList, replacements) {
    for (const file of fileList) {
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
}

applyReplacementsToFiles(pluginFiles, pluginReplacements);
applyReplacementsToFiles(rootFiles, rootReplacements);

if (!isDryRun) {
    renamePluginPaths(oldPluginDir, newPluginDir, oldSlug, slug, oldNamespace, namespace);
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
