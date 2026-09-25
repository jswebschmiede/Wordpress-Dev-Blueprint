/**
 * Replaces boilerplate theme placeholders with project-specific names.
 *
 * Includes `cursor/` and `.vscode/settings.json` at the repository root.
 * `phpsab.standard` is `boilerplate-theme/phpcs.xml` relative to that root;
 * the `boilerplate-theme` segment is replaced with the new package slug.
 * Skill directories under `cursor/skills/` whose names contain `boilerplate-theme`
 * are renamed to the new slug (for example `boilerplate-theme-create-block`).
 *
 * Theme sync: rewrites the destination slug in `sync-theme.example.json`,
 * `.env.example`, and the default in `node_scripts/sync-theme.js`.
 * `.env`, `.env.local`, and `sync-theme.local.json` are left unchanged.
 *
 * Slug and company come from the CLI or from the environment
 * (process, then `.env.local`, then `.env`). CLI wins.
 * Slug keys: `THEME_SLUG`, then `THEME_SYNC_SLUG`.
 * Company key: `THEME_COMPANY`.
 */

import { existsSync, readFileSync, renameSync, writeFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import {
    applyReplacements,
    collectTextFiles,
    companyToNamespace,
    companyToVendorSlug,
    defaultSkippedDirectories,
    planChildDirectoryRenames,
    slugToConstantPrefix,
    slugToNamespace,
    slugToTitle,
    validateCompany,
    validateSlug,
} from './rename-shared.js';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const repoRoot = join(rootDir, '..');
const oldThemeSlug = 'boilerplate-theme';
const cursorDir = join(repoRoot, 'cursor');
const skillsDir = join(cursorDir, 'skills');
const vscodeSettingsFile = join(repoRoot, '.vscode', 'settings.json');
const envExampleFile = join(rootDir, '.env.example');
const ignoredSyncOverrides = new Set([
    join(rootDir, '.env'),
    join(rootDir, '.env.local'),
    join(rootDir, 'sync-theme.local.json'),
]);
const args = process.argv.slice(2);
const isDryRun = args.includes('--dry-run');

const skippedRelativeDirectories = new Set([join('theme', 'js')]);

const usageMessage = `Usage: node node_scripts/rename-theme.js [<slug>] [--company <company>] [--dry-run]
Slug: <slug>, THEME_SLUG, or THEME_SYNC_SLUG.
Company: --company <company> or THEME_COMPANY.
A CLI value overrides the process environment, .env.local, and .env.`;

/**
 * Reads KEY=VALUE pairs from a dotenv-style file.
 *
 * Same rules as `sync-theme.js`: blank lines and `#` comments are ignored,
 * and matching single or double quotes around a value are removed.
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
 * Loads `.env` and `.env.local` from the package root.
 *
 * `.env.local` overrides `.env`.
 *
 * @returns {Record<string, string>} Merged file variables.
 */
function loadFileEnv() {
    return {
        ...parseEnvFile(join(rootDir, '.env')),
        ...parseEnvFile(join(rootDir, '.env.local')),
    };
}

/**
 * Reads the positional theme slug, skipping values that belong to `--company`.
 *
 * @param {string[]} cliArgs - Arguments after the script path.
 * @returns {string | undefined} Slug when passed on the CLI.
 */
function readCliSlug(cliArgs) {
    for (let index = 0; index < cliArgs.length; index += 1) {
        const arg = cliArgs[index];

        if (arg === '--company') {
            index += 1;
            continue;
        }

        if (!arg.startsWith('--')) {
            return arg;
        }
    }

    return undefined;
}

/**
 * Reads `--company <value>`.
 *
 * A present flag without a value is an error. It does not fall through to the environment.
 *
 * @param {string[]} cliArgs - Arguments after the script path.
 * @returns {string | undefined} Company when the flag is present and has a value.
 */
function readCliCompany(cliArgs) {
    const flagIndex = cliArgs.indexOf('--company');

    if (flagIndex === -1) {
        return undefined;
    }

    const value = cliArgs[flagIndex + 1];

    if (!value || value.startsWith('--')) {
        console.error('Missing value for --company.');
        console.error(usageMessage);
        process.exit(1);
    }

    return value;
}

/**
 * Resolves a setting from the CLI, then from env keys.
 *
 * Process environment is checked for every key before dotenv files.
 *
 * @param {string | undefined} cliValue - CLI value when provided.
 * @param {string[]} keys - Env keys, highest priority first.
 * @param {Record<string, string>} fileEnv - Values from `.env` and `.env.local`.
 * @returns {{ value: string, source: string }} Resolved value and the winning source.
 */
function resolveSetting(cliValue, keys, fileEnv) {
    if (typeof cliValue === 'string' && cliValue.trim() !== '') {
        return { value: cliValue.trim(), source: 'CLI' };
    }

    for (const key of keys) {
        const processValue = process.env[key];

        if (typeof processValue === 'string' && processValue.trim() !== '') {
            return { value: processValue.trim(), source: key };
        }
    }

    for (const key of keys) {
        const fileValue = fileEnv[key];

        if (typeof fileValue === 'string' && fileValue.trim() !== '') {
            return { value: fileValue.trim(), source: key };
        }
    }

    return { value: '', source: '' };
}

/**
 * Exits when slug or company was not provided on the CLI or in the environment.
 *
 * @param {string} slugValue - Resolved slug.
 * @param {string} companyValue - Resolved company.
 * @returns {void}
 */
function assertRenameInputs(slugValue, companyValue) {
    const missing = [];

    if (!slugValue) {
        missing.push('theme slug (<slug>, THEME_SLUG, or THEME_SYNC_SLUG)');
    }

    if (!companyValue) {
        missing.push('company (--company or THEME_COMPANY)');
    }

    if (missing.length === 0) {
        return;
    }

    console.error(`Missing required ${missing.join(' and ')}.`);
    console.error(usageMessage);
    process.exit(1);
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
        [oldThemeSlug]: slugValue,
        [oldThemeSlug.replaceAll('-', '_')]: slugValue.replaceAll('-', '_'),
        'boilerplate/example-block': `${slugValue}/example-block`,
        CompanyName: companyNamespace,
        companyname: companyVendor,
        BoilerplateTheme: namespace,
        'Boilerplate Theme': title,
        BOILERPLATE_THEME_: constantPrefix,
    };
}

