/**
 * Copies deployable plugin directories into a Local WP plugins folder.
 *
 * The contents of `plugins/<slug>/` land directly in
 * `<WP_CONTENT_PATH>/plugins/<slug>/`, so the bootstrap PHP file is
 * `<destination>/<bootstrap>.php`.
 *
 * PLUGIN_SLUGS selects the plugins. An empty list exits 0. `--slug` syncs
 * that one plugin even when it is not listed. `--optional` exits 0 when
 * WP_CONTENT_PATH is missing; without it that case exits 1.
 *
 * Usage:
 *   node node_scripts/sync-plugin.js [--watch] [--dry-run] [--optional] [--slug=<slug>]
 */

import { cpSync, existsSync, lstatSync, mkdirSync, readdirSync, rmSync, statSync, watch } from 'fs';
import { dirname, join, relative, resolve, sep } from 'path';
import { fileURLToPath } from 'url';
import { assertPluginSlug, loadFileEnv, readEnv, resolvePluginSlugs } from './plugin-slugs.js';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const repoRoot = join(rootDir, '..');
const args = process.argv.slice(2).filter((arg) => arg !== '--');

const EXCLUDED_DIRECTORIES = new Set(['node_modules', '.git', 'vendor']);
const EXCLUDED_FILE_NAMES = new Set(['.DS_Store', 'Thumbs.db']);

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
 * Reports whether a plugin-relative path must not be copied.
 *
 * `vendor/` is the unprefixed Composer tree. `vendor-prefixed/` stays, because Local PHP loads it.
 *
 * @param {string} relativePath - Path relative to the plugin directory, using forward slashes.
 * @returns {boolean} True when the path is excluded.
 */
function isExcludedRelative(relativePath) {
    const parts = relativePath.split('/');

    if (parts.some((part) => EXCLUDED_DIRECTORIES.has(part))) {
        return true;
    }

    const baseName = parts.at(-1) ?? '';

    if (EXCLUDED_FILE_NAMES.has(baseName) || baseName.endsWith('.map')) {
        return true;
    }

    return false;
}

/**
 * Rejects relative paths that could escape the destination.
 *
 * @param {string} relativePath - Path relative to the plugin directory.
 * @returns {boolean} True when every segment is a single path component.
 */
function isSafeRelative(relativePath) {
    if (!relativePath || relativePath === '.') {
        return false;
    }

    return relativePath.split('/').every((part) => part !== '' && part !== '.' && part !== '..');
}

/**
 * Rejects Windows drive paths, which WSL Node cannot use as copy destinations.
 *
 * @param {string} value - Configured path.
 * @param {string} label - Field name for the error.
 * @returns {void}
 */
function assertWslPath(value, label) {
    if (!/^[A-Za-z]:[\\/]/.test(value)) {
        return;
    }

    const drive = value[0].toLowerCase();
    const rest = value.slice(2).replaceAll('\\', '/');

    throw new Error(`${label} is a Windows path (${value}). From WSL use /mnt/${drive}${rest}`);
}

/**
 * Rejects destinations that would overwrite the repository or a parent WordPress directory.
 *
 * @param {string} destination - Absolute destination directory.
 * @param {string} pluginSource - Absolute `plugins/<slug>/` directory.
 * @returns {void}
 */
function assertSafeDestination(destination, pluginSource) {
    const dest = resolve(destination);
    const source = resolve(pluginSource);
    const packageRoot = resolve(rootDir);
    const repositoryRoot = resolve(repoRoot);

    if (dest === source || dest.startsWith(`${source}${sep}`)) {
        throw new Error(`Refusing to sync into the plugin source (${dest}).`);
    }

    if (
        dest === packageRoot ||
        dest.startsWith(`${packageRoot}${sep}`) ||
        dest === repositoryRoot ||
        dest.startsWith(`${repositoryRoot}${sep}`)
    ) {
        throw new Error(`Refusing to sync into the repository (${dest}).`);
    }

    const baseName = dest.split(sep).at(-1)?.toLowerCase() ?? '';

    if (baseName === 'plugins' || baseName === 'wp-content') {
        throw new Error(
            `Refusing to sync into "${dest}". Point WP_CONTENT_PATH at wp-content so the copy lands in plugins/<slug>.`,
        );
    }

    if (dest.split(sep).filter(Boolean).length < 3) {
        throw new Error(`Refusing to sync to a short path (${dest}).`);
    }
}

