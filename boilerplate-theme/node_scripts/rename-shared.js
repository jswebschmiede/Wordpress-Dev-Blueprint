/**
 * Shared helpers for rename-theme.js and rename-plugin.js.
 */

import { existsSync, readdirSync, statSync } from 'fs';
import { extname, join } from 'path';

export const allowedExtensions = new Set(['.php', '.json', '.js', '.css', '.md', '.mdc', '.twig']);

export const defaultSkippedDirectories = new Set([
    'vendor',
    'vendor-prefixed',
    'node_modules',
    '.git',
    'build',
    'zip',
]);

/**
 * Normalizes paths to forward slash separators for comparisons.
 *
 * @param {string} value - Path value.
 * @returns {string} Normalized path.
 */
export function toPosixPath(value) {
    return value.replaceAll('\\', '/');
}

/**
 * Converts a slug to a human-readable title.
 *
 * @param {string} value - Slug value.
 * @returns {string} Human-readable title.
 */
export function slugToTitle(value) {
    return value
        .split('-')
        .filter(Boolean)
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

/**
 * Converts a slug to a compact PascalCase PHP namespace segment.
 *
 * @param {string} value - Slug value.
 * @returns {string} PHP namespace segment.
 */
export function slugToNamespace(value) {
    return value
        .split('-')
        .filter(Boolean)
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join('');
}

/**
 * Converts a slug to an uppercase constant prefix (hyphens removed).
 *
 * @param {string} value - Slug value.
 * @returns {string} Constant prefix with trailing underscore.
 */
export function slugToConstantPrefix(value) {
    return `${value.replaceAll('-', '').toUpperCase()}_`;
}

/**
 * Converts a slug to the uppercase constant prefix used in boilerplate placeholders (hyphens become underscores).
 *
 * @param {string} value - Slug value.
 * @returns {string} Placeholder constant prefix with trailing underscore.
 */
export function slugToPlaceholderConstantPrefix(value) {
    return `${value.replaceAll('-', '_').toUpperCase()}_`;
}

/**
 * Validates a kebab-case slug.
 *
 * @param {string | undefined} value - Requested slug.
 * @param {string} usageMessage - CLI usage string.
 * @returns {void}
 */
export function validateSlug(value, usageMessage) {
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
 * Converts a company name to a PascalCase PHP namespace segment.
 *
 * @param {string} value - Company name.
 * @returns {string} PHP namespace segment.
 */
export function companyToNamespace(value) {
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
export function companyToVendorSlug(value) {
    return companyToNamespace(value).toLowerCase();
}

/**
 * Validates the requested company name.
 *
 * @param {string | undefined} value - Requested company name.
 * @param {string} usageMessage - CLI usage string.
 * @returns {void}
 */
export function validateCompany(value, usageMessage) {
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
 * Determines whether a path should be skipped.
 *
 * @param {string} absolutePath - Absolute file or directory path.
 * @param {string} relativePath - Relative path from the root directory.
 * @param {Set<string>} skippedDirectories - Directory names to skip.
 * @param {Set<string>} skippedRelativeDirectories - Relative directory paths to skip.
 * @returns {boolean} Whether the path should be skipped.
 */
export function shouldSkipPath(
    absolutePath,
    relativePath,
    skippedDirectories,
    skippedRelativeDirectories = new Set()
) {
    const baseName = absolutePath.split(/[\\/]/).at(-1);
    const normalizedRelativePath = toPosixPath(relativePath);

    if (baseName && skippedDirectories.has(baseName)) {
        return true;
    }

    return [...skippedRelativeDirectories].some((directory) => {
        const normalizedDirectory = toPosixPath(directory);
        return (
            normalizedRelativePath === normalizedDirectory ||
            normalizedRelativePath.startsWith(`${normalizedDirectory}/`)
        );
    });
}

/**
 * Plans renames for immediate child directories whose names contain a slug.
 *
 * Only the directory basename is rewritten. Nested directories are left in place.
 * A missing parent, an empty slug, or an unchanged slug yields an empty plan.
 *
 * @param {string} parentDir - Directory whose immediate children are scanned.
 * @param {string} oldSlug - Slug substring currently in the directory name.
 * @param {string} newSlug - Replacement slug.
 * @returns {{ from: string, to: string, fromName: string, toName: string }[]} Planned renames, sorted by current name.
 */
export function planChildDirectoryRenames(parentDir, oldSlug, newSlug) {
    if (!existsSync(parentDir) || !oldSlug || oldSlug === newSlug) {
        return [];
    }

    /** @type {{ from: string, to: string, fromName: string, toName: string }[]} */
    const planned = [];

    for (const entry of readdirSync(parentDir, { withFileTypes: true })) {
        if (!entry.isDirectory() || !entry.name.includes(oldSlug)) {
            continue;
        }

        const toName = entry.name.replaceAll(oldSlug, newSlug);

        if (toName === entry.name) {
            continue;
        }

        planned.push({
            from: join(parentDir, entry.name),
            to: join(parentDir, toName),
            fromName: entry.name,
            toName,
        });
    }

    planned.sort((left, right) => left.fromName.localeCompare(right.fromName));

    return planned;
}

/**
 * Collects text files that can be renamed safely.
 *
 * @param {string} directory - Directory to scan.
 * @param {string} relativeBase - Relative path from root.
 * @param {Set<string>} skippedDirectories - Directory names to skip.
 * @param {Set<string>} skippedRelativeDirectories - Relative directory paths to skip.
 * @returns {string[]} Absolute file paths.
 */
export function collectTextFiles(
    directory,
    relativeBase = '',
    skippedDirectories = defaultSkippedDirectories,
    skippedRelativeDirectories = new Set()
) {
    if (!existsSync(directory)) {
        return [];
    }

    const files = [];

    for (const entry of readdirSync(directory)) {
        const absolutePath = join(directory, entry);
        const relativePath = relativeBase ? join(relativeBase, entry) : entry;

        if (shouldSkipPath(absolutePath, relativePath, skippedDirectories, skippedRelativeDirectories)) {
            continue;
        }

        const stats = statSync(absolutePath);

        if (stats.isDirectory()) {
            files.push(
                ...collectTextFiles(
                    absolutePath,
                    relativePath,
                    skippedDirectories,
                    skippedRelativeDirectories
                )
            );
            continue;
        }

        if (stats.isFile() && allowedExtensions.has(extname(entry))) {
            files.push(absolutePath);
        }
    }

    return files;
}

/**
 * Escapes a string for use inside a regular expression.
 *
 * @param {string} value - Literal text.
 * @returns {string} Expression-safe text.
 */
function escapeRegExp(value) {
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/**
 * Replaces a slug only when it is a whole token.
 *
 * Hyphen slugs treat `-` as part of the token, so `boilerplate-plugin` does not
 * match inside `scf-boilerplate-plugin`. Underscore slugs use the same rule for `_`.
 *
 * @param {string} content - File text.
 * @param {string} token - Slug token to replace.
 * @param {string} replacement - New slug token.
 * @returns {string} Updated text.
 */
export function replaceWholeToken(content, token, replacement) {
    if (!token || token === replacement) {
        return content;
    }

    const interior = token.includes('_') && !token.includes('-') ? 'A-Za-z0-9_' : 'A-Za-z0-9-';
    const pattern = new RegExp(`(?<![${interior}])${escapeRegExp(token)}(?![${interior}])`, 'g');

    return content.replace(pattern, () => replacement);
}

/**
 * Rewrites one commented PLUGIN_SLUGS example line.
 *
 * Only a whole comma-separated field equal to `fromSlug` is replaced.
 * Other lines, including an empty `# PLUGIN_SLUGS=`, are returned unchanged.
 *
 * @param {string} line - One line from `.env.example`.
 * @param {string} fromSlug - Current plugin directory name.
 * @param {string} toSlug - New plugin directory name.
 * @returns {string} Updated line.
 */
export function rewriteCommentedPluginSlugsLine(line, fromSlug, toSlug) {
    const match = line.match(/^(\s*#\s*PLUGIN_SLUGS=)(.*)$/);

    if (!match || !fromSlug || fromSlug === toSlug) {
        return line;
    }

    let changed = false;
    const fields = match[2].split(',').map((field) => {
        if (field.trim() !== fromSlug) {
            return field;
        }

        changed = true;
        const leading = field.match(/^\s*/)?.[0] ?? '';
        const trailing = field.match(/\s*$/)?.[0] ?? '';

        return `${leading}${toSlug}${trailing}`;
    });

    if (!changed) {
        return line;
    }

    return `${match[1]}${fields.join(',')}`;
}

/**
 * Applies all replacements to a string in a stable order (longer keys first).
 *
 * @param {string} content - Original file content.
 * @param {Record<string, string>} replacements - Replacement map.
 * @returns {string} Updated file content.
 */
export function applyReplacements(content, replacements) {
    const orderedEntries = Object.entries(replacements).sort(
        ([searchA], [searchB]) => searchB.length - searchA.length
    );

    return orderedEntries.reduce(
        (updatedContent, [search, replacement]) => updatedContent.replaceAll(search, replacement),
        content
    );
}
