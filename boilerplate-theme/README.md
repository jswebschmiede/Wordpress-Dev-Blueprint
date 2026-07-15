# Boilerplate Theme

Reusable WordPress theme and plugin development boilerplate with Gutenberg blocks and Tailwind CSS.

## Development environment

This package lives at `_wp-content-dev/boilerplate-theme/` (or your renamed slug) inside a local WordPress site. For clone instructions and the directory layout, see [`../README.md`](../README.md#wordpress-development-environment).

## Structure

```text
_wp-content-dev/
├── cursor/
│   ├── rules/
│   └── skills/
└── boilerplate-theme/
    ├── .editorconfig
    ├── .gitignore
    ├── .npmrc
    ├── .prettierignore
    ├── .prettierrc
    ├── composer.json
    ├── eslint.config.js
    ├── package.json
    ├── phpcs.xml
    ├── postcss.config.js
    ├── prettier.config.js
    ├── tailwind.css
    ├── docs/
    │   └── DEVELOPMENT.md
    ├── node_scripts/
    │   ├── build-blocks.js
    │   ├── build-block-views.js
    │   ├── build-plugin.js
    │   ├── clean-js-sourcemaps.js
    │   ├── copy-blocks.js
    │   ├── rename-theme.js
    │   ├── rename-plugin.js
    │   └── zip.js
    ├── javascript/
    │   ├── blocks.js
    │   ├── block-editor.js
    │   └── script.js
    ├── blocks/example-block/
    ├── plugins/boilerplate-plugin/
    ├── tailwind/custom/components/example-block.css
    └── theme/
        ├── functions.php
        ├── style.css
        ├── style-editor.css
        ├── composer.json
        ├── blocks/example-block/block.json
        ├── src/
        └── template-parts/blocks/example-block.php
```

The root directory is the development package. The deployable WordPress theme lives in `theme/`; plugin boilerplates live in `plugins/`. The Cursor AI template lives in `_wp-content-dev/cursor/`.

**Key ideas:**

- **Dual-path blocks:** Block metadata is synced into `theme/blocks/` for WordPress; editor code is bundled separately; optional **view** bundles load only when the block is present on the page.
- **Global `@wordpress/*` in bundles:** Editor and plugin builds resolve npm imports to `window.wp.*` instead of duplicating packages.
- **Boilerplate placeholders:** Use `rename-theme.js` and `rename-plugin.js` before starting a real project.

For architecture diagrams and Node script internals, see [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md).

## Recommended project setup order

1. Set up a local WordPress site and clone the blueprint into its web root as `_wp-content-dev` (see [`../README.md`](../README.md#wordpress-development-environment)).
2. Rename this folder to your slug, install dependencies, then run the rename scripts (see [Rename theme placeholders](#rename-theme-placeholders)).
3. Build development assets: `pnpm run development` (or start `pnpm run watch` during active work).
4. Link or copy theme and plugin into WordPress.
5. Copy `_wp-content-dev/cursor/` to your workspace root as `.cursor/`.

## Prerequisites

From this directory after renaming (e.g. `_wp-content-dev/sw-soltau/`):

- **Node.js** and **pnpm** for CSS/JS builds and linting.
- **PHP** and **Composer** for autoloading, Strauss prefixing, and PHPCS.

Rename the package folder **before** `pnpm install`. pnpm creates symlinks in `node_modules` that break when the parent directory path changes.

```bash
cd _wp-content-dev
mv boilerplate-theme sw-soltau
cd sw-soltau
pnpm install
pnpm run composer:install:dev
```

Install all Composer roots (package root, theme, plugins) — or use the convenience script above.

Or manually:

```bash
composer install
composer install --working-dir=theme
composer install --working-dir=plugins/boilerplate-plugin
```

## Development workflow

All commands below are run from `_wp-content-dev/boilerplate-theme/`.

### Daily development

Build all development assets once:

```bash
pnpm run development
pnpm run dev          # alias
```

Watch CSS, JS, blocks, and plugin assets during active work:

```bash
pnpm run watch
```

### Blocks

After any change to `blocks/<name>/block.json`, sync metadata into WordPress:

```bash
pnpm run development:copy-blocks
```

Only `block.json` is copied to `theme/blocks/` — PHP templates and classes stay in `theme/`. Editor JavaScript is bundled via `javascript/blocks.js`; optional frontend behaviour uses `blocks/<slug>/view.js` → `theme/js/blocks-view/<slug>.min.js`.

### CSS (Tailwind)

```bash
pnpm run development:tailwind:frontend
pnpm run development:tailwind:editor
pnpm run production:tailwind:frontend
```

Component-level block styling lives under `tailwind/custom/components/` using `@apply`.

### Theme JavaScript

```bash
pnpm run development:esbuild
pnpm run development:esbuild:blocks
pnpm run development:esbuild:block-views
pnpm run production:esbuild
pnpm run production:esbuild:blocks
pnpm run production:esbuild:block-views
```

### Plugin JavaScript

```bash
pnpm run development:esbuild:plugin:boilerplate-plugin
pnpm run production:esbuild:plugin:boilerplate-plugin
```

After `rename-plugin.js`, the script name in `package.json` is updated automatically.

### Linting

PHP (from the package root — not from `theme/` or `plugins/`):

```bash
composer php:lint
composer php:lint:autofix
composer php:rector:fix:lint     # apply Rector, WPCS autofix, then WPCS check (recommended)
composer make-pot:theme
composer make-pot:plugin
```

JavaScript and CSS:

```bash
pnpm run lint
pnpm run lint-fix
```

### Production and release

Full production pipeline (minified assets, production Composer installs, sourcemap cleanup):

```bash
pnpm run production
pnpm run prod         # alias
```

Create deployable ZIP archives under `zip/` (gitignored):

```bash
pnpm run zip:theme
pnpm run zip:plugin:boilerplate-plugin
pnpm run zip          # theme + all plugin ZIPs
pnpm run bundle       # production + zip
```

### Composer and Strauss

Runtime Composer packages are **prefixed with [Strauss](https://github.com/BrianHenryIE/strauss)** so theme and plugins ship isolated dependencies without autoloader conflicts.

- Prefixed output: `vendor-prefixed/` (generated on `composer install`, gitignored)
- `require-dev` packages are **not** prefixed
- After renaming a plugin, run `composer install --working-dir=plugins/<slug>` to regenerate `vendor-prefixed/`

```bash
pnpm run composer:install:dev
composer prefix-namespaces:dry-run --working-dir=plugins/boilerplate-plugin
```

## Rename theme placeholders

Rename the package folder **before** `pnpm install`, then run the rename scripts to replace placeholder strings in file contents.

```bash
cd _wp-content-dev
mv boilerplate-theme sw-soltau
cd sw-soltau
pnpm install
pnpm run composer:install:dev
node node_scripts/rename-theme.js sw-soltau --company SmartMedia24 --dry-run
node node_scripts/rename-theme.js sw-soltau --company SmartMedia24
```

On Windows (PowerShell), replace `mv boilerplate-theme sw-soltau` with `Rename-Item boilerplate-theme sw-soltau`.

For `sw-soltau` with `--company SmartMedia24`, the script derives:

- Text domain, asset handle prefix, and paths: `sw-soltau`
- Hook/function prefix: `sw_soltau`
- Company namespace: `SmartMedia24`
- Composer vendor / author slug: `smartmedia24`
- PHP namespace segment: `Swsoltau` (full namespace: `SmartMedia24\Swsoltau`)
- Theme name: `Sw Soltau`
- Constant prefix: `SWSOLTAU_`
- Block namespace: `sw-soltau/example-block`

The script replaces placeholders in this package and in `_wp-content-dev/cursor/` (rules and skills, `.md` and `.mdc`). It only replaces file contents and does not rename folders.

### Rename plugin

Run **`rename-theme.js` first**, then **`rename-plugin.js`** (both after `pnpm install`):

```bash
node node_scripts/rename-theme.js sw-soltau --company SmartMedia24
node node_scripts/rename-plugin.js mvg-aktuell --dry-run
node node_scripts/rename-plugin.js mvg-aktuell
```

This renames `plugins/boilerplate-plugin/` to `plugins/<slug>/` and updates plugin-specific placeholders.

## Manual rename checklist

If you rename manually, replace:

- `boilerplate-theme` -> your theme slug and text domain.
- `boilerplate_theme` -> your hook/function prefix.
- `CompanyName` -> your company namespace prefix (PascalCase, e.g. `SmartMedia24`).
- `companyname` -> your Composer vendor and author slug (lowercase, e.g. `smartmedia24`).
- `https://companyname.example` -> your company URL placeholder (update the domain after rename).
- `CompanyName\\BoilerplateTheme\\` -> your full PSR-4 namespace root.
- `BoilerplateTheme` -> your PHP namespace segment (replaced by `rename-theme.js`).
- `Boilerplate Theme` -> your display name.
- `BOILERPLATE_THEME_` -> your constant prefix.
- `boilerplate/example-block` -> your block namespace.

## Cursor AI configuration

The template lives in `_wp-content-dev/cursor/` (rules and skills). After renaming placeholders:

- Copy `cursor/` to your WordPress workspace root as `.cursor/` (copy, not symlink).
- The template stays under `_wp-content-dev/` for reuse.

```bash
cp -r "_wp-content-dev/cursor" ".cursor"
```

On Windows, use `_wp-content-dev/ps/copy-cursor-config.ps1` or `Copy-Item -Recurse`.

## Local usage

Create a symlink or copy the deployable theme directory to WordPress:

```bash
ln -s "_wp-content-dev/boilerplate-theme/theme" "wp-content/themes/boilerplate-theme"
ln -s "_wp-content-dev/boilerplate-theme/plugins/boilerplate-plugin" "wp-content/plugins/boilerplate-plugin"
```

On Windows, configure and run `_wp-content-dev/ps/create-blueprint-theme-link.ps1` and `create-blueprint-plugin-link.ps1`.

Activate the theme and plugin in WordPress after linking. Install Composer dependencies before activating if you have not run `pnpm run composer:install:dev` yet.

Root-level tooling:

- `composer.json` and `phpcs.xml` at the package root provide PHPCS for the entire package (theme and plugins).
- Theme and plugin `composer.json` files only define runtime autoloading.
- `eslint.config.js`, `prettier.config.js`, `.prettierrc`, and `.prettierignore` provide JavaScript and CSS formatting/linting defaults.
- `.editorconfig`, `.npmrc`, and `.gitignore` keep editor, dependency, and generated-file behavior consistent.

## Best practices

- Run **`development:copy-blocks`** after any `block.json` change before testing in WordPress.
- Keep **`name` in `block.json` stable**; renaming breaks existing post content. Use deprecations for markup migrations.
- Use **`apiVersion`: 3** for new blocks (iframe editor compatibility).
- For dynamic blocks, implement PHP `render` and keep `save` minimal (`null` or inner blocks content only).
- Align **`editorScript`** with the theme's registered handle; avoid raw file paths in `block.json` where the theme expects a handle.
- Run **`rename-theme.js`** then **`rename-plugin.js`** before copying `_wp-content-dev/cursor/` to `.cursor/` when starting a new project.

## Common pitfalls

| Pitfall | Symptom | Mitigation |
| ------- | ------- | ---------- |
| Forgot `copy-blocks` after `block.json` edit | WordPress loads stale metadata | Run `development:copy-blocks` |
| New `@wordpress/*` import in blocks bundle | Resolve/bundle errors | Extend `wpGlobals` in `build-blocks.js` |
| Expecting full block folder under `theme/blocks/` | Only `block.json` is copied | Keep PHP/templates in `theme/`; bundle JS via `javascript/` and `blocks/` |
| `view.js` present but not enqueued | Missing frontend behaviour | Register/enqueue handle in PHP; reference in `block.json` |
| Plugin script empty in WordPress | `build/` missing or outdated | Run `build-plugin.js` for that plugin |
| `pnpm install` before folder rename | Broken symlinks in `node_modules` | Rename package folder first, then run `pnpm install` |
| Renamed project but Cursor rules unchanged | AI uses old `boilerplate-theme` paths | Run `rename-theme.js` (includes `_wp-content-dev/cursor/`) |

## Theme dependencies and scaffolding

The theme includes general infrastructure migrated from a production reference (navigation, Redux options, templates, admin hygiene):

- **Redux Framework** (recommended): powers Theme Options (logo, search page, breadcrumbs, 404 text, social links, custom CSS/JS). Without Redux, options fall back to defaults and an admin notice is shown.
- **Font Awesome** (recommended): icons in header search, footer social links, and back-to-top button.
- **Example CPT** (`example_item` + `example_category`): scaffold in `theme/src/PostTypes/ExamplePostType.php` — copy and adapt for project-specific post types.
- **Breadcrumb CPT mapping**: extend via the `boilerplate_theme_breadcrumb_cpt_page_map` filter.

## Adding blocks

1. Copy `blocks/example-block/` to a new block directory.
2. Update the block name, title, attributes, and editor UI.
3. Add a PHP renderer class in `theme/src/Blocks/Blocks/`.
4. Register the block in `theme/src/Blocks/BlockManager.php`.
5. Add a template in `theme/template-parts/blocks/`.
6. Add styles under `tailwind/custom/components/`.
7. Run `pnpm run development:copy-blocks` and rebuild block assets when ready.

For a step-by-step checklist, see [`../cursor/skills/boilerplate-theme-create-block/SKILL.md`](../cursor/skills/boilerplate-theme-create-block/SKILL.md).

## Related documentation

- [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md) — asset pipeline architecture, Node script API, Tailwind details
- [`../README.md`](../README.md) — clone setup, package overview, Windows helper scripts