/**
 * Lists files under a directory that the sync is allowed to copy.
 *
 * @param {string} directory - Directory to walk.
 * @param {string} pluginRoot - Absolute plugin directory used for relative paths.
 * @returns {string[]} Forward-slash paths relative to the plugin directory.
 */
function collectRelativeFiles(directory, pluginRoot) {
    if (!existsSync(directory)) {
        return [];
    }

    /** @type {string[]} */
    const files = [];

    for (const entry of readdirSync(directory, { withFileTypes: true })) {
        const absolutePath = join(directory, entry.name);
        const relativePath = relative(pluginRoot, absolutePath).split(sep).join('/');

        if (!isSafeRelative(relativePath) || isExcludedRelative(relativePath) || entry.isSymbolicLink()) {
            continue;
        }

        if (entry.isDirectory()) {
            files.push(...collectRelativeFiles(absolutePath, pluginRoot));
            continue;
        }

        if (entry.isFile()) {
            files.push(relativePath);
        }
    }

    return files;
}

/**
 * Compares size and mtime so repeat syncs can skip untouched files.
 *
 * @param {string} from - Source file.
 * @param {string} to - Destination file.
 * @returns {boolean} True when the destination already matches the source.
 */
function isUnchanged(from, to) {
    if (!existsSync(to)) {
        return false;
    }

    const sourceStat = statSync(from);
    const targetStat = statSync(to);

    if (!targetStat.isFile()) {
        return false;
    }

    return sourceStat.size === targetStat.size && Math.abs(sourceStat.mtimeMs - targetStat.mtimeMs) < 1;
}

/**
 * Copies one plugin file into the destination, preserving mtime.
 *
 * @param {string} pluginSource - Absolute plugin directory.
 * @param {string} destination - Absolute destination directory.
 * @param {string} relativePath - Forward-slash path relative to the plugin directory.
 * @param {{ dryRun: boolean, force: boolean }} options - Copy options.
 * @returns {'copied' | 'skipped'} Whether the file was written.
 */
function copyPluginFile(pluginSource, destination, relativePath, options) {
    const from = join(pluginSource, relativePath);
    const to = join(destination, relativePath);

    if (!options.force && isUnchanged(from, to)) {
        return 'skipped';
    }

    if (!options.dryRun) {
        mkdirSync(dirname(to), { recursive: true });
        cpSync(from, to, { preserveTimestamps: true });
    }

    return 'copied';
}

/**
 * Removes a destination path that no longer exists in the plugin source.
 *
 * @param {string} destination - Absolute destination directory.
 * @param {string} relativePath - Forward-slash path relative to the plugin directory.
 * @param {boolean} dryRun - When true, only report the removal.
 * @returns {boolean} True when a path was removed or would be removed.
 */
function removeDestinationPath(destination, relativePath, dryRun) {
    if (!isSafeRelative(relativePath) || isExcludedRelative(relativePath)) {
        return false;
    }

    const target = join(destination, relativePath);

    if (!existsSync(target)) {
        return false;
    }

    if (!dryRun) {
        rmSync(target, { recursive: true, force: true });
    }

    return true;
}

/**
 * Copies the plugin tree and deletes destination files that were removed from the source.
 *
 * @param {string} pluginSource - Absolute plugin directory.
 * @param {string} destination - Absolute destination directory.
 * @param {boolean} dryRun - When true, report actions without writing.
 * @returns {{ copied: number, skipped: number, removed: string[] }} Sync counts and removed paths.
 */
