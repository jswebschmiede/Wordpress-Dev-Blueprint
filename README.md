# Boilerplates

This directory contains reusable WordPress development boilerplates. Use `boilerplate-theme/` as a starting point for new theme and plugin projects.

## WordPress development environment

Clone this repository as its own folder. WordPress stays in a separate install. Symlink the theme and the plugins into that install’s `wp-content/themes` and `wp-content/plugins`.

Work in WSL or on Linux. On Windows, open this repository in Cursor or VS Code with **Remote – WSL** and run the commands in the Linux shell inside WSL.

Typical WordPress locations:

- **Local WP** — from WSL the site is usually under `/mnt/c/...`, for example `/mnt/c/Users/you/Local Sites/my-site/app/public`.
- **Linux** — a normal install path, for example `/var/www/my-site`.

```text
~/projects/my-project/              this repository (workspace root)
├── .vscode/settings.json
├── boilerplate-theme/
├── cursor/
└── README.md

/path/to/wordpress/                 separate WordPress install
├── wp-admin/
├── wp-content/
│   ├── themes/boilerplate-theme    symlink → repository theme/
│   └── plugins/                    symlinks → repository plugins/
└── wp-config.php
```

```bash
git clone https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git my-project
cd my-project
rm -rf .git
git init
```

Remove `.git` and run `git init` only when starting a new project from the blueprint. If you keep the upstream history, skip that step.

### Symlink theme and plugins

Run these from the repository root. Theme and plugins use the same `ln -s` pattern. Quote paths that contain spaces (Local WP site names often do).

Local WP, from WSL:

```bash
WP_CONTENT="/mnt/c/Users/you/Local Sites/my-site/app/public/wp-content"

ln -s "$(pwd)/boilerplate-theme/theme" "$WP_CONTENT/themes/boilerplate-theme"
ln -s "$(pwd)/boilerplate-theme/plugins/boilerplate-plugin" "$WP_CONTENT/plugins/boilerplate-plugin"
ln -s "$(pwd)/boilerplate-theme/plugins/scf-boilerplate-plugin" "$WP_CONTENT/plugins/scf-boilerplate-plugin"
```

Linux install:

```bash
WP_CONTENT="/var/www/my-site/wp-content"

ln -s "$(pwd)/boilerplate-theme/theme" "$WP_CONTENT/themes/boilerplate-theme"
ln -s "$(pwd)/boilerplate-theme/plugins/boilerplate-plugin" "$WP_CONTENT/plugins/boilerplate-plugin"
ln -s "$(pwd)/boilerplate-theme/plugins/scf-boilerplate-plugin" "$WP_CONTENT/plugins/scf-boilerplate-plugin"
```

After you rename the package folder or a plugin, use those directory names in the source path and the same slug as the link name WordPress sees (`themes/<theme-slug>`, `plugins/<plugin-slug>`).

## Contents

- **`boilerplate-theme/`** — WordPress development package with deployable `theme/`, example Gutenberg block, `plugins/boilerplate-plugin/`, and `plugins/scf-boilerplate-plugin/`.
- **`cursor/`** — Cursor AI configuration template (rules and skills). Copy to the repository root as `.cursor/` after the rename scripts.

## Documentation

| Topic                                                                                    | Location                                                                         |
| ---------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| Project setup, rename workflow, pnpm/Composer commands, WordPress linking, best practices | [`boilerplate-theme/README.md`](boilerplate-theme/README.md)                     |
| Asset pipeline architecture, Node script API, Tailwind internals, Strauss details        | [`boilerplate-theme/docs/DEVELOPMENT.md`](boilerplate-theme/docs/DEVELOPMENT.md) |
| Cursor AI rules and skills template                                                      | [`cursor/`](cursor/) (copy to the repository root as `.cursor/` after rename)    |

## Getting started

1. Set up WordPress outside this repository (Local WP or a Linux install) and clone [Wordpress-Dev-Blueprint](https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git) as its own folder (see [WordPress development environment](#wordpress-development-environment)). On Windows, open that folder with Remote – WSL.
2. Follow the [recommended project setup order](boilerplate-theme/README.md#recommended-project-setup-order) in `boilerplate-theme/README.md` — rename the package folder, install pnpm dependencies, run the rename scripts, install Composer dependencies, build assets, symlink the theme and plugins into WordPress, then copy `cursor/` to `.cursor/`.
