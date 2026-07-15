# Boilerplates

This directory contains reusable WordPress development boilerplates. Use `boilerplate-theme/` as a starting point for new theme and plugin projects: rename placeholders, link or copy assets into WordPress, and optionally copy the included `cursor/` AI configuration into your workspace root.

## WordPress development environment

The boilerplates live inside a preconfigured local WordPress installation. Clone the blueprint repository into your local web root (for example a [Local](https://localwp.com/) site `app/public/` directory), then detach it from the upstream Git history so you can start your own project repository.

```bash
git clone https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git my-wordpress-project
cd my-wordpress-project
rm -rf .git
git init
```

On Windows (PowerShell):

```powershell
git clone https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git my-wordpress-project
cd my-wordpress-project
Remove-Item -Recurse -Force .git
git init
```

After cloning:

1. Point your local site (Local, DDEV, Laragon, etc.) at the project folder.
2. Import or create a WordPress database and configure `wp-config.php` if needed.
3. Continue with the [recommended project setup order](#recommended-project-setup-order) below.

The blueprint includes WordPress core, a `_wp-content-dev/` development tree, and optional helper scripts under `_wp-content-dev/ps/` for Windows symlinks.

## Package

- `boilerplate-theme/`: WordPress development package with a deployable `theme/` directory, one dynamic Gutenberg example block, and `plugins/boilerplate-plugin/`.
- `cursor/`: Cursor AI configuration template (rules and skills) for the boilerplate workflow.

The package includes Composer/PHPCS, ESLint, Prettier, PostCSS, EditorConfig, npm config, and git ignore defaults.

Technical details (asset pipeline, Node scripts, pitfalls): [`boilerplate-theme/docs/DEVELOPMENT.md`](boilerplate-theme/docs/DEVELOPMENT.md).

Full development workflow (daily commands, builds, release): [`boilerplate-theme/README.md`](boilerplate-theme/README.md).

## Recommended project setup order

1. Clone [Wordpress-Dev-Blueprint](https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git) and remove `.git` (see above).
2. Install Node and PHP dependencies in `boilerplate-theme/` (see [`boilerplate-theme/README.md`](boilerplate-theme/README.md#prerequisites)).
3. Rename placeholders with `rename-theme.js` (and `rename-plugin.js` if you use the boilerplate plugin).
4. Build development assets (`pnpm run development` or `pnpm run watch`).
5. Link or copy theme and plugin into WordPress (see below).
6. Copy `_wp-content-dev/cursor/` to your workspace root as `.cursor/` (copy, not symlink).

## Rename theme placeholders

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

See [`boilerplate-theme/README.md`](boilerplate-theme/README.md) for the full placeholder checklist and plugin rename workflow.

## Local WordPress usage

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

## Composer dependencies

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

Or use the convenience script from `boilerplate-theme/`:

```bash
cd _wp-content-dev/boilerplate-theme
pnpm run composer:install:dev
```

Build and watch commands are documented in [`boilerplate-theme/README.md`](boilerplate-theme/README.md). For script internals and architecture, see [`boilerplate-theme/docs/DEVELOPMENT.md`](boilerplate-theme/docs/DEVELOPMENT.md).
