# Boilerplates

This directory contains reusable WordPress development boilerplates. Use `boilerplate-theme/` as a starting point for new theme and plugin projects.

## WordPress development environment

Clone this repository into the WSL filesystem (`/home/...`). WordPress stays in a separate **Local WP** install on Windows. Keep the repository files in WSL.

Develop in WSL. Open the repository in Cursor or VS Code with **Remote – WSL** and run Node, pnpm, Composer, and the other tooling in the Linux shell. Local’s PHP runs on Windows, so create the symlinks on Windows. Map the WSL path to a drive letter, then point the links at that drive. See [Symlink theme and plugins](#symlink-theme-and-plugins).

```text
/home/you/projects/my-project/               this repository (WSL filesystem)
├── .vscode/settings.json
├── boilerplate-theme/
├── cursor/
└── README.md

J:\Local Sites\my-site\app\public\           Local WP (Windows)
├── wp-admin/
├── wp-content/
│   ├── themes/boilerplate-theme             symlink → W:\boilerplate-theme\theme
│   └── plugins/                             symlinks → W:\boilerplate-theme\plugins\...
└── wp-config.php
```

A site on `C:` uses `C:\Users\you\Local Sites\my-site\app\public`. From WSL that folder is `/mnt/c/...` or `/mnt/j/...`. The repository stays under `/home/...`.

```bash
mkdir -p ~/projects
cd ~/projects
git clone https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git my-project
cd my-project
rm -rf .git
git init
```

Remove `.git` and run `git init` only when starting a new project from the blueprint. If you keep the upstream history, skip that step.

### Symlink theme and plugins

The link name is the slug WordPress loads (`themes/<theme-slug>`, `plugins/<plugin-slug>`). After a rename, the target uses the new directory and the link name is that slug. The link path must not already exist. Quote paths that contain spaces (`Local Sites` usually does).

Keep the clone in the WSL filesystem (`/home/you/projects/my-project`) and edit it with Remote – WSL. WSL must be running. The distro name is what `wsl -l -q` prints (for example `Ubuntu-22.04`): `/home/you/projects/my-project` is `\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project`.

Map that UNC path to a drive letter, then symlink Local to the drive. A symlink whose target is `\\wsl.localhost\...` itself often leaves `style.css` unreadable. WordPress then reports that the stylesheet is not readable and the theme looks incomplete.

`ln -s` from WSL stores a Linux target such as `/home/you/...`, which Local’s PHP cannot open.

PowerShell as Administrator, or a normal window when [Developer Mode](https://learn.microsoft.com/windows/apps/get-started/enable-your-device-for-development) is on. A site on `C:` uses `C:\Users\you\Local Sites\...` as `$Link`. If `W:` is already in use, pick another letter.

```powershell
net use W: "\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project"

$Link = "J:\Local Sites\my-site\app\public\wp-content\themes\boilerplate-theme"
$Target = "W:\boilerplate-theme\theme"
New-Item -ItemType SymbolicLink -Path $Link -Target $Target
```

The same link with `mklink /D` (link first, then target):

```bat
mklink /D "J:\Local Sites\my-site\app\public\wp-content\themes\boilerplate-theme" "W:\boilerplate-theme\theme"
```

Repeat for `plugins\boilerplate-plugin` and `plugins\scf-boilerplate-plugin`, with targets `W:\boilerplate-theme\plugins\boilerplate-plugin` and `W:\boilerplate-theme\plugins\scf-boilerplate-plugin`. `Get-Item <link> | Format-List LinkType, Target` should show `SymbolicLink` and a `W:\...` target.

Mapping only the theme directory is the same pattern: `net use W: "\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project\boilerplate-theme\theme"`, then `$Target = "W:\"`.

#### Repository on a Windows drive (not recommended)

Keep the repository in the WSL filesystem. Only if the mapped drive still leaves `style.css` unreadable, clone onto a Windows path such as `J:\dev\my-project` (in WSL: `/mnt/j/dev/my-project`) and symlink to that Windows path (`J:\dev\...`). Tooling still runs via Remote – WSL. This is a last resort.

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

1. Install Local WP on Windows, outside this repository, and clone [Wordpress-Dev-Blueprint](https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git) into the WSL filesystem (see [WordPress development environment](#wordpress-development-environment)). Open that folder with Remote – WSL and create the theme and plugin symlinks from Windows via a mapped drive letter ([Symlink theme and plugins](#symlink-theme-and-plugins)).
2. Follow the [recommended project setup order](boilerplate-theme/README.md#recommended-project-setup-order) in `boilerplate-theme/README.md` — rename the package folder, install pnpm dependencies, run the rename scripts, install Composer dependencies, build assets, symlink the theme and plugins into WordPress, then copy `cursor/` to `.cursor/`.
