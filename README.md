# Boilerplates

This directory contains reusable WordPress development boilerplates. Use `boilerplate-theme/` as a starting point for new theme and plugin projects.

## WordPress development environment

Clone this repository into the WSL filesystem (`/home/...`). WordPress stays in a separate **Local WP** install on Windows. Keep the repository files in WSL. Open the repository in Cursor or VS Code with **Remote – WSL** and run Node, pnpm, and Composer in the Linux shell.

Local’s PHP cannot read the theme through a symlink into WSL. A target of `\\wsl.localhost\...`, or a link created with `ln -s`, leaves `is_readable()` false, so WordPress reports that `style.css` is missing. Copy the theme onto the Windows drive with [Sync the theme to Local](#sync-the-theme-to-local).

```text
/home/you/projects/my-project/boilerplate-theme/theme/          source (WSL)
        │  pnpm run sync:theme
        ▼
/mnt/j/Local Sites/my-site/app/public/wp-content/themes/boilerplate-theme/
├── style.css          contents of theme/, not a nested theme/ folder
├── functions.php
└── …
```

`J:\Local Sites\my-site\app\public\wp-content` is `/mnt/j/Local Sites/my-site/app/public/wp-content` in WSL (lowercase drive letter, backslashes become slashes, spaces stay). A site on `C:` uses `/mnt/c/Users/you/Local Sites/...`. The repository stays under `/home/...`.

```bash
mkdir -p ~/projects
cd ~/projects
git clone https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git my-project
cd my-project
rm -rf .git
git init
```

Remove `.git` and run `git init` only when starting a new project from the blueprint. If you keep the upstream history, skip that step.

### Sync the theme to Local

From the package directory (`boilerplate-theme/`, or the renamed slug):

```bash
cp .env.example .env
```

Set `WP_CONTENT_PATH` in `.env` to the WSL path of that site’s `wp-content`. `.env` is gitignored. The sync copies the contents of `theme/` into `wp-content/themes/<slug>/`.

The default slug is `boilerplate-theme` (the constant in `node_scripts/sync-theme.js`). `pnpm run rename:theme` reads `THEME_SLUG` and `THEME_COMPANY` from `.env` and takes no slug or company arguments. It rewrites that default and the commented examples in `.env.example`. `THEME_SLUG`, `THEME_SYNC_SLUG` (alias), or `THEME_SYNC_TARGET` in `.env` or `.env.local` are not rewritten and win over the default.

```bash
pnpm run sync:theme
pnpm run sync:theme --dry-run
pnpm run sync:theme --slug=hair-salon
pnpm run sync:theme --target="/mnt/j/Local Sites/my-site/app/public/wp-content/themes/hair-salon"
```

`--target` is the theme folder itself (`style.css` goes directly inside it). `--slug` with `WP_CONTENT_PATH` uses `…/wp-content/themes/<slug>`. When both a full target and a wp-content path are set, the wp-content path plus slug is used. If only `THEME_SYNC_TARGET` is set, `--slug` renames that folder.

`pnpm run development` syncs the theme once after the asset build, then syncs plugins. `pnpm run watch` syncs once, then copies only files that change under `theme/`. `node_modules`, `.git`, `vendor` (Composer sources), and `*.map` files are left behind. `vendor-prefixed/` is copied — Local PHP loads that autoloader.

Plugin build, watch, and sync use the same `WP_CONTENT_PATH` and one list, `PLUGIN_SLUGS` (comma-separated folder names under `plugins/`). Leave that key empty or commented out and all three skip with exit 0. Set it in `.env`, for example `PLUGIN_SLUGS=scf-boilerplate-plugin`. The copy lands in `wp-content/plugins/<slug>/` with the plugin files directly in that folder, including `build/` after the driver has created it. For the SCF plugin the folder is `scf-boilerplate-plugin` and the bootstrap file stays `boilerplate-plugin.php`. The same excludes apply, and `vendor-prefixed/` is copied. Do not symlink plugins from `\\wsl.localhost\...` or with `ln -s`: `is_readable()` is false there too.

```bash
pnpm run development:plugins --slug=scf-boilerplate-plugin
pnpm run watch:plugins
pnpm run sync:plugin --slug=scf-boilerplate-plugin
pnpm run sync:plugins --optional
pnpm run watch:sync:plugins
```

`--slug` builds or syncs that one plugin even when it is absent from `PLUGIN_SLUGS`. With the list set and `WP_CONTENT_PATH` missing, the build still runs; `sync:plugins --optional` skips, and `sync:plugins` without `--optional` exits 1.

## Contents

- **`boilerplate-theme/`** — WordPress development package with deployable `theme/`, example Gutenberg block, `plugins/boilerplate-plugin/`, and `plugins/scf-boilerplate-plugin/`.
- **`cursor/`** — Cursor AI configuration template (rules and skills). Copy to the repository root as `.cursor/` after the rename scripts.

## Documentation

| Topic                                                                                    | Location                                                                         |
| ---------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| Project setup, rename workflow, pnpm/Composer commands, theme sync, best practices | [`boilerplate-theme/README.md`](boilerplate-theme/README.md)                     |
| Asset pipeline architecture, Node script API, Tailwind internals, Strauss details        | [`boilerplate-theme/docs/DEVELOPMENT.md`](boilerplate-theme/docs/DEVELOPMENT.md) |
| Cursor AI rules and skills template                                                      | [`cursor/`](cursor/) (copy to the repository root as `.cursor/` after rename)    |

## Getting started

1. Install Local WP on Windows, outside this repository, and clone [Wordpress-Dev-Blueprint](https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git) into the WSL filesystem (see [WordPress development environment](#wordpress-development-environment)). Open that folder with Remote – WSL and sync the theme into `wp-content/themes/<slug>` ([Sync the theme to Local](#sync-the-theme-to-local)).
2. Follow the [recommended project setup order](boilerplate-theme/README.md#recommended-project-setup-order) in `boilerplate-theme/README.md` — rename the package folder, install pnpm dependencies, run the rename scripts, install Composer dependencies, build assets, configure theme sync, then copy `cursor/` to `.cursor/`.
