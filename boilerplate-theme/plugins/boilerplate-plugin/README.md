# Boilerplate Plugin

Reusable WordPress plugin boilerplate based on the MVG Aktuell plugin structure.

## Structure

```text
boilerplate-plugin/
├── boilerplate-plugin.php
├── composer.json
├── includes/
│   ├── BoilerplatePlugin.php
│   ├── Assets/Assets.php
│   ├── Backend/PluginOptions.php
│   ├── Shortcodes/Shortcode.php
│   └── Ajax/AjaxHandler.php
├── assets/
│   ├── admin/js/dashboard.js
│   └── frontend/js/frontend.js
├── build/
└── views/shortcode-view.php
```

## Rename Checklist

Replace these values when starting a new plugin:

- `Boilerplate Plugin` -> your plugin display name.
- `boilerplate-plugin` -> your plugin slug and text domain.
- `boilerplate_plugin` -> your shortcode/action prefix.
- `BOILERPLATE_PLUGIN_` -> your constant prefix.
- `SmartMedia24\\BoilerplatePlugin\\` -> your PSR-4 namespace.
- `BoilerplatePlugin` -> your main plugin class name.

## Local Usage

Create a symlink or copy this folder to `wp-content/plugins/boilerplate-plugin`.

Install Composer dependencies in the plugin directory before activating the plugin:

```bash
composer install --working-dir=plugins/boilerplate-plugin
```

Build scripts are defined in the development package root `package.json`, matching the reference structure:

```bash
pnpm run development:esbuild:plugin:boilerplate-plugin
pnpm run production:esbuild:plugin:boilerplate-plugin
```

Run build scripts only when you intentionally want to generate assets.

## Included Examples

- Settings API page under Settings > Boilerplate Plugin.
- `[boilerplate_plugin]` shortcode with a separate view template.
- AJAX endpoint skeleton using nonce verification.
- Admin and frontend JavaScript entries bundled into `build/`.
