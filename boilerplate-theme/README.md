# Boilerplate Theme

Reusable WordPress theme and plugin development boilerplate with Gutenberg blocks and Tailwind CSS.

## Development environment

This package is `boilerplate-theme/` (or your renamed slug) inside the repository. The repository stays in the WSL filesystem (`/home/...`); WordPress stays in a separate Local WP install on Windows. Open the repository with Cursor or VS Code **Remote – WSL** and run the tooling in Linux. Sync `theme/` into the Local `wp-content/themes/<slug>` folder. Clone instructions and the sync setup are in [`../README.md`](../README.md#sync-the-theme-to-local).

## Structure

```text
repository root/
├── .vscode/settings.json
├── cursor/
│   ├── rules/
│   └── skills/
└── boilerplate-theme/
    ├── .editorconfig
    ├── .env.example
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
    ├── bin/
    │   ├── download-strauss.php
    │   └── fix-prefixed-twig.php
    ├── node_scripts/
    │   ├── build-blocks.js
    │   ├── build-block-views.js
    │   ├── build-plugin.js
    │   ├── clean-js-sourcemaps.js
    │   ├── copy-blocks.js
    │   ├── plugin-slugs.js
    │   ├── rename-theme.js
    │   ├── rename-plugin.js
    │   ├── run-plugin-builds.js
    │   ├── sync-theme.js
    │   ├── sync-plugin.js
    │   └── zip.js
    ├── javascript/
    │   ├── blocks.js
    │   ├── block-editor.js
    │   └── script.js
    ├── blocks/example-block/
    ├── plugins/boilerplate-plugin/
    ├── plugins/scf-boilerplate-plugin/
    ├── tailwind/custom/components/example-block.css
    └── theme/
        ├── functions.php
        ├── style.css
        ├── style-editor.css
        ├── composer.json
        ├── index.php, page.php, single.php, archive.php, 404.php   # thin Timber stubs
        ├── templates/template-search.php                          # WP page template (stub)
        ├── blocks/example-block/block.json
        ├── inc/template-functions.php
        ├── src/
        └── views/                                                  # Twig (Timber)
            ├── layouts/base.twig
            ├── partials/
            ├── templates/
            └── blocks/example-block.twig
```

The package directory is the development package. The deployable WordPress theme lives in `theme/`; plugin boilerplates live in `plugins/`. The Cursor AI template lives in `cursor/` at the repository root.

For architecture, build pipeline details, and Node script behaviour, see [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md).

## Recommended project setup order

1. Clone the blueprint into the WSL filesystem and keep WordPress in a separate Local WP install (see [`../README.md`](../README.md#wordpress-development-environment)). Open the repository with Remote – WSL.
2. Rename this folder to your slug, then run `pnpm install` (see [Prerequisites](#prerequisites)).
3. Copy `.env.example` to `.env`, set `THEME_SLUG` and `THEME_COMPANY`, then run the rename scripts (see [Rename theme placeholders](#rename-theme-placeholders)). Theme first (`pnpm run rename:theme`), then plugin (`pnpm run rename:plugin`).
4. Run `pnpm run composer:install:dev`. Strauss prefixes Timber and plugin dependencies with the namespaces the rename scripts wrote into each `composer.json`. Do this after the rename scripts, not before.
5. Build development assets: `pnpm run development` (or start `pnpm run watch` during active work).
6. In `.env`, set `WP_CONTENT_PATH`. Uncomment `PLUGIN_SLUGS` when a plugin should be built and copied into Local. Sync with `pnpm run sync:theme` or `pnpm run watch`. Activate the theme after step 4. See [Local usage](#local-usage).
7. From the repository root, copy `cursor/` to `.cursor/` after the rename scripts (see [Cursor AI configuration](#cursor-ai-configuration)).

## Prerequisites

From this directory after renaming (for example `sw-soltau/` inside the repository):

- **Node.js** and **pnpm** for CSS/JS builds and linting.
- **PHP 8.3+** and **Composer** for autoloading, Strauss prefixing, and PHPCS. The theme requires PHP 8.3 (`theme/composer.json`); Timber 2 itself allows PHP 8.2.

Rename the package folder **before** `pnpm install`. pnpm creates symlinks in `node_modules` that break when the parent directory path changes.

```bash
# repository root
mv boilerplate-theme sw-soltau
cd sw-soltau
pnpm install
```

Run Composer only after the rename scripts. `pnpm run composer:install:dev` installs the package root (PHPCS), the theme (Timber), and both plugins. A manual install of only `plugins/boilerplate-plugin` skips `plugins/scf-boilerplate-plugin`.

## Rename theme placeholders

Rename the package folder **before** `pnpm install`, then replace placeholder strings. `pnpm run rename:theme` reads the slug and company from `.env`. Do not pass them as arguments.

```bash
# repository root
mv boilerplate-theme sw-soltau
cd sw-soltau
pnpm install
cp .env.example .env
```

In `.env`, uncomment and set:

```dotenv
THEME_SLUG=sw-soltau
THEME_COMPANY=SmartMedia24
```

`THEME_SYNC_SLUG` is an alias of `THEME_SLUG`. `.env.local` overrides `.env`. The process environment overrides both, and a shell `THEME_SYNC_SLUG` still overrides `THEME_SLUG` in `.env`. If slug or company is still missing, the script exits with an error.

From the package directory:

```bash
pnpm run rename:theme
```

`pnpm run rename:theme` rewrites `extra.strauss.namespace_prefix` in `theme/composer.json` and the prefixed `use` lines in PHP and Twig. It skips `vendor/` and `vendor-prefixed/`. If Composer already ran, those directories still contain `CompanyName\BoilerplateTheme\…`. Run `pnpm run composer:install:dev` once, after the theme rename and any plugin rename. Do not run it before those scripts or between them.

It also rewrites the theme-sync slug in `.env.example` (commented `THEME_SLUG`, `THEME_SYNC_SLUG`, and `THEME_SYNC_TARGET`) and the default in `node_scripts/sync-theme.js`, so the Local folder becomes `wp-content/themes/<new-slug>`. `.env` and `.env.local` are not changed. A slug already set there keeps the sync destination.

For `sw-soltau` with `THEME_COMPANY=SmartMedia24`, the script derives text domain, hook prefix, PHP namespace, display name, and block namespace from the slug and company. See [`docs/DEVELOPMENT.md` §3.5](docs/DEVELOPMENT.md#35-node_scriptsrename-themejs) for the full replacement table and validation rules.

The script replaces placeholders in this package, in `cursor/` at the repository root (rules and skills), and in `.vscode/settings.json` (`phpsab.standard`). That setting is `boilerplate-theme/phpcs.xml` when the repository root is the workspace; the script updates the `boilerplate-theme` segment to the new slug. It also renames directories under `cursor/skills/` whose names contain `boilerplate-theme` (for example `boilerplate-theme-create-block`). Other folders are not renamed.

### Rename plugin

Run **`pnpm run rename:theme` first**, then **`pnpm run rename:plugin`** (both after `pnpm install`).

`pnpm run rename:plugin` does not read a slug or company from `.env`. `PLUGIN_SLUGS` only selects plugins for build, watch, and sync. Pass the new slug and the flags the script requires. pnpm forwards those arguments; do not insert an extra `--` before them:

```bash
pnpm run rename:plugin mvg-aktuell \
  --plugin boilerplate-plugin \
  --old-slug boilerplate-plugin \
  --company SmartMedia24 \
  --namespace MvgAktuell
```

For the SCF alternative, use `--plugin scf-boilerplate-plugin` (content placeholders stay `--old-slug boilerplate-plugin`). Calling the script without the required slug and `--company` prints the missing parameters.

This renames `plugins/<plugin>/` to `plugins/<slug>/` and updates plugin-specific placeholders (including company and namespace). Composer, zip, and sourcemap script names in `package.json` are updated as whole tokens, so renaming `boilerplate-plugin` leaves `scf-boilerplate-plugin` intact. The commented `PLUGIN_SLUGS` example in `.env.example` is updated the same way, one comma-separated field at a time. `.env` and `.env.local` are left unchanged. Then run `pnpm run composer:install:dev` so each `vendor-prefixed/` autoloader matches the new namespaces.

## Manual rename checklist

If you rename manually, replace:

- `boilerplate-theme` -> your theme slug and text domain.
- `boilerplate_theme` -> your hook/function prefix.
- `CompanyName` -> your company namespace prefix (PascalCase, e.g. `SmartMedia24`).
- `companyname` -> your Composer vendor and author slug (lowercase, e.g. `smartmedia24`).
- `https://companyname.example` -> your company URL placeholder (update the domain after rename).
- `CompanyName\\BoilerplateTheme\\` -> your full PSR-4 namespace root.
- `BoilerplateTheme` -> your PHP namespace segment (replaced by `pnpm run rename:theme`).
- `Boilerplate Theme` -> your display name.
- `BOILERPLATE_THEME_` -> your constant prefix.
- `boilerplate/example-block` -> your block namespace.

After a manual rename, run `pnpm run composer:install:dev`. Replacing strings in `composer.json` does not rebuild `vendor-prefixed/`.

## Development workflow

All commands below are run from the package directory (`boilerplate-theme/`, or the renamed slug).

### Daily development

Build all development assets once:

```bash
pnpm run development
pnpm run dev          # alias
```

Watch CSS, JS, blocks, and the plugins listed in `PLUGIN_SLUGS`. When `WP_CONTENT_PATH` is set, this also syncs `theme/` and those plugins once, then copies only files that change. An empty `PLUGIN_SLUGS` makes the plugin build and plugin sync exit immediately so the theme watchers keep running:

```bash
pnpm run watch
```

Sync the theme into Local without building (full copy). `pnpm run development` does this once after the asset build:

```bash
pnpm run sync:theme
pnpm run sync:theme --slug=hair-salon
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

`PLUGIN_SLUGS` drives the dev build, the watch build, and the production minify. An empty or commented key skips all three.

```bash
pnpm run development:plugins --slug=scf-boilerplate-plugin
pnpm run watch:plugins
pnpm run sync:plugin --slug=scf-boilerplate-plugin
pnpm run sync:plugins --optional
pnpm run watch:sync:plugins
pnpm run production:esbuild:plugins
```

`pnpm run development` runs `development:plugins` with the theme build, then `sync:theme`, then `sync:plugins`. `pnpm run production` minifies the listed plugins through `production:esbuild:plugins`. Composer, zip, and sourcemap cleanup stay as per-directory scripts.

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
- Strauss PHAR: downloaded via `bin/download-strauss.php` (PHP cURL, then `file_get_contents`, so a failed download cannot leave an empty PHAR)
- `require-dev` packages are **not** prefixed
- After `pnpm run rename:theme` or `pnpm run rename:plugin`, run `pnpm run composer:install:dev` (or `composer install --working-dir=theme` and `composer install --working-dir=plugins/<slug>`). The rename scripts skip `vendor-prefixed/`, so an install from before the rename leaves Timber and other packages on the placeholder namespace.
- The theme ships **Timber 2** (`timber/timber` `^2.0`, including Twig) as its only runtime dependency, listed in `extra.strauss.packages`, prefixed to `CompanyName\BoilerplateTheme\Timber\…` / `CompanyName\BoilerplateTheme\Twig\…`
- `update_call_sites: true` rewrites call sites only under the Composer autoload directory (`theme/src/`, plugin `includes/`). Root templates and `inc/` are not rewritten.
- `bin/fix-prefixed-twig.php` runs in the theme `prefix-namespaces` script after `strauss.phar` (not on `prefix-namespaces:dry-run`, and not in the plugins). Strauss does not rewrite the class names Twig writes into compiled templates (`use Twig\Template;` etc.), which would otherwise break every render with `Class "Twig\Template" not found`
- `delete_vendor_packages` removes the unprefixed sources after prefixing, so regenerate `vendor-prefixed/` with `composer install` in that package. `composer prefix-namespaces` alone cannot refetch deleted packages.

```bash
pnpm run composer:install:dev
composer prefix-namespaces:dry-run --working-dir=plugins/boilerplate-plugin
```

## Cursor AI configuration

The template lives in `cursor/` at the repository root (rules and skills). After renaming placeholders:

- Copy `cursor/` to `.cursor/` at the repository root (the workspace). Copy, not symlink.
- Keep `cursor/` as the reusable template.

```bash
# repository root
cp -a cursor .cursor
```

## Local usage

Sync `theme/` into the Local site. The destination folder name is the theme slug (`boilerplate-theme` until `pnpm run rename:theme` runs). `style.css` lands at `wp-content/themes/<slug>/style.css`.

```bash
# .env is created in the rename step. If it is missing: cp .env.example .env
# WP_CONTENT_PATH=/mnt/j/Local Sites/my-site/app/public/wp-content
pnpm run sync:theme
```

A symlink into WSL does not work: `\\wsl.localhost\...` and `ln -s` leave PHP `is_readable()` false, so WordPress treats the theme as incomplete. Paths, CLI overrides, and the watch behaviour are in [`../README.md`](../README.md#sync-the-theme-to-local).

Activate the theme after `vendor-prefixed/` exists. For the SCF demo plugin and example CPT fields, also activate **Secure Custom Fields**.

Plugin sync uses the same `WP_CONTENT_PATH`. Set `PLUGIN_SLUGS` to the folder names that should be built and copied (see [Plugin JavaScript](#plugin-javascript)). The files land in `wp-content/plugins/<slug>/`. Leave `PLUGIN_SLUGS` commented out to skip plugin build, watch, and sync.

The theme must load `theme/vendor-prefixed/autoload.php` from a Composer install that ran **after** the rename scripts. Otherwise `functions.php` shows an admin error that `vendor-prefixed` is missing, or `ThemeManager` shows that prefixed Timber was not found. The front end does not fatal: template stubs print a short HTML notice for administrators and a generic message for visitors, the example block is empty for visitors, and the search form keeps WordPress core markup.

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
- Run **`pnpm run rename:theme`**, then **`pnpm run rename:plugin`**, then **`pnpm run composer:install:dev`**, before copying `cursor/` to `.cursor/` at the repository root when starting a new project. `rename:theme` reads `THEME_SLUG` and `THEME_COMPANY` from `.env`.

## Common pitfalls

| Pitfall | Symptom | Mitigation |
| ------- | ------- | ---------- |
| Forgot `copy-blocks` after `block.json` edit | WordPress loads stale metadata | Run `development:copy-blocks` |
| New `@wordpress/*` import in blocks bundle | Resolve/bundle errors | Extend `wpGlobals` in `build-blocks.js` |
| Expecting full block folder under `theme/blocks/` | Only `block.json` is copied | Keep PHP classes in `theme/src/` and Twig in `theme/views/blocks/`; bundle JS via `javascript/` and `blocks/` |
| `composer install` in `theme/` before `pnpm run rename:theme` | Admin notice that Timber is missing from `vendor-prefixed`; front end shows the Timber fallback instead of Twig | Run `pnpm run composer:install:dev` again after the rename scripts |
| Theme activated without `theme/vendor-prefixed/` | Admin notice that `vendor-prefixed` is missing; front end shows the Timber fallback instead of Twig | `composer install --working-dir=theme` before activation |
| `pnpm run zip:theme` before the theme Composer install | ZIP has no prefixed Timber | `pnpm run bundle` runs production Composer installs, then zips. `zip:theme` alone archives `theme/` as it is |
| `view.js` present but not enqueued | Missing frontend behaviour | Register/enqueue handle in PHP; reference in `block.json` |
| Plugin script empty in WordPress | `build/` missing or outdated | `pnpm run development:plugins --slug=<folder>` and sync that slug |
| `pnpm install` before folder rename | Broken symlinks in `node_modules` | Rename package folder first, then run `pnpm install` |
| Symlink or UNC path from Local into WSL | `is_readable()` is false; stylesheet is missing | Sync with `pnpm run sync:theme` ([root README](../README.md#sync-the-theme-to-local)) |
| Renamed project but Cursor rules unchanged | AI uses old `boilerplate-theme` paths | Run `pnpm run rename:theme` (includes `cursor/` and `.vscode/settings.json` at the repository root) |

## Theme dependencies and scaffolding

The theme includes general infrastructure migrated from a production reference (navigation, Redux options, templates, admin hygiene):

- **Redux Framework** (recommended): powers Theme Options (logo, search page, breadcrumbs, 404 text, social links, custom CSS/JS). Without Redux, options fall back to defaults and an admin notice is shown.
- **Secure Custom Fields** (recommended): powers example CPT fields (`ExamplePostType`) and the SCF plugin boilerplate. The SCF admin menu is hidden; fields are registered in PHP.
- **Font Awesome** (recommended): icons in header search, footer social links, and back-to-top button.
- **Example CPT** (`example_item` + `example_category`): scaffold in `theme/src/PostTypes/ExamplePostType.php` — copy and adapt for project-specific post types.
- **Breadcrumb CPT mapping**: extend via the `boilerplate_theme_breadcrumb_cpt_page_map` filter.

## Timber views

The theme renders all frontend markup with [Timber 2](https://timber.github.io/docs/v2/) and Twig. The view structure follows the [Timber Starter Theme 2.x](https://github.com/timber/starter-theme/tree/2.x/views):

| Path | Purpose |
| ---- | ------- |
| `views/layouts/base.twig` | HTML skeleton (`wp_head`, `wp_body_open`, `wp_footer`) with the blocks `head`, `header`, `content`, `footer` |
| `views/templates/*.twig` | Page templates; each `{% extends 'layouts/base.twig' %}` and fills `{% block content %}` |
| `views/partials/*.twig` | Reusable includes (header, footer, menus, teasers, pagination, breadcrumb, search, preloader) |
| `views/blocks/*.twig` | Frontend markup of dynamic Gutenberg blocks |

Root templates (`index.php`, `page.php`, `single.php`, `archive.php`, `404.php`, `templates/template-search.php`) contain no markup: they build the context and call `Timber::render()`. If prefixed Timber is unavailable, each stub returns early via `boilerplate_theme_bail_if_timber_unavailable()` and prints a minimal HTML fallback instead. More specific Twig files are picked up automatically where the stub lists fallbacks (e.g. `templates/single-{post_type}.twig`, `templates/page-{slug}.twig`, `templates/archive-{post_type}.twig`).

**Imports:** Root templates and `inc/` are outside the Strauss autoload scope, so their call sites are not rewritten. Always import the prefixed class: `use CompanyName\BoilerplateTheme\Timber\Timber;`.

**Global context** (`Theme\TimberIntegration`, filter `timber/context`):

| Key | Content |
| --- | ------- |
| `options` | Curated Redux theme options with defaults (works without Redux): `logo`, `logo_footer` (media arrays, use `.url`), `website_title`, `show_breadcrumb`, `show_preloader`, `preloader_style`, `show_backtotop`, `social.*`, `error_title`, `error_text`, `error_btn` |
| `header_menu`, `footer_menu_1` … `footer_menu_3` | `Timber\Menu` per location (`null` if unassigned); footer menus are limited to depth 1 |
| `search_url` | URL of the search page (`search_page` option or first page using the search template) |
| `typography_classes` | Tailwind Typography classes for content wrappers |
| `strip_header_footer_links` | Result of the `boilerplate_theme_strip_header_footer_links` filter |

Twig never calls `ThemeOptions` directly; security and asset options (`disable_*`, `custom_css`, `custom_js`) stay in PHP.

**Theme Twig functions:** `breadcrumb_items()` returns the crumbs from `Theme\Breadcrumb`; `the_content()` returns the filtered content of the current post like WordPress' `the_content()` (Timber's `post.content` ignores `<!--more-->`).

**Menus:** There is no PHP walker. `partials/menu.twig` / `menu-item.twig` render the header menu with the `f-header__*` BEM classes used by `flexi-header.css` and the header JavaScript; `partials/footer-menu.twig` renders the footer menus. Filters such as `nav_menu_link_attributes` and `walker_nav_menu_start_el` no longer run, because the markup is Twig rather than `wp_nav_menu()`.

**Escaping and i18n:** Timber disables Twig autoescaping, so escape explicitly with `|esc_html`, `|esc_attr`, `|esc_url` or `|wp_kses_post`. Translate with `{{ __('Text', 'boilerplate-theme') }}`, `_x()`, `_n()`. Note that `wp i18n make-pot` does not scan `.twig` files; strings used only in Twig are not extracted into the POT file.

## Plugin boilerplates

| Plugin | Options stack | Notes |
|--------|---------------|--------|
| `plugins/boilerplate-plugin/` | WordPress Settings API | Shortcode, AJAX skeleton, Strauss demo |
| `plugins/scf-boilerplate-plugin/` | Secure Custom Fields | Text fields + shortcode `[boilerplate_plugin]`; requires the SCF WP plugin |

Theme Options still use Redux (`ThemeOptions`); CPT meta and the SCF plugin alternative use Secure Custom Fields. See `plugins/scf-boilerplate-plugin/README.md` for setup and local linking.

## Adding blocks

1. Copy `blocks/example-block/` to a new block directory.
2. Update the block name, title, attributes, and editor UI.
3. Add a PHP renderer class in `theme/src/Blocks/Blocks/` that prepares the view data and calls `Timber::compile( 'blocks/<slug>.twig', $context )`.
4. Register the block in `theme/src/Blocks/BlockManager.php`.
5. Add a Twig template in `theme/views/blocks/<slug>.twig`.
6. Add styles under `tailwind/custom/components/`.
7. Run `pnpm run development:copy-blocks` and rebuild block assets when ready.

For a step-by-step checklist, see [`../cursor/skills/boilerplate-theme-create-block/SKILL.md`](../cursor/skills/boilerplate-theme-create-block/SKILL.md).

## Related documentation

- [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md) — asset pipeline architecture, Node script API, Tailwind details
- [`../README.md`](../README.md) — WSL clone, sync into Local WP `wp-content/themes/<slug>`
