/**
 * Reads PLUGIN_SLUGS for plugin build, watch, and sync.
 *
 * Precedence is the process environment, then `.env.local`, then `.env`.
 * Blank lines and `#` comments are ignored. An empty, missing, or
 * commented key yields an empty list. There is no fallback slug.
 */

import { existsSync, readFileSync } from 'fs';
import { join } from 'path';

/**
 * Reads KEY=VALUE pairs from a dotenv-style file.
 *
 * @param {string} filePath - Absolute path to the env file.
 * @returns {Record<string, string>} Parsed variables. Missing files yield an empty object.
 */
export function parseEnvFile(filePath) {
    if (!existsSync(filePath)) {
        return {};
    }

    /** @type {Record<string, string>} */
    const values = {};

    for (const line of readFileSync(filePath, 'utf8').split('\n')) {
        const trimmed = line.trim();

        if (!trimmed || trimmed.startsWith('#')) {
            continue;
        }

        const separator = trimmed.indexOf('=');

        if (separator === -1) {
            continue;
        }

        const key = trimmed.slice(0, separator).trim();
        let value = trimmed.slice(separator + 1).trim();

        if (
            (value.startsWith('"') && value.endsWith('"')) ||
            (value.startsWith("'") && value.endsWith("'"))
        ) {
            value = value.slice(1, -1);
        }

        if (key) {
            values[key] = value;
        }
    }

    return values;
}

/**
 * Loads `.env` and `.env.local` from the package root.
 *
 * `.env.local` overrides `.env`.
 *
 * @param {string} rootDir - Package root (`boilerplate-theme/`).
 * @returns {Record<string, string>} Merged file variables.
 */
export function loadFileEnv(rootDir) {
    return {
        ...parseEnvFile(join(rootDir, '.env')),
        ...parseEnvFile(join(rootDir, '.env.local')),
    };
}

/**
 * Reads one variable from the process environment, then from dotenv files.
 *
 * An empty value is skipped so the next layer can supply a list.
 *
 * @param {string} key - Variable name.
 * @param {Record<string, string>} fileEnv - Values from `.env` and `.env.local`.
 * @returns {string} Trimmed value, or an empty string.
 */
export function readEnv(key, fileEnv) {
    const fromProcess = process.env[key];

    if (typeof fromProcess === 'string' && fromProcess.trim() !== '') {
        return fromProcess.trim();
    }

    if (typeof fileEnv[key] === 'string' && fileEnv[key].trim() !== '') {
        return fileEnv[key].trim();
    }

    return '';
}

/**
 * Splits a comma-separated PLUGIN_SLUGS value.
 *
 * @param {string} value - Raw list. Empty fields are dropped.
 * @returns {string[]} Slugs in order, without duplicates.
 */
export function parsePluginSlugList(value) {
    if (typeof value !== 'string' || value.trim() === '') {
        return [];
    }

    /** @type {string[]} */
    const slugs = [];
    const seen = new Set();

    for (const part of value.split(',')) {
        const slug = part.trim();

        if (!slug || seen.has(slug)) {
            continue;
        }

        seen.add(slug);
        slugs.push(slug);
    }

    return slugs;
}

/**
 * Reads PLUGIN_SLUGS from the process environment, then `.env.local`, then `.env`.
 *
 * @param {string} rootDir - Package root (`boilerplate-theme/`).
 * @returns {string[]} Configured slugs. Empty when the key is missing or blank.
 */
export function readPluginSlugs(rootDir) {
    return parsePluginSlugList(readEnv('PLUGIN_SLUGS', loadFileEnv(rootDir)));
}

/**
 * Resolves the slugs to build or sync.
 *
 * `--slug` selects that one plugin even when it is absent from PLUGIN_SLUGS.
 *
 * @param {string} rootDir - Package root (`boilerplate-theme/`).
 * @param {string | undefined} cliSlug - Value of `--slug` when present.
 * @returns {string[]} Slugs to build or sync.
 */
export function resolvePluginSlugs(rootDir, cliSlug) {
    if (typeof cliSlug === 'string' && cliSlug.trim() !== '') {
        return [cliSlug.trim()];
    }

    return readPluginSlugs(rootDir);
}

/**
 * Validates a plugin directory name used in a filesystem path.
 *
 * @param {string} slug - Plugin directory name.
 * @returns {void}
 */
export function assertPluginSlug(slug) {
    if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) {
        throw new Error(
            `Plugin slug must contain only lowercase letters, numbers, and single hyphens (${slug}).`,
        );
    }
}
