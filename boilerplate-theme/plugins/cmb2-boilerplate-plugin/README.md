# Boilerplate Plugin (CMB2 alternative)

Demo WordPress plugin that uses **CMB2** for options (text fields) and outputs them via a shortcode.

This is an **alternative** to `plugins/boilerplate-plugin/` (Settings API). Both use the same rename placeholders so either can be turned into a project plugin.

## Requirements

- PHP 8.3+
- [CMB2](https://wordpress.org/plugins/cmb2/) WordPress plugin (active)
- Composer install in this directory (generates `vendor-prefixed/`)

## Structure

```text
cmb2-boilerplate-plugin/
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

The **directory** stays `cmb2-boilerplate-plugin` so both alternatives can coexist in the repo.

## Rename for a project

```bash
node node_scripts/rename-plugin.js mvg-aktuell \
  --plugin cmb2-boilerplate-plugin \
  --old-slug boilerplate-plugin \
  --company SmartMedia24 \
  --namespace MvgAktuell
```

## Usage

1. Symlink or copy this folder to `wp-content/plugins/cmb2-boilerplate-plugin`.
2. Install Composer dependencies:

```bash
composer install --working-dir=plugins/cmb2-boilerplate-plugin
```

3. Activate **CMB2** and **Boilerplate Plugin**.
4. Configure options under **Boilerplate Plugin** in the admin menu.
5. Output with the shortcode:

```text
[boilerplate_plugin]
```

## Options

| Field | CMB2 type | Description |
|-------|-----------|-------------|
| `demo_headline` | `text` | Shortcode headline |
| `demo_intro` | `textarea` | Shortcode intro text |

Options are stored under `boilerplate_plugin_options`.

## Build scripts

From the development package root:

```bash
pnpm run development:esbuild:plugin:cmb2-boilerplate-plugin
pnpm run production:esbuild:plugin:cmb2-boilerplate-plugin
```

## Relationship to boilerplate-plugin

| Plugin directory | Options stack |
|------------------|---------------|
| `boilerplate-plugin` | WordPress Settings API |
| `cmb2-boilerplate-plugin` | CMB2 |