function syncAll(pluginSource, destination, dryRun) {
    const sourceFiles = collectRelativeFiles(pluginSource, pluginSource);
    const sourceSet = new Set(sourceFiles);
    let copied = 0;
    let skipped = 0;

    for (const relativePath of sourceFiles) {
        const result = copyPluginFile(pluginSource, destination, relativePath, { dryRun, force: false });

        if (result === 'copied') {
            copied += 1;
        } else {
            skipped += 1;
        }
    }

    /** @type {string[]} */
    const removed = [];

    if (existsSync(destination)) {
        for (const relativePath of collectRelativeFiles(destination, destination)) {
            if (sourceSet.has(relativePath)) {
                continue;
            }

            if (removeDestinationPath(destination, relativePath, dryRun)) {
                removed.push(relativePath);
            }
        }
    }

    return { copied, skipped, removed };
}

/**
 * Copies or deletes one changed path, including children when the path is a directory.
 *
 * @param {string} slug - Plugin directory name, used in log lines.
 * @param {string} pluginSource - Absolute plugin directory.
 * @param {string} destination - Absolute destination directory.
 * @param {string} relativePath - Forward-slash path relative to the plugin directory.
 * @param {boolean} dryRun - When true, report actions without writing.
 * @returns {void}
 */
function syncChangedPath(slug, pluginSource, destination, relativePath, dryRun) {
    if (!isSafeRelative(relativePath) || isExcludedRelative(relativePath)) {
        return;
    }

    const from = join(pluginSource, relativePath);

    if (!existsSync(from)) {
        if (removeDestinationPath(destination, relativePath, dryRun)) {
            console.log(`${dryRun ? 'would remove' : 'removed'} ${slug}: ${relativePath}`);
        }

        return;
    }

    const stats = lstatSync(from);

    if (stats.isSymbolicLink()) {
        return;
    }

    if (stats.isDirectory()) {
        for (const child of collectRelativeFiles(from, pluginSource)) {
            const result = copyPluginFile(pluginSource, destination, child, { dryRun, force: true });

            if (result === 'copied') {
                console.log(`${dryRun ? 'would sync' : 'synced'} ${slug}: ${child}`);
            }
        }

        return;
    }

    if (!stats.isFile()) {
        return;
    }

    copyPluginFile(pluginSource, destination, relativePath, { dryRun, force: true });
    console.log(`${dryRun ? 'would sync' : 'synced'} ${slug}: ${relativePath}`);
}

/**
 * One plugin selected for sync.
 *
 * @typedef {object} PluginSyncJob
 * @property {string} slug - Plugin directory name.
 * @property {string} source - Absolute `plugins/<slug>/` directory.
 * @property {string} destination - Absolute `<wp-content>/plugins/<slug>/` directory.
 */

/**
 * Resolves every selected plugin after the shared path checks have passed.
 *
 * @param {string[]} slugs - Plugin directory names.
 * @param {string} wpContentPath - Absolute wp-content directory.
 * @returns {PluginSyncJob[]} Jobs in list order.
 */
function resolveJobs(slugs, wpContentPath) {
    /** @type {PluginSyncJob[]} */
    const jobs = [];

    for (const slug of slugs) {
        assertPluginSlug(slug);

        const source = join(rootDir, 'plugins', slug);

        if (!existsSync(source) || !statSync(source).isDirectory()) {
            throw new Error(`Plugin source not found: ${source}`);
        }

        const destination = resolve(wpContentPath, 'plugins', slug);

        assertWslPath(destination, 'Sync target');
        assertSafeDestination(destination, source);
        jobs.push({ slug, source, destination });
    }

    return jobs;
}

/**
 * Watches one plugin directory and copies files that change after the initial sync.
 *
 * Listening starts immediately so writes during the initial copy are queued.
 * Call `markReady` after that copy to flush the queue and log the watch line.
 *
 * @param {PluginSyncJob} job - Plugin source and destination.
 * @returns {{ markReady: () => void }} Control handle.
 */
