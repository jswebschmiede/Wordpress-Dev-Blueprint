# Boilerplates

This directory contains reusable WordPress development boilerplates. Use `boilerplate-theme/` as a starting point for new theme and plugin projects: rename placeholders, link or copy assets into WordPress, and optionally copy the included `cursor/` AI configuration into your workspace root.

## Package

- `boilerplate-theme/`: WordPress development package with a deployable `theme/` directory, one dynamic Gutenberg example block, and `plugins/boilerplate-plugin/`.
- `cursor/`: Cursor AI configuration template (rules and skills) for the boilerplate workflow.

The package includes Composer/PHPCS, ESLint, Prettier, PostCSS, EditorConfig, npm config, and git ignore defaults.

Technical details (asset pipeline, Node scripts, pitfalls): [`boilerplate-theme/docs/DEVELOPMENT.md`](boilerplate-theme/docs/DEVELOPMENT.md).

## Rename Theme Placeholders

From the package directory:

```bash
cd _wp-content-dev/boilerplate-theme
node node_scripts/rename-theme.js sw-soltau --company SmartMedia24 --dry-run
node node_scripts/rename-theme.js sw-soltau --company SmartMedia24
```

1. Choose a slug (lowercase letters, numbers, and hyphens only, e.g. `sw-soltau`).
2. Choose a company name (kebab-case or PascalCase, e.g. `SmartMedia24`).
3. Run with `--dry-run` first to review affected files (includes `_wp-content-dev/cursor/` rules and skills).
4. Run without `--dry-run` to apply replacements.

For `sw-soltau` with `--company SmartMedia24`, the script derives:

- Text domain, asset handle prefix, and paths: `sw-soltau`
- Hook/function prefix: `sw_soltau`
- Company namespace: `SmartMedia24`
- Composer vendor / author slug: `smartmedia24`
- PHP namespace segment: `Swsoltau` (full namespace: `SmartMedia24\Swsoltau`)
- Theme name: `Sw Soltau`
- Constant prefix: `SWSOLTAU_`
- Block namespace: `sw-soltau/example-block`

The script replaces placeholders in `boilerplate-theme/` and `_wp-content-dev/cursor/`. It only replaces file contents and does not rename folders.

See `_wp-content-dev/boilerplate-theme/README.md` for the full placeholder checklist.

## Recommended Project Setup Order

1. Rename placeholders with `rename-theme.js`
2. Link or copy theme and plugin into WordPress (see below)
3. Copy `_wp-content-dev/cursor/` to your workspace root as `.cursor/` (copy, not symlink)

## Local WordPress Usage

### Bash (macOS/Linux)

Create symlinks or copies into a WordPress installation:

```bash
ln -s "_wp-content-dev/boilerplate-theme/plugins/boilerplate-plugin" "wp-content/plugins/boilerplate-plugin"
ln -s "_wp-content-dev/boilerplate-theme/theme" "wp-content/themes/boilerplate-theme"
```

Copy the Cursor AI configuration:

```bash
cp -r "_wp-content-dev/cursor" ".cursor"
```

### Windows (PowerShell)

Configure paths at the top of each script in `_wp-content-dev/ps/`, then run:

```powershell
# Symlinks (Administrator PowerShell may be required)
.\_wp-content-dev\ps\create-blueprint-theme-link.ps1
.\_wp-content-dev\ps\create-blueprint-plugin-link.ps1

# Cursor configuration (copy, not symlink)
.\_wp-content-dev\ps\copy-cursor-config.ps1
```

Alternatively, copy manually:

```powershell
Copy-Item -Recurse -Force "_wp-content-dev\cursor" ".cursor"
```

## Composer Dependencies

Install runtime autoloading for the theme and plugin:

```bash
composer install --working-dir="_wp-content-dev/boilerplate-theme/theme"
composer install --working-dir="_wp-content-dev/boilerplate-theme/plugins/boilerplate-plugin"
```

Install development tooling (PHPCS, translation exports) from the package root:

```bash
composer install --working-dir="_wp-content-dev/boilerplate-theme"
composer php:lint --working-dir="_wp-content-dev/boilerplate-theme"
```

Build scripts are documented in `_wp-content-dev/boilerplate-theme/package.json`. See [`boilerplate-theme/docs/DEVELOPMENT.md`](boilerplate-theme/docs/DEVELOPMENT.md) for development workflow details. Run builds only when you intentionally want to generate assets.