/**
 * Exits when a planned skill-directory rename would overwrite an existing path.
 *
 * @param {{ to: string, toName: string }[]} planned - Planned directory renames.
 * @returns {void}
 */
function assertSkillDirectoryTargets(planned) {
    const seenTargets = new Set();

    for (const item of planned) {
        if (seenTargets.has(item.to)) {
            console.error(`Skill directory renames collide on: ${item.toName}`);
            process.exit(1);
        }

        seenTargets.add(item.to);

        if (existsSync(item.to)) {
            console.error(`Target skill directory already exists: ${item.to}`);
            process.exit(1);
        }
    }
}

/**
 * Renames skill directories whose names contain the old theme slug.
 *
 * Dry run logs the planned names and leaves the directories in place, matching
 * the plugin rename script.
 *
 * @param {{ from: string, to: string, fromName: string, toName: string }[]} planned - Planned renames.
 * @param {boolean} dryRun - When true, logs the plan and does not rename.
 * @returns {void}
 */
function renameSkillDirectories(planned, dryRun) {
    if (planned.length === 0) {
        return;
    }

    if (dryRun) {
        for (const item of planned) {
            console.log(`Would rename skill directory: ${item.fromName} -> ${item.toName}`);
        }

        console.log('Dry run only. Skill directories were not renamed.');
        return;
    }

    for (const item of planned) {
        renameSync(item.from, item.to);
        console.log(`Renamed skill directory: ${item.fromName} -> ${item.toName}`);
    }
}

const fileEnv = loadFileEnv();
const resolvedSlug = resolveSetting(readCliSlug(args), ['THEME_SLUG', 'THEME_SYNC_SLUG'], fileEnv);
const resolvedCompany = resolveSetting(readCliCompany(args), ['THEME_COMPANY'], fileEnv);
const slug = resolvedSlug.value;
const company = resolvedCompany.value;

assertRenameInputs(slug, company);
validateSlug(slug, usageMessage);
validateCompany(company, usageMessage);

const replacements = getReplacements(slug, company);
const plannedSkillRenames = planChildDirectoryRenames(skillsDir, oldThemeSlug, slug);
assertSkillDirectoryTargets(plannedSkillRenames);
const files = [
    ...new Set([
        ...collectTextFiles(rootDir, '', defaultSkippedDirectories, skippedRelativeDirectories),
        ...collectTextFiles(cursorDir),
        ...(existsSync(vscodeSettingsFile) ? [vscodeSettingsFile] : []),
        ...(existsSync(envExampleFile) ? [envExampleFile] : []),
    ]),
].filter((file) => !ignoredSyncOverrides.has(file));
const changedFiles = [];

console.log('Theme rename values:');
console.log(`Slug/Text Domain: ${slug} (${resolvedSlug.source})`);
console.log(`Company: ${company} (${resolvedCompany.source})`);
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

renameSkillDirectories(plannedSkillRenames, isDryRun);

if (isDryRun) {
    console.log('Dry run only. No files were changed.');
}

console.log(`Affected files: ${changedFiles.length}`);
for (const file of changedFiles) {
    console.log(file);
}