function startWatcher(job) {
    /** @type {Set<string>} */
    const pending = new Set();
    let timer;
    let ready = false;

    /**
     * Copies the paths gathered from the latest watch burst.
     *
     * @returns {void}
     */
    function flush() {
        const paths = [...pending];
        pending.clear();

        for (const relativePath of paths) {
            try {
                syncChangedPath(job.slug, job.source, job.destination, relativePath, false);
            } catch (error) {
                const message = error instanceof Error ? error.message : String(error);
                console.error(`Plugin sync failed for ${job.slug}: ${relativePath}: ${message}`);
            }
        }
    }

    /**
     * Queues one watch path. Before `markReady`, the queue waits.
     *
     * @param {string | Buffer | null} filename - Path reported by `fs.watch`.
     * @returns {void}
     */
    function enqueue(filename) {
        if (!filename) {
            return;
        }

        const relativePath = String(filename).split(sep).join('/');

        if (!isSafeRelative(relativePath) || isExcludedRelative(relativePath)) {
            return;
        }

        pending.add(relativePath);

        if (!ready) {
            return;
        }

        clearTimeout(timer);
        timer = setTimeout(flush, 100);
    }

    const watcher = watch(job.source, { recursive: true }, (_eventType, filename) => {
        enqueue(filename);
    });

    watcher.on('error', (error) => {
        const message = error instanceof Error ? error.message : String(error);
        console.error(`Plugin sync watch error (${job.slug}): ${message}`);
    });

    return {
        /**
         * Flushes paths saved during the initial sync and accepts further changes.
         *
         * @returns {void}
         */
        markReady() {
            ready = true;
            console.log(`Watching plugins/${job.slug}/ — syncing changed files only.`);

            if (pending.size === 0) {
                return;
            }

            clearTimeout(timer);
            timer = setTimeout(flush, 100);
        },
    };
}

/**
 * Prints the one-shot sync summary for one plugin.
 *
 * @param {string} slug - Plugin directory name.
 * @param {{ copied: number, skipped: number, removed: string[] }} result - Sync counts.
 * @param {boolean} dryRun - When true, phrase the summary as a preview.
 * @returns {void}
 */
function logSummary(slug, result, dryRun) {
    const verb = dryRun ? 'Would sync' : 'Synced';
    console.log(
        `${verb} ${slug}: ${result.copied} file(s), skipped ${result.skipped} unchanged, removed ${result.removed.length}.`,
    );

    for (const relativePath of result.removed) {
        console.log(`${dryRun ? 'would remove' : 'removed'} ${slug}: ${relativePath}`);
    }
}

/**
 * Runs a full sync for each selected plugin, then optionally keeps watching.
 *
 * @returns {void}
 */
function main() {
    const dryRun = args.includes('--dry-run');
    const optional = args.includes('--optional');
    const watchMode = args.includes('--watch');
    const knownFlags = new Set(['--dry-run', '--optional', '--watch', '--slug']);

    try {
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

        const slugs = resolvePluginSlugs(rootDir, getFlagValue('--slug'));

        if (slugs.length === 0) {
            console.log('Plugin sync skipped: PLUGIN_SLUGS is empty.');
            return;
        }

        const wpContentPath = readEnv('WP_CONTENT_PATH', loadFileEnv(rootDir));

        if (!wpContentPath) {
            const message = 'Set WP_CONTENT_PATH. See .env.example.';

            if (optional) {
                console.log(`Plugin sync skipped: ${message}`);
                return;
            }

            throw new Error(`Plugin sync needs WP_CONTENT_PATH. ${message}`);
        }

        assertWslPath(wpContentPath, 'WP_CONTENT_PATH');

        if (!existsSync(wpContentPath)) {
            throw new Error(
                `wp-content path does not exist: ${wpContentPath}. Map the Windows path to /mnt/<drive>/...`,
            );
        }

        const jobs = resolveJobs(slugs, wpContentPath);

        for (const job of jobs) {
            console.log(`Plugin sync (${job.slug}): ${job.source} → ${job.destination}`);

            if (!dryRun) {
                mkdirSync(job.destination, { recursive: true });
            }
        }

        const watchers = watchMode && !dryRun ? jobs.map((job) => startWatcher(job)) : [];

        for (const job of jobs) {
            logSummary(job.slug, syncAll(job.source, job.destination, dryRun), dryRun);
        }

        if (!watchMode) {
            return;
        }

        if (dryRun) {
            console.log('Dry run: watch was not started.');
            return;
        }

        for (const watcher of watchers) {
            watcher.markReady();
        }
    } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        console.error(message);
        process.exit(1);
    }
}

main();
