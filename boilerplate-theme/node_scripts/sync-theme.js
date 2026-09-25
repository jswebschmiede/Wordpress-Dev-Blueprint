/**
 * Copies the deployable theme directory into a Local WP theme folder.
 *
 * The contents of `theme/` land directly in the destination, so `style.css`
 * is `<destination>/style.css` (typically `wp-content/themes/<slug>/style.css`).
 *
 * Usage:
 *   node node_scripts/sync-theme.js [--watch] [--dry-run] [--optional]
 *       [--target=<path>] [--wp-content=<path>] [--slug=<slug>]
 */

import { cpSync, existsSync, lstatSync, mkdirSync, readdirSync, readFileSync, rmSync, statSync, watch } from 'fs';
import { dirname, join, relative, resolve, sep } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const repoRoot = join(rootDir, '..');
const sourceDir = join(rootDir, 'theme');
const args = process.argv.slice(2);

const DEFAULT_THEME_SLUG = 'boilerplate-theme';
const EXCLUDED_DIRECTORIES = new Set(['node_modules', '.git', 'vendor']);
const EXCLUDED_FILE_NAMES = new Set(['.DS_Store', 'Thumbs.db']);

/**
 * Reads KEY=VALUE pairs from a dotenv-style file.
 *
 * @param {string} filePath - Absolute path to the env file.
 * @returns {Record<string, string>} Parsed variables. Missing files yield an empty object.
 */
function parseEnvFile(filePath) {
    if (!existsSync(filePath)) {
        return {};
    }

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
 * Reads the theme sync JSON shape.
 *
 * @param {string} filePath - Absolute path to a sync config file.
 * @returns {{ wpContentPath: string, slug: string, target: string }} Config fields. Empty when the file is missing.
 */
function readJsonConfig(filePath) {
    const empty = { wpContentPath: '', slug: '', target: '' };

    if (!existsSync(filePath)) {
        return empty;
    }

    let parsed;

    try {
        parsed = JSON.parse(readFileSync(filePath, 'utf8'));
    } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        throw new Error(`Could not parse ${filePath}: ${message}`);
    }

    return {
        wpContentPath: typeof parsed.wpContentPath === 'string' ? parsed.wpContentPath.trim() : '',
        slug: typeof parsed.slug === 'string' ? parsed.slug.trim() : '',
        target: typeof parsed.target === 'string' ? parsed.target.trim() : '',
    };
}

/**
 * Returns the first non-empty string.
 *
 * @param {...string} values - Candidates in priority order.
 * @returns {string} Trimmed value, or an empty string.
 */
function firstNonEmpty(...values) {
    for (const value of values) {
        if (typeof value === 'string' && value.trim() !== '') {
            return value.trim();
        }
    }

    return '';
}

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
 * Reports whether a theme-relative path must not be copied.
 *
 * `vendor/` is the unprefixed Composer tree. `vendor-prefixed/` stays, because Local PHP loads it.
 *
 * @param {string} relativePath - Path relative to `theme/`, using forward slashes.
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
 * @param {string} relativePath - Path relative to `theme/`.
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
 * @param {string} themeSource - Absolute `theme/` directory.
 * @returns {void}
 */
