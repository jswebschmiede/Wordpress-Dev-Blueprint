# Boilerplate Plugin

Reusable WordPress plugin boilerplate.

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

Replace these values when starting a new plugin (or run `rename-plugin.js` from the package root):

```bash
node node_scripts/rename-plugin.js <slug> \
  --plugin boilerplate-plugin \
  --old-slug boilerplate-plugin \
  --company <Company> \
  --namespace <Namespace> \
  --dry-run
```

- `Boilerplate Plugin` -> your plugin display name.
- `boilerplate-plugin` -> your plugin slug and text domain.
- `boilerplate_plugin` -> your shortcode/action prefix.
- `BOILERPLATE_PLUGIN_` -> your constant prefix.
- `CompanyName` -> your company namespace prefix (PascalCase).
- `companyname` -> your Composer vendor and author slug.
- `https://companyname.example` -> your company URL placeholder.
- `CompanyName\\BoilerplatePlugin\\` -> your PSR-4 namespace.
- `BoilerplatePlugin` -> your main plugin class name.

## Local Usage

Theme files are copied into Local with `pnpm run sync:theme` ([repository README](../../../README.md#sync-the-theme-to-local)). This plugin is copied only when its folder is listed in `PLUGIN_SLUGS` (or passed with `--slug`). The files land in `wp-content/plugins/boilerplate-plugin/`. Leave `PLUGIN_SLUGS` empty or commented out to skip plugin build, watch, and sync. Do not symlink the plugin from `\\wsl.localhost\...` or with `ln -s`: Local’s PHP `is_readable()` is false for those targets.

```bash
pnpm run development:plugins --slug=boilerplate-plugin
pnpm run watch:plugins
pnpm run sync:plugin --slug=boilerplate-plugin
pnpm run sync:plugins --optional
pnpm run watch:sync:plugins
```

Install Composer dependencies in the plugin directory before activating the plugin:

```bash
composer install --working-dir=plugins/boilerplate-plugin
```

This runs [Strauss](https://github.com/BrianHenryIE/strauss) automatically and generates `vendor-prefixed/` with prefixed runtime dependencies. WordPress loads `vendor-prefixed/autoload.php` at runtime. If `rename-plugin.js` already ran, run this install afterwards: the rename script skips `vendor-prefixed/`.

Preview prefixing without changes:

```bash
composer prefix-namespaces:dry-run --working-dir=plugins/boilerplate-plugin
```

## Strauss demo

Settings → Boilerplate Plugin shows a read-only **Strauss test (UUID)** field. It uses `ramsey/uuid` loaded from `vendor-prefixed/` to verify prefixed autoloading works.

`PLUGIN_SLUGS` in `.env` selects which plugins `development:plugins`, `watch:plugins`, and `production:esbuild:plugins` build. An empty list skips them. To build only this plugin:

```bash
pnpm run development:plugins --slug=boilerplate-plugin
pnpm run production:esbuild:plugins
```

Run build scripts only when you intentionally want to generate assets. The sync copies `build/` after that.

## Included Examples

- Settings API page under Settings > Boilerplate Plugin.
- `[boilerplate_plugin]` shortcode with a separate view template.
- AJAX endpoint skeleton using nonce verification.
- Admin and frontend JavaScript entries bundled into `build/`.
