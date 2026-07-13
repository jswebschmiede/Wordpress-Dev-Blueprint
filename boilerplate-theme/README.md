# Boilerplate Theme

Reusable WordPress theme and plugin development boilerplate with Gutenberg blocks and Tailwind CSS.

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

## Rename Theme Placeholders

Use the rename script from this directory:

```bash
node node_scripts/rename-theme.js sw-soltau --dry-run
node node_scripts/rename-theme.js sw-soltau
```

For `sw-soltau`, the script derives:

- Text domain, asset handle prefix, and paths: `sw-soltau`
- Hook/function prefix: `sw_soltau`
- PHP namespace: `Swsoltau`
- Theme name: `Sw Soltau`
- Constant prefix: `SWSOLTAU_`
- Block namespace: `sw-soltau/example-block`

The script replaces placeholders in this package and in `_wp-content-dev/cursor/` (rules and skills, `.md` and `.mdc`). It only replaces file contents and does not rename folders.

### Recommended order for a new project

1. Run `rename-theme.js` with your slug
2. Link or copy theme and plugin into WordPress
3. Copy `_wp-content-dev/cursor/` to your workspace root as `.cursor/`

## Manual Rename Checklist

If you rename manually, replace:

- `boilerplate-theme` -> your theme slug and text domain.
- `boilerplate_theme` -> your hook/function prefix.
- `SmartMedia24\\BoilerplateTheme\\` -> your PSR-4 namespace root (company prefix optional).
- `BoilerplateTheme` -> your PHP namespace segment (replaced by `rename-theme.js`).
- `Boilerplate Theme` -> your display name.
- `BOILERPLATE_THEME_` -> your constant prefix.
- `boilerplate/example-block` -> your block namespace.

## Cursor AI Configuration

The template lives in `_wp-content-dev/cursor/` (rules and skills). After renaming placeholders:

- Copy `cursor/` to your WordPress workspace root as `.cursor/` (copy, not symlink).
- The template stays under `_wp-content-dev/` for reuse.

```bash
cp -r "_wp-content-dev/cursor" ".cursor"
```

On Windows, use `_wp-content-dev/ps/copy-cursor-config.ps1` or `Copy-Item -Recurse`.

## Local Usage

Create a symlink or copy the deployable theme directory to WordPress:

```bash
ln -s "_wp-content-dev/boilerplate-theme/theme" "wp-content/themes/boilerplate-theme"
ln -s "_wp-content-dev/boilerplate-theme/plugins/boilerplate-plugin" "wp-content/plugins/boilerplate-plugin"
```

Install Composer dependencies before activating the theme or plugin:

```bash
composer install --working-dir=theme
composer install --working-dir=plugins/boilerplate-plugin
```

For development tooling (PHPCS, i18n), install Composer dependencies in the package root:

```bash
composer install
composer php:lint
composer make-pot:theme
composer make-pot:plugin
```

Root-level tooling:

- `composer.json` and `phpcs.xml` at the package root provide PHPCS for the entire package (theme and plugins).
- Theme and plugin `composer.json` files only define runtime autoloading.
- `eslint.config.js`, `prettier.config.js`, `.prettierrc`, and `.prettierignore` provide JavaScript and CSS formatting/linting defaults.
- `.editorconfig`, `.npmrc`, and `.gitignore` keep editor, dependency, and generated-file behavior consistent.

Build, lint, and Composer scripts are documented in `package.json`. See [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md) for the asset pipeline, Node scripts, and common pitfalls. Run builds only when you intentionally want to install dependencies or generate assets.

For deployment, run `pnpm run production` to build optimized assets, then `pnpm run zip` or `pnpm run bundle` to create ZIP archives under `zip/`.

## Theme dependencies and scaffolding

The theme includes general infrastructure migrated from a production reference (navigation, Redux options, templates, admin hygiene):

- **Redux Framework** (recommended): powers Theme Options (logo, search page, breadcrumbs, 404 text, social links, custom CSS/JS). Without Redux, options fall back to defaults and an admin notice is shown.
- **Font Awesome** (recommended): icons in header search, footer social links, and back-to-top button.
- **Example CPT** (`example_item` + `example_category`): scaffold in `theme/src/PostTypes/ExamplePostType.php` — copy and adapt for project-specific post types.
- **Breadcrumb CPT mapping**: extend via the `boilerplate_theme_breadcrumb_cpt_page_map` filter.

## Adding Blocks

1. Copy `blocks/example-block/` to a new block directory.
2. Update the block name, title, attributes, and editor UI.
3. Add a PHP renderer class in `theme/src/Blocks/Blocks/`.
4. Register the block in `theme/src/Blocks/BlockManager.php`.
5. Add a template in `theme/template-parts/blocks/`.
6. Add styles under `tailwind/custom/components/`.
7. Run the block copy/build scripts when you are ready to generate assets.
