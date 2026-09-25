# Boilerplate Plugin (SCF alternative)

Demo WordPress plugin that uses **Secure Custom Fields (SCF)** for options (text fields) and outputs them via a shortcode.

This is an **alternative** to `plugins/boilerplate-plugin/` (Settings API). Both use the same rename placeholders so either can be turned into a project plugin.

## Requirements

- PHP 8.3+
- [Secure Custom Fields](https://wordpress.org/plugins/secure-custom-fields/) WordPress plugin (active)
- Composer install in this directory (generates `vendor-prefixed/`)

## Structure

```text
scf-boilerplate-plugin/
├── boilerplate-plugin.php
├── composer.json
├── includes/
│   ├── BoilerplatePlugin.php
│   ├── Assets/Assets.php
│   ├── Backend/PluginOptions.php
│   └── Shortcodes/Shortcode.php
├── assets/frontend/js/frontend.js
├── build/
└── views/shortcode-view.php
```

## Placeholders

Content placeholders match the Settings API plugin (for `rename-plugin.js`):

- `boilerplate-plugin` — slug / text domain / handles
- `boilerplate_plugin` — shortcode / option keys
- `BoilerplatePlugin` — PHP namespace segment / main class
- `Boilerplate Plugin` — display name
- `BOILERPLATE_PLUGIN_` — PHP constants
- `CompanyName` / `companyname` — company namespace and Composer vendor

The **directory** stays `scf-boilerplate-plugin` so both alternatives can coexist in the repo.

## Rename for a project

```bash
node node_scripts/rename-plugin.js mvg-aktuell \
  --plugin scf-boilerplate-plugin \
  --old-slug boilerplate-plugin \
  --company SmartMedia24 \
  --namespace MvgAktuell
```

## Usage

1. From the repository root, symlink this folder into the real WordPress `wp-content/plugins` (same `ln -s` pattern as the theme). On Windows, use Remote – WSL. See [`../../../README.md`](../../../README.md#symlink-theme-and-plugins).

Local WP, from WSL:

```bash
WP_CONTENT="/mnt/c/Users/you/Local Sites/my-site/app/public/wp-content"
ln -s "$(pwd)/boilerplate-theme/plugins/scf-boilerplate-plugin" "$WP_CONTENT/plugins/scf-boilerplate-plugin"
```

Linux install:

```bash
WP_CONTENT="/var/www/my-site/wp-content"
ln -s "$(pwd)/boilerplate-theme/plugins/scf-boilerplate-plugin" "$WP_CONTENT/plugins/scf-boilerplate-plugin"
```

2. Install Composer dependencies (again after `rename-plugin.js`; that script skips `vendor-prefixed/`, including the prefixed project autoload):

```bash
composer install --working-dir=plugins/scf-boilerplate-plugin
```

3. Activate **Secure Custom Fields** and **Boilerplate Plugin**.
4. Configure options under **Boilerplate Plugin** in the admin menu.
5. Output with the shortcode:

```text
[boilerplate_plugin]
```

## Options

| Field | SCF type | Description |
|-------|----------|-------------|
| `demo_headline` | `text` | Shortcode headline |
| `demo_intro` | `textarea` | Shortcode intro text |

Options are stored via SCF options (`get_field( $key, 'option' )`).

## Build scripts

From the development package root:

```bash
pnpm run development:esbuild:plugin:scf-boilerplate-plugin
pnpm run production:esbuild:plugin:scf-boilerplate-plugin
```

## Relationship to boilerplate-plugin

| Plugin directory | Options stack |
|------------------|---------------|
| `boilerplate-plugin` | WordPress Settings API |
| `scf-boilerplate-plugin` | Secure Custom Fields |
