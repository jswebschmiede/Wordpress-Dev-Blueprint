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

Create a symlink or copy this folder to `wp-content/plugins/boilerplate-plugin`.

Install Composer dependencies in the plugin directory before activating the plugin:

```bash
composer install --working-dir=plugins/boilerplate-plugin
```

This runs [Strauss](https://github.com/BrianHenryIE/strauss) automatically and generates `vendor-prefixed/` with prefixed runtime dependencies. WordPress loads `vendor-prefixed/autoload.php` at runtime.

Preview prefixing without changes:

```bash
composer prefix-namespaces:dry-run --working-dir=plugins/boilerplate-plugin
```

## Strauss demo

Settings → Boilerplate Plugin shows a read-only **Strauss test (UUID)** field. It uses `ramsey/uuid` loaded from `vendor-prefixed/` to verify prefixed autoloading works.

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
