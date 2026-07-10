#!/usr/bin/env node

/*
 * Based upon the Archiver quickstart.
 * @see: https://www.archiverjs.com/docs/quickstart
 */

import AdmZip from 'adm-zip';
import archiver from 'archiver';
import fs from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const args = process.argv.slice(2);
const target = args[0];
const slug = args[1];

const THEME_VERSION_CONSTANT = 'BOILERPLATE_THEME_VERSION';

/**
 * Writes a base36 Unix-timestamp build id into BOILERPLATE_THEME_VERSION inside the
 * zipped ThemeManager.php (source of truth for theme asset versions).
 *
 * @param {string} zipFilePath - Absolute path to the zip file.
 * @param {string} themeSlug - Root folder name of the theme inside the archive.
 * @returns {void}
 */
function addDateVersionToTheme(zipFilePath, themeSlug) {
    const zip = new AdmZip(zipFilePath);
    const relativePath = `${themeSlug}/src/Theme/ThemeManager.php`;
    const entry = zip.getEntry(relativePath);

    if (!entry) {
        console.warn(
            `No ThemeManager.php found for version injection (${relativePath}).`,
        );
        return;
    }

    const originalContent = zip.readAsText(entry);
    const buildVersion = Math.floor(Date.now() / 1000).toString(36);
    const versionPattern = new RegExp(
        `define\\(\\s*'${THEME_VERSION_CONSTANT}',\\s*'[^']*'\\s*\\)`,
    );
    const updatedContent = originalContent.replace(
        versionPattern,
        `define( '${THEME_VERSION_CONSTANT}', '${buildVersion}' )`,
    );

    if (updatedContent === originalContent) {
        console.warn(
            `${THEME_VERSION_CONSTANT} pattern not found; zip left unchanged for version injection.`,
        );
        return;
    }

    zip.updateFile(entry, Buffer.from(updatedContent, 'utf8'));
    zip.writeZip(zipFilePath);
    console.log(
        `Date-based version "${buildVersion}" written to ${relativePath} in the theme zip.`,
    );
}

/**
 * Create a ZIP archive for a theme or plugin.
 * @param {'theme'|'plugin'} archiveTarget Target type to archive.
 * @param {string} archiveSlug Directory slug inside the archive.
 */
function createArchive(archiveTarget, archiveSlug) {
    if (!archiveTarget || !['theme', 'plugin'].includes(archiveTarget)) {
        console.error('Target must be provided as "theme" or "plugin".');
        process.exit(1);
    }

    if (!archiveSlug) {
        console.error('Slug argument missing.');
        process.exit(1);
    }

    const __dirname = dirname(fileURLToPath(import.meta.url));
    const baseDir = join(__dirname, '..');
    const sourceDir =
        archiveTarget === 'theme'
            ? join(baseDir, 'theme')
            : join(baseDir, 'plugins', archiveSlug);

    if (!fs.existsSync(sourceDir)) {
        console.error(`Source directory not found: ${sourceDir}`);
        process.exit(1);
    }

    const zipOutDir = join(baseDir, 'zip');
    fs.mkdirSync(zipOutDir, { recursive: true });
    const zipFilePath = join(zipOutDir, `${archiveSlug}.zip`);
    const output = fs.createWriteStream(zipFilePath);
    const archive = archiver('zip');

    output.on('close', function () {
        console.log(`${archive.pointer()} total bytes.`);
        console.log(`ZIP file created for ${archiveTarget}: ${zipFilePath}`);

        if (archiveTarget === 'theme') {
            addDateVersionToTheme(zipFilePath, archiveSlug);
        }
    });

    output.on('end', function () {
        console.log('Data has been drained');
    });

    archive.on('warning', function (err) {
        if (err.code === 'ENOENT') {
            return;
        }

        throw err;
    });

    archive.on('error', function (err) {
        throw err;
    });

    archive.pipe(output);
    archive.directory(sourceDir, archiveSlug);
    archive.finalize();
}

createArchive(target, slug);