function assertSafeDestination(destination, themeSource) {
    const dest = resolve(destination);
    const source = resolve(themeSource);
    const packageRoot = resolve(rootDir);
    const repositoryRoot = resolve(repoRoot);

    if (dest === source || dest.startsWith(`${source}${sep}`)) {
        throw new Error(`Refusing to sync into the theme source (${dest}).`);
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

    if (baseName === 'themes' || baseName === 'wp-content' || baseName === 'public' || baseName === 'app') {
        throw new Error(
            `Refusing to sync into "${dest}". Point at the theme folder (…/wp-content/themes/<slug>).`,
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
 * @param {string} themeSource - Absolute `theme/` directory used for relative paths.
 * @returns {string[]} Forward-slash paths relative to `theme/`.
 */
function collectRelativeFiles(directory, themeSource) {
    if (!existsSync(directory)) {
        return [];
    }

    const files = [];

    for (const entry of readdirSync(directory, { withFileTypes: true })) {
        const absolutePath = join(directory, entry.name);
        const relativePath = relative(themeSource, absolutePath).split(sep).join('/');

        if (!isSafeRelative(relativePath) || isExcludedRelative(relativePath) || entry.isSymbolicLink()) {
            continue;
        }

        if (entry.isDirectory()) {
            files.push(...collectRelativeFiles(absolutePath, themeSource));
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

    // cpSync preserveTimestamps keeps millisecond precision; some filesystems store fractions.
    return sourceStat.size === targetStat.size && Math.abs(sourceStat.mtimeMs - targetStat.mtimeMs) < 1;
}

/**
 * Copies one theme file into the destination, preserving mtime.
 *
 * @param {string} themeSource - Absolute `theme/` directory.
 * @param {string} destination - Absolute destination directory.
 * @param {string} relativePath - Forward-slash path relative to `theme/`.
 * @param {{ dryRun: boolean, force: boolean }} options - Copy options.
 * @returns {'copied' | 'skipped'} Whether the file was written.
 */
function copyThemeFile(themeSource, destination, relativePath, options) {
    const from = join(themeSource, relativePath);
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
 * Removes a destination path that no longer exists in `theme/`.
 *
 * @param {string} destination - Absolute destination directory.
 * @param {string} relativePath - Forward-slash path relative to `theme/`.
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
 * Copies the theme tree and deletes destination files that were removed from `theme/`.
 *
 * @param {string} themeSource - Absolute `theme/` directory.
 * @param {string} destination - Absolute destination directory.
 * @param {boolean} dryRun - When true, report actions without writing.
 * @returns {{ copied: number, skipped: number, removed: string[] }} Sync counts and removed paths.
 */
function syncAll(themeSource, destination, dryRun) {
    const sourceFiles = collectRelativeFiles(themeSource, themeSource);
    const sourceSet = new Set(sourceFiles);
    let copied = 0;
    let skipped = 0;

    for (const relativePath of sourceFiles) {
        const result = copyThemeFile(themeSource, destination, relativePath, { dryRun, force: false });

        if (result === 'copied') {
            copied += 1;
        } else {
            skipped += 1;
        }
    }

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
 * @param {string} themeSource - Absolute `theme/` directory.
 * @param {string} destination - Absolute destination directory.
 * @param {string} relativePath - Forward-slash path relative to `theme/`.
 * @param {boolean} dryRun - When true, report actions without writing.
 * @returns {void}
 */
function syncChangedPath(themeSource, destination, relativePath, dryRun) {
    if (!isSafeRelative(relativePath) || isExcludedRelative(relativePath)) {
        return;
    }

    const from = join(themeSource, relativePath);

    if (!existsSync(from)) {
        if (removeDestinationPath(destination, relativePath, dryRun)) {
            console.log(`${dryRun ? 'would remove' : 'removed'} ${relativePath}`);
        }

        return;
    }

    const stats = lstatSync(from);

    if (stats.isSymbolicLink()) {
        return;
    }

    if (stats.isDirectory()) {
        for (const child of collectRelativeFiles(from, themeSource)) {
            const result = copyThemeFile(themeSource, destination, child, { dryRun, force: true });

            if (result === 'copied') {
                console.log(`${dryRun ? 'would sync' : 'synced'} ${child}`);
            }
        }

        return;
    }

    if (!stats.isFile()) {
        return;
    }

    copyThemeFile(themeSource, destination, relativePath, { dryRun, force: true });
    console.log(`${dryRun ? 'would sync' : 'synced'} ${relativePath}`);
}

/**
 * Resolves env and JSON config. CLI values are applied by the caller.
 *
 * @returns {{ fileEnv: Record<string, string>, example: { wpContentPath: string, slug: string, target: string }, local: { wpContentPath: string, slug: string, target: string } }} Layers.
 */
function loadConfigLayers() {
    const fileEnv = {
        ...parseEnvFile(join(rootDir, '.env')),
        ...parseEnvFile(join(rootDir, '.env.local')),
    };

    return {
        fileEnv,
        example: readJsonConfig(join(rootDir, 'sync-theme.example.json')),
        local: readJsonConfig(join(rootDir, 'sync-theme.local.json')),
    };
}

/**
 * Lists env values for keys in priority order.
 *
 * Process environment is checked for every key before dotenv files, so a shell
 * `THEME_SYNC_SLUG` still overrides `THEME_SLUG` in `.env`.
 *
 * @param {string[]} keys - Variable names, highest priority first.
 * @param {Record<string, string>} fileEnv - Values from `.env` and `.env.local`.
 * @returns {string[]} Trimmed non-empty values.
 */
function envCandidates(keys, fileEnv) {
    const fromProcess = [];
    const fromFile = [];

    for (const key of keys) {
        const processValue = process.env[key];

        if (typeof processValue === 'string' && processValue.trim() !== '') {
            fromProcess.push(processValue.trim());
        }

        if (typeof fileEnv[key] === 'string' && fileEnv[key].trim() !== '') {
            fromFile.push(fileEnv[key].trim());
        }
    }

    return [...fromProcess, ...fromFile];
}

/**
 * Reads one variable from the process environment, then from dotenv files.
 *
 * @param {string} key - Variable name.
 * @param {Record<string, string>} fileEnv - Values from `.env` and `.env.local`.
 * @returns {string} Trimmed value, or an empty string.
 */
function readEnv(key, fileEnv) {
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
 * Builds the destination directory from CLI flags and config layers.
 *
 * `--target` is the folder that receives `theme/` contents. Otherwise the folder is
 * `<wp-content>/themes/<slug>`. A configured full target is used only when no wp-content
 * path is set. `--slug` changes that folder name. Slug keys are `THEME_SLUG`, then
 * `THEME_SYNC_SLUG`, before the JSON configs.
 *
 * @param {{ target?: string, wpContent?: string, slug?: string }} cli - Parsed CLI overrides.
 * @returns {{ destination: string, slug: string, wpContentPath: string }} Resolved destination.
 */
function resolveDestination(cli) {
    const { fileEnv, example, local } = loadConfigLayers();
    const slug = firstNonEmpty(
        cli.slug,
        ...envCandidates(['THEME_SLUG', 'THEME_SYNC_SLUG'], fileEnv),
        local.slug,
        example.slug,
        DEFAULT_THEME_SLUG,
    );
    const wpContentPath = firstNonEmpty(
        cli.wpContent,
        readEnv('WP_CONTENT_PATH', fileEnv),
        local.wpContentPath,
        example.wpContentPath,
    );
    const configuredTarget = firstNonEmpty(
        cli.target,
        cli.wpContent ? '' : readEnv('THEME_SYNC_TARGET', fileEnv),
        cli.wpContent ? '' : local.target,
        cli.wpContent ? '' : example.target,
    );

    if (cli.target) {
        return { destination: resolve(cli.target), slug, wpContentPath };
    }

    if (wpContentPath) {
        return { destination: resolve(wpContentPath, 'themes', slug), slug, wpContentPath };
    }

    if (cli.slug && configuredTarget) {
        return { destination: resolve(dirname(configuredTarget), cli.slug), slug, wpContentPath };
    }

    if (configuredTarget) {
        return { destination: resolve(configuredTarget), slug, wpContentPath };
    }

    return { destination: '', slug, wpContentPath };
}

/**
 * Validates the slug when it is part of the destination path.
 *
 * @param {string} slug - Theme directory name.
 * @returns {void}
 */
function assertSlug(slug) {
    if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) {
        throw new Error('Theme slug must contain only lowercase letters, numbers, and single hyphens.');
    }
}

/**
 * Watches `theme/` and copies files that change after the initial sync.
 *
 * Listening starts immediately so writes during the initial copy are queued.
 * Call `markReady` after that copy to flush the queue and log the watch line.
 *
 * @param {string} destination - Absolute destination directory.
 * @param {boolean} dryRun - When true, report actions without writing.
 * @returns {{ markReady: () => void }} Control handle.
 */
function startWatcher(destination, dryRun) {
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
                syncChangedPath(sourceDir, destination, relativePath, dryRun);
            } catch (error) {
                const message = error instanceof Error ? error.message : String(error);
                console.error(`Theme sync failed for ${relativePath}: ${message}`);
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

    const watcher = watch(sourceDir, { recursive: true }, (_eventType, filename) => {
        enqueue(filename);
    });

    watcher.on('error', (error) => {
        const message = error instanceof Error ? error.message : String(error);
        console.error(`Theme sync watch error: ${message}`);
    });

    return {
        /**
         * Flushes paths saved during the initial sync and accepts further changes.
         *
         * @returns {void}
         */
        markReady() {
            ready = true;
            console.log('Watching theme/ — syncing changed files only.');

            if (pending.size === 0) {
                return;
            }

            clearTimeout(timer);
            timer = setTimeout(flush, 100);
        },
    };
}

/**
 * Prints the one-shot sync summary.
 *
 * @param {{ copied: number, skipped: number, removed: string[] }} result - Sync counts.
 * @param {boolean} dryRun - When true, phrase the summary as a preview.
 * @returns {void}
 */
function logSummary(result, dryRun) {
    const verb = dryRun ? 'Would sync' : 'Synced';
    console.log(
        `${verb} ${result.copied} file(s), skipped ${result.skipped} unchanged, removed ${result.removed.length}.`,
    );

    for (const relativePath of result.removed) {
        console.log(`${dryRun ? 'would remove' : 'removed'} ${relativePath}`);
    }
}

/**
 * Runs a full sync, then optionally keeps watching `theme/`.
 *
 * @returns {void}
 */
function main() {
    const dryRun = args.includes('--dry-run');
    const optional = args.includes('--optional');
    const watchMode = args.includes('--watch');
    const knownFlags = new Set(['--dry-run', '--optional', '--watch', '--target', '--wp-content', '--slug']);

    try {
        for (const arg of args) {
            const flag = arg.startsWith('--') ? arg.split('=')[0] : '';

            if (flag && !knownFlags.has(flag)) {
                throw new Error(`Unknown option: ${flag}`);
            }
        }

        const cli = {
            target: getFlagValue('--target'),
            wpContent: getFlagValue('--wp-content'),
            slug: getFlagValue('--slug'),
        };
        const resolved = resolveDestination(cli);

        if (!resolved.destination) {
            const message =
                'Set WP_CONTENT_PATH or THEME_SYNC_TARGET. See .env.example and sync-theme.example.json.';

            if (optional) {
                console.log(`Theme sync skipped: ${message}`);
                return;
            }

            throw new Error(`Theme sync needs a destination. ${message}`);
        }

        const destinationUsesSlug = !cli.target && (Boolean(resolved.wpContentPath) || Boolean(cli.slug));

        if (destinationUsesSlug) {
            assertSlug(resolved.slug);
        }

        if (resolved.wpContentPath) {
            assertWslPath(resolved.wpContentPath, 'WP_CONTENT_PATH');

            if (!existsSync(resolved.wpContentPath)) {
                throw new Error(
                    `wp-content path does not exist: ${resolved.wpContentPath}. Map the Windows path to /mnt/<drive>/...`,
                );
            }
        }

        assertWslPath(resolved.destination, 'Sync target');
        assertSafeDestination(resolved.destination, sourceDir);

        if (!existsSync(sourceDir)) {
            throw new Error(`Theme source not found: ${sourceDir}`);
        }

        console.log(`Theme sync: ${sourceDir} → ${resolved.destination}`);

        if (!dryRun) {
            mkdirSync(resolved.destination, { recursive: true });
        }

        const watcher = watchMode && !dryRun ? startWatcher(resolved.destination, false) : null;

        logSummary(syncAll(sourceDir, resolved.destination, dryRun), dryRun);

        if (!watchMode) {
            return;
        }

        if (dryRun) {
            console.log('Dry run: watch was not started.');
            return;
        }

        watcher.markReady();
    } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        console.error(message);
        process.exit(1);
    }
}

main();
