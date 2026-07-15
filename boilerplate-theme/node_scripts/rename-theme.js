/**
 * Replaces boilerplate theme placeholders with project-specific names.
 */

import { readFileSync, writeFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import {
    applyReplacements,
    collectTextFiles,
    companyToNamespace,
    companyToVendorSlug,
    defaultSkippedDirectories,
    slugToConstantPrefix,
    slugToNamespace,
    slugToTitle,
    validateCompany,
    validateSlug,
} from './rename-shared.js';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const cursorDir = join(rootDir, '..', 'cursor');
const args = process.argv.slice(2);
const slug = args.find((arg) => !arg.startsWith('--'));
const isDryRun = args.includes('--dry-run');
const companyFlagIndex = args.indexOf('--company');
const company = companyFlagIndex !== -1 ? args[companyFlagIndex + 1] : undefined;

const skippedRelativeDirectories = new Set([join('theme', 'js')]);

const usageMessage = 'Usage: node node_scripts/rename-theme.js <slug> --company <company> [--dry-run]';

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

validateSlug(slug, usageMessage);
validateCompany(company, usageMessage);

const replacements = getReplacements(slug, company);
const files = [
    ...new Set([
        ...collectTextFiles(rootDir, '', defaultSkippedDirectories, skippedRelativeDirectories),
        ...collectTextFiles(cursorDir),
    ]),
];
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
