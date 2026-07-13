/**
 * Replaces boilerplate theme placeholders with project-specific names.
 */

import { existsSync, readFileSync, readdirSync, statSync, writeFileSync } from 'fs';
import { dirname, extname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const cursorDir = join(rootDir, '..', 'cursor');
const args = process.argv.slice(2);
const slug = args.find((arg) => !arg.startsWith('--'));
const isDryRun = args.includes('--dry-run');
const companyFlagIndex = args.indexOf('--company');
const company = companyFlagIndex !== -1 ? args[companyFlagIndex + 1] : undefined;

const allowedExtensions = new Set(['.php', '.json', '.js', '.css', '.md', '.mdc']);
const skippedDirectories = new Set(['vendor', 'node_modules', '.git']);
const skippedRelativeDirectories = new Set([
    join('theme', 'js'),
]);

/**
 * Normalizes paths to forward slash separators for comparisons.
 *
 * @param {string} value - Path value.
 * @returns {string} Normalized path.
 */
function toPosixPath(value) {
    return value.replaceAll('\\', '/');
}

/**
 * Converts a slug to a human-readable title.
 *
 * @param {string} value - Slug value.
 * @returns {string} Human-readable title.
 */
function slugToTitle(value) {
    return value
        .split('-')
        .filter(Boolean)
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

/**
 * Converts a slug to a compact PascalCase PHP namespace.
 *
 * @param {string} value - Slug value.
 * @returns {string} PHP namespace segment.
 */
function slugToNamespace(value) {
    return value
        .split('-')
        .filter(Boolean)
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join('');
}

/**
 * Converts a slug to an uppercase constant prefix.
 *
 * @param {string} value - Slug value.
 * @returns {string} Constant prefix with trailing underscore.
 */
function slugToConstantPrefix(value) {
    return `${value.replaceAll('-', '').toUpperCase()}_`;
}

const usageMessage = 'Usage: node node_scripts/rename-theme.js <slug> --company <company> [--dry-run]';

/**
 * Validates the requested slug.
 *
 * @param {string | undefined} value - Requested slug.
 * @returns {void}
 */
function validateSlug(value) {
    if (!value) {
        console.error(usageMessage);
        process.exit(1);
    }

    if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(value)) {
        console.error('Slug must contain only lowercase letters, numbers, and single hyphens.');
        process.exit(1);
    }
}

/**
 * Validates the requested company name.
 *
 * @param {string | undefined} value - Requested company name.
 * @returns {void}
 */
function validateCompany(value) {
    if (!value || value.startsWith('--')) {
        console.error('Missing required --company flag.');
        console.error(usageMessage);
        process.exit(1);
    }

    const isKebabCase = /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(value);
    const isPascalCase = /^[A-Z][A-Za-z0-9]*$/.test(value);

    if (!isKebabCase && !isPascalCase) {
        console.error('Company must be kebab-case (e.g. smart-media-24) or PascalCase (e.g. SmartMedia24).');
        process.exit(1);
    }
}

/**
 * Converts a company name to a PascalCase PHP namespace segment.
 *
 * @param {string} value - Company name.
 * @returns {string} PHP namespace segment.
 */
function companyToNamespace(value) {
    if (value.includes('-')) {
        return slugToNamespace(value);
    }

    return value;
}

/**
 * Converts a company name to a lowercase Composer vendor slug.
 *
 * @param {string} value - Company name.
 * @returns {string} Lowercase vendor slug without hyphens.
 */
function companyToVendorSlug(value) {
    return companyToNamespace(value).toLowerCase();
}

/**
 * Gets project-specific replacement values.
 *
 * @param {string} slugValue - Theme slug.
 * @param {string} companyValue - Company name.
 * @returns {Record<string, string>} Replacement map.
 */
function getReplacements(slugValue, companyValue) {
    const namespace = slugToNamespace(slugValue);
    const title = slugToTitle(slugValue);
    const constantPrefix = slugToConstantPrefix(slugValue);
    const companyNamespace = companyToNamespace(companyValue);
    const companyVendor = companyToVendorSlug(companyValue);

    return {
        'https://companyname.example': `https://${companyVendor}.example`,
        'boilerplate-theme': slugValue,
        'boilerplate_theme': slugValue.replaceAll('-', '_'),
        'boilerplate/example-block': `${slugValue}/example-block`,
        CompanyName: companyNamespace,
        companyname: companyVendor,
        BoilerplateTheme: namespace,
        'Boilerplate Theme': title,
        BOILERPLATE_THEME_: constantPrefix,
    };
}

/**
 * Determines whether a path should be skipped.
 *
 * @param {string} absolutePath - Absolute file or directory path.
 * @param {string} relativePath - Relative path from the root directory.
 * @returns {boolean} Whether the path should be skipped.
 */
function shouldSkipPath(absolutePath, relativePath) {
    const baseName = absolutePath.split(/[\\/]/).at(-1);
    const normalizedRelativePath = toPosixPath(relativePath);

    if (baseName && skippedDirectories.has(baseName)) {
        return true;
    }

    return [...skippedRelativeDirectories].some(
        (directory) => {
            const normalizedDirectory = toPosixPath(directory);
            return (
                normalizedRelativePath === normalizedDirectory ||
                normalizedRelativePath.startsWith(`${normalizedDirectory}/`)
            );
        }
    );
}

/**
 * Collects text files that can be renamed safely.
 *
 * @param {string} directory - Directory to scan.
 * @param {string} relativeBase - Relative path from root.
 * @returns {string[]} Absolute file paths.
 */
function collectTextFiles(directory, relativeBase = '') {
    if (!existsSync(directory)) {
        return [];
    }

    const files = [];

    for (const entry of readdirSync(directory)) {
        const absolutePath = join(directory, entry);
        const relativePath = relativeBase ? join(relativeBase, entry) : entry;

        if (shouldSkipPath(absolutePath, relativePath)) {
            continue;
        }

        const stats = statSync(absolutePath);

        if (stats.isDirectory()) {
            files.push(...collectTextFiles(absolutePath, relativePath));
            continue;
        }

        if (stats.isFile() && allowedExtensions.has(extname(entry))) {
            files.push(absolutePath);
        }
    }

    return files;
}

/**
 * Applies all replacements to a string.
 *
 * @param {string} content - Original file content.
 * @param {Record<string, string>} replacements - Replacement map.
 * @returns {string} Updated file content.
 */
function applyReplacements(content, replacements) {
    return Object.entries(replacements).reduce(
        (updatedContent, [search, replacement]) => updatedContent.replaceAll(search, replacement),
        content
    );
}

validateSlug(slug);
validateCompany(company);

const replacements = getReplacements(slug, company);
const files = [...new Set([...collectTextFiles(rootDir), ...collectTextFiles(cursorDir)])];
const changedFiles = [];

console.log('Theme rename values:');
console.log(`Slug/Text Domain: ${slug}`);
console.log(`Company Namespace: ${replacements.CompanyName}`);
console.log(`Composer Vendor: ${replacements.companyname}`);
console.log(`PHP Namespace: ${replacements.BoilerplateTheme}`);
console.log(`Theme Name: ${replacements['Boilerplate Theme']}`);
console.log(`Constant Prefix: ${replacements.BOILERPLATE_THEME_}`);

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

if (isDryRun) {
    console.log('Dry run only. No files were changed.');
}

console.log(`Affected files: ${changedFiles.length}`);
for (const file of changedFiles) {
    console.log(file);
}
