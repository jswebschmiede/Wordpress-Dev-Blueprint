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

WordPress stays outside the repository. Symlink this plugin into the real `wp-content/plugins` the same way as the theme. On Windows, open the repository with Remote – WSL and run tooling in Linux. The three symlink cases (native Linux, Local with the repo in WSL, Local with the repo on a Windows drive), plus `mklink /D`, are in [`../../../README.md`](../../../README.md#symlink-theme-and-plugins).

Native Linux, from the repository root:

```bash
WP_CONTENT="/var/www/my-site/wp-content"
ln -s "$(pwd)/boilerplate-theme/plugins/boilerplate-plugin" "$WP_CONTENT/plugins/boilerplate-plugin"
```

Local on Windows, repository in WSL. PowerShell (Administrator or Developer Mode). WSL must be running. A `C:` site uses `C:\Users\you\Local Sites\...` as `$Link`.

```powershell
$Link = "J:\Local Sites\my-site\app\public\wp-content\plugins\boilerplate-plugin"
$Target = "\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project\boilerplate-theme\plugins\boilerplate-plugin"
New-Item -ItemType SymbolicLink -Path $Link -Target $Target
```

If that UNC path does not resolve, clone the repository onto a Windows drive and set `$Target` to that Windows path (for example `J:\dev\my-project\boilerplate-theme\plugins\boilerplate-plugin`).

After `rename-plugin.js`, use the new plugin directory in the source path and that slug as the link name.

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
