# Boilerplates

This directory contains reusable WordPress development boilerplates. Use `boilerplate-theme/` as a starting point for new theme and plugin projects.

## WordPress development environment

This repository **is** the development tree that lives beside WordPress as `_wp-content-dev/` in your site’s web root. It does **not** include WordPress core — set up a local site first (for example with [Local](https://localwp.com/)), then clone this repository into that site’s `app/public/` directory.

```text
app/public/                         WordPress web root
├── wp-admin/
├── wp-content/
├── wp-config.php
└── _wp-content-dev/                clone target (this repository)
    ├── boilerplate-theme/
    ├── cursor/
    ├── ps/
    └── README.md
```

Clone into your existing WordPress web root. The folder name `_wp-content-dev` is the convention used throughout this documentation; you may choose another name, but then adjust the paths in the examples accordingly.

```bash
cd /path/to/your-site/app/public
git clone https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git _wp-content-dev
cd _wp-content-dev
rm -rf .git
git init
```

On Windows (PowerShell):

```powershell
cd C:\path\to\your-site\app\public
git clone https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git _wp-content-dev
cd _wp-content-dev
Remove-Item -Recurse -Force .git
git init
```

Remove `.git` and run `git init` only when starting a new project from the blueprint. If you keep the upstream history, skip that step.

## Contents

- **`boilerplate-theme/`** — WordPress development package with deployable `theme/`, example Gutenberg block, and `plugins/boilerplate-plugin/`.
- **`cursor/`** — Cursor AI configuration template (rules and skills).
- **`ps/`** — Windows PowerShell helpers for theme/plugin symlinks and Cursor config (configure paths at the top of each script before running).

## Documentation

| Topic                                                                   | Location                                                                         |
| ----------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| Project setup, rename workflow, dependencies, builds, WordPress linking | [`boilerplate-theme/README.md`](boilerplate-theme/README.md)                     |
| Asset pipeline, Node scripts, architecture, pitfalls                    | [`boilerplate-theme/docs/DEVELOPMENT.md`](boilerplate-theme/docs/DEVELOPMENT.md) |

## Getting started

1. Set up a local WordPress site and clone [Wordpress-Dev-Blueprint](https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git) into its web root as `_wp-content-dev` (see [WordPress development environment](#wordpress-development-environment)).
2. Follow the [recommended project setup order](boilerplate-theme/README.md#recommended-project-setup-order) in `boilerplate-theme/README.md`.
