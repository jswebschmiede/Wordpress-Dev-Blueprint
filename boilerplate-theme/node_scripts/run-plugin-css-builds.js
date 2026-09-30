/**
 * Builds CSS for every plugin listed in PLUGIN_SLUGS by calling build-plugin-css.js.
 *
 * An empty list exits 0 without starting PostCSS. `--slug` builds that one
 * plugin even when it is not listed. `--watch` keeps one PostCSS process open
 * per CSS entry. `--minify` sets production cssnano via _TW_ENV.
 *
 * Usage:
 *   node node_scripts/run-plugin-css-builds.js [--watch] [--minify] [--slug=<slug>]
 */

import { spawn, spawnSync } from 'child_process';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { assertPluginSlug, resolvePluginSlugs } from './plugin-slugs.js';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const buildScript = join(__dirname, 'build-plugin-css.js');
const args = process.argv.slice(2).filter((arg) => arg !== '--');
const knownFlags = new Set(['--watch', '--minify', '--slug']);

/**
 * Reads a CLI flag as `--flag=value` or `--flag value`.
 *
 * @param {string} flag - Flag name including leading dashes.
 * @returns {string | undefined} Flag value when present.
 */
function getFlagValue(flag) {
    const withEquals = `${flag}=`;

    for (let index = 0; index < args.length; index += 1) {
        const arg = args[index];

        if (arg.startsWith(withEquals)) {
            return arg.slice(withEquals.length);
        }

        if (arg === flag) {
            const value = args[index + 1];

            if (!value || value.startsWith('--')) {
                throw new Error(`Missing value for ${flag}.`);
            }

            return value;
        }
    }

    return undefined;
}

/**
 * Rejects flags this driver does not forward to build-plugin-css.js.
 *
 * @returns {void}
 */
function assertKnownFlags() {
    for (let index = 0; index < args.length; index += 1) {
        const arg = args[index];

        if (!arg.startsWith('--')) {
            throw new Error(`Unexpected argument "${arg}". Use --slug=<slug>.`);
        }

        const flag = arg.split('=')[0];

        if (!knownFlags.has(flag)) {
            throw new Error(`Unknown option: ${flag}`);
        }

        if (flag === '--slug' && arg === '--slug') {
            const value = args[index + 1];

            if (value && !value.startsWith('--')) {
                index += 1;
            }
        }
    }
}

/**
 * Collects build-plugin-css.js flags shared by every slug.
 *
 * @returns {string[]} `--watch` and/or `--minify` when requested.
 */
function forwardedFlags() {
    /** @type {string[]} */
    const flags = [];

    if (args.includes('--watch')) {
        flags.push('--watch');
    }

    if (args.includes('--minify')) {
        flags.push('--minify');
    }

    return flags;
}

/**
 * Runs build-plugin-css.js once per slug and waits for each process.
 *
 * @param {string[]} slugs - Plugin directory names.
 * @param {string[]} flags - Flags forwarded to build-plugin-css.js.
 * @returns {void}
 */
function buildSequentially(slugs, flags) {
    for (const slug of slugs) {
        const result = spawnSync(process.execPath, [buildScript, slug, ...flags], {
            cwd: rootDir,
            stdio: 'inherit',
        });

        if (result.error) {
            throw result.error;
        }

        if ((result.status ?? 1) !== 0) {
            process.exit(result.status ?? 1);
        }
    }
}

/**
 * Keeps one build-plugin-css.js watch process open per slug.
 *
 * A non-zero exit stops the remaining watchers and fails the driver.
 * A slug with no CSS entries exits 0 immediately; other watchers stay up.
 *
 * @param {string[]} slugs - Plugin directory names.
 * @param {string[]} flags - Flags forwarded to build-plugin-css.js.
 * @returns {void}
 */
function buildWatching(slugs, flags) {
    const children = slugs.map((slug) =>
        spawn(process.execPath, [buildScript, slug, ...flags], {
            cwd: rootDir,
            stdio: 'inherit',
        }),
    );
    let running = children.length;
    let failed = false;

    /**
     * Stops sibling watchers after one build fails.
     *
     * @param {import('child_process').ChildProcess} failedChild - Process that exited with an error.
     * @returns {void}
     */
    function stopSiblings(failedChild) {
        for (const child of children) {
            if (child === failedChild || child.exitCode !== null || child.signalCode !== null) {
                continue;
            }

            child.kill('SIGTERM');
        }
    }

    /**
     * Records one child as finished. `error` and `exit` can both fire.
     *
     * @param {import('child_process').ChildProcess & { settled?: boolean }} child - Watched process.
     * @param {number | null} code - Exit code. Null counts as a failure.
     * @returns {void}
     */
    function settle(child, code) {
        if (child.settled) {
            return;
        }

        child.settled = true;

        if ((code ?? 1) !== 0) {
            failed = true;
            stopSiblings(child);
        }

        running -= 1;

        if (running === 0) {
            process.exit(failed ? 1 : 0);
        }
    }

    for (const child of children) {
        child.on('error', (error) => {
            console.error(error instanceof Error ? error.message : String(error));
            settle(child, 1);
        });

        child.on('exit', (code) => {
            settle(child, code);
        });
    }
}

/**
 * Builds the resolved plugin list, or exits 0 when the list is empty.
 *
 * @returns {void}
 */
function main() {
    try {
        assertKnownFlags();

        const slugs = resolvePluginSlugs(rootDir, getFlagValue('--slug'));

        if (slugs.length === 0) {
            console.log('Plugin CSS builds skipped: PLUGIN_SLUGS is empty.');
            return;
        }

        for (const slug of slugs) {
            assertPluginSlug(slug);
        }

        const flags = forwardedFlags();

        if (flags.includes('--watch')) {
            buildWatching(slugs, flags);
            return;
        }

        buildSequentially(slugs, flags);
    } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        console.error(message);
        process.exit(1);
    }
}

main();
