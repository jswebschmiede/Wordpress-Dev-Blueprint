# Boilerplates

This directory contains reusable WordPress development boilerplates. Use `boilerplate-theme/` as a starting point for new theme and plugin projects.

## WordPress development environment

Clone this repository as its own folder. WordPress stays in a separate **Local WP** install on Windows. Symlink the theme and the plugins into that install’s `wp-content/themes` and `wp-content/plugins`.

Develop in WSL. Open the repository in Cursor or VS Code with **Remote – WSL** and run Node, pnpm, Composer, and the other tooling in the Linux shell. Local’s PHP runs on Windows, so create the symlinks on Windows. See [Symlink theme and plugins](#symlink-theme-and-plugins).

```text
~/projects/my-project/                          this repository (in WSL)
├── .vscode/settings.json
├── boilerplate-theme/
├── cursor/
└── README.md

J:\Local Sites\my-site\app\public\              Local WP (Windows)
├── wp-admin/
├── wp-content/
│   ├── themes/boilerplate-theme                symlink → repository theme/
│   └── plugins/                                symlinks → repository plugins/
└── wp-config.php
```

A site on `C:` uses `C:\Users\you\Local Sites\my-site\app\public`. From WSL that folder is `/mnt/j/...` or `/mnt/c/...`.

```bash
git clone https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git my-project
cd my-project
rm -rf .git
git init
```

Remove `.git` and run `git init` only when starting a new project from the blueprint. If you keep the upstream history, skip that step.

### Symlink theme and plugins

The link name is the slug WordPress loads (`themes/<theme-slug>`, `plugins/<plugin-slug>`). After a rename, the target uses the new directory and the link name is that slug. The link path must not already exist. Quote paths that contain spaces (`Local Sites` usually does).

#### Repository in WSL

Keep the clone in the WSL filesystem (`/home/you/projects/my-project`) and edit it with Remote – WSL. The symlink target is `\\wsl.localhost\<Distro>\...`. WSL must be running, or that UNC path does not resolve. The distro name is what `wsl -l -q` prints (for example `Ubuntu-22.04`): `/home/you/projects/my-project` is `\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project`.

Do not `ln -s` from WSL into the Local site. That stores a Linux target such as `/home/you/...`, which Local’s PHP cannot open.

PowerShell as Administrator, or a normal window when [Developer Mode](https://learn.microsoft.com/windows/apps/get-started/enable-your-device-for-development) is on. A site on `C:` uses `C:\Users\you\Local Sites\...` as `$Link`:

```powershell
$Link = "J:\Local Sites\my-site\app\public\wp-content\themes\boilerplate-theme"
$Target = "\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project\boilerplate-theme\theme"
New-Item -ItemType SymbolicLink -Path $Link -Target $Target

$Link = "J:\Local Sites\my-site\app\public\wp-content\plugins\boilerplate-plugin"
$Target = "\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project\boilerplate-theme\plugins\boilerplate-plugin"
New-Item -ItemType SymbolicLink -Path $Link -Target $Target

$Link = "J:\Local Sites\my-site\app\public\wp-content\plugins\scf-boilerplate-plugin"
$Target = "\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project\boilerplate-theme\plugins\scf-boilerplate-plugin"
New-Item -ItemType SymbolicLink -Path $Link -Target $Target
```

The same link with `mklink /D` (link first, then target; Administrator, or Developer Mode):

```bat
mklink /D "J:\Local Sites\my-site\app\public\wp-content\themes\boilerplate-theme" "\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project\boilerplate-theme\theme"
```

Repeat for each plugin. `Get-Item <link> | Format-List LinkType, Target` should show `SymbolicLink` and the UNC path. If `\\wsl.localhost\...` still fails, check that WSL is running and the distro name matches `wsl -l -q`, then use the next section.

#### Repository on a Windows drive

Fallback when the UNC path does not work. Clone onto a Windows path such as `J:\dev\my-project` (in WSL: `/mnt/j/dev/my-project`). Commands still run in WSL via Remote – WSL. The target is that Windows path, not `/mnt/j/...` or `/home/...`:

```powershell
$Link = "J:\Local Sites\my-site\app\public\wp-content\themes\boilerplate-theme"
$Target = "J:\dev\my-project\boilerplate-theme\theme"
New-Item -ItemType SymbolicLink -Path $Link -Target $Target
```

Repeat for `plugins\boilerplate-plugin` and `plugins\scf-boilerplate-plugin`. `mklink /D` takes the same Windows link and target. `Get-Item` must show a Windows target.

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

1. Install Local WP on Windows, outside this repository, and clone [Wordpress-Dev-Blueprint](https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git) as its own folder (see [WordPress development environment](#wordpress-development-environment)). Open that folder with Remote – WSL and create the theme and plugin symlinks from Windows ([Symlink theme and plugins](#symlink-theme-and-plugins)).
2. Follow the [recommended project setup order](boilerplate-theme/README.md#recommended-project-setup-order) in `boilerplate-theme/README.md` — rename the package folder, install pnpm dependencies, run the rename scripts, install Composer dependencies, build assets, symlink the theme and plugins into WordPress, then copy `cursor/` to `.cursor/`.
