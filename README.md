# Boilerplates

This directory contains reusable WordPress development boilerplates. Use `boilerplate-theme/` as a starting point for new theme and plugin projects.

## WordPress development environment

Clone this repository as its own folder. WordPress stays in a separate install. Symlink the theme and the plugins into that install’s `wp-content/themes` and `wp-content/plugins`.

Develop in WSL or on native Linux. On Windows, open this repository in Cursor or VS Code with **Remote – WSL** and run Node, pnpm, Composer, and the other tooling in the Linux shell. Local WP’s PHP runs on Windows, so the symlink target has to be a path Windows can open. Which command creates the link depends on where the repository lives. See [Symlink theme and plugins](#symlink-theme-and-plugins).

Typical WordPress locations:

- **Native Linux** — a normal install path, for example `/var/www/my-site`.
- **Local WP on Windows** — a Windows path, for example `C:\Users\you\Local Sites\my-site\app\public` or `J:\Local Sites\my-site\app\public`. From WSL that folder is under `/mnt/c/...` or `/mnt/j/...`, but Local does not follow a symlink whose target is a Linux path such as `/home/you/...`.

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

The link name is the slug WordPress loads (`themes/<theme-slug>`, `plugins/<plugin-slug>`). After you rename the package folder or a plugin, use that directory in the target and the same slug as the link name. The link path must not already exist. Quote paths that contain spaces (`Local Sites` usually does).

#### Native Linux WordPress

WordPress and the repository are both on Linux. From the repository root:

```bash
WP_CONTENT="/var/www/my-site/wp-content"

ln -s "$(pwd)/boilerplate-theme/theme" "$WP_CONTENT/themes/boilerplate-theme"
ln -s "$(pwd)/boilerplate-theme/plugins/boilerplate-plugin" "$WP_CONTENT/plugins/boilerplate-plugin"
ln -s "$(pwd)/boilerplate-theme/plugins/scf-boilerplate-plugin" "$WP_CONTENT/plugins/scf-boilerplate-plugin"
```

#### Local on Windows, repository in WSL

Keep the clone in the WSL filesystem (for example `/home/you/projects/my-project`) and keep editing it with Remote – WSL. Local’s PHP runs on Windows. A symlink created with `ln -s` in that case stores a Linux target such as `/home/you/projects/my-project/boilerplate-theme/theme`. Windows cannot open that path, so the theme and plugins never load.

Create the directory symlink on Windows. The link sits in the Local site. The target is the WSL UNC path `\\wsl.localhost\<Distro>\...`. WSL has to be running, otherwise the UNC path does not resolve. The distro name is what `wsl -l -q` prints (for example `Ubuntu-22.04`). Map `/home/you/projects/my-project` to `\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project`.

PowerShell as Administrator, or a normal PowerShell window when [Developer Mode](https://learn.microsoft.com/windows/apps/get-started/enable-your-device-for-development) is on:

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

A site on `C:` uses the same commands with `C:\Users\you\Local Sites\my-site\app\public\wp-content\...` as `$Link`.

The same links from an Administrator Command Prompt (`mklink /D` also works without elevation when Developer Mode is on). Link first, then target:

```bat
mklink /D "J:\Local Sites\my-site\app\public\wp-content\themes\boilerplate-theme" "\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project\boilerplate-theme\theme"
mklink /D "J:\Local Sites\my-site\app\public\wp-content\plugins\boilerplate-plugin" "\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project\boilerplate-theme\plugins\boilerplate-plugin"
mklink /D "J:\Local Sites\my-site\app\public\wp-content\plugins\scf-boilerplate-plugin" "\\wsl.localhost\Ubuntu-22.04\home\you\projects\my-project\boilerplate-theme\plugins\scf-boilerplate-plugin"
```

Confirm the link from PowerShell. `LinkType` is `SymbolicLink` and `Target` is the UNC path:

```powershell
Get-Item "J:\Local Sites\my-site\app\public\wp-content\themes\boilerplate-theme" | Format-List FullName, LinkType, Target
```

If `\\wsl.localhost\...` does not resolve, check that WSL is running and that the distro name matches `wsl -l -q`. When the UNC path still fails, clone the repository onto a Windows drive and use the next section.

#### Local on Windows, repository on a Windows drive

This is the fallback when the WSL UNC path does not work with Local. Clone onto a Windows path such as `J:\dev\my-project` (in WSL: `/mnt/j/dev/my-project`) or `C:\Users\you\dev\my-project` (in WSL: `/mnt/c/Users/you/dev/my-project`). Day-to-day commands still run in WSL via Remote – WSL. The symlink uses normal Windows paths, because Local’s PHP is Windows:

```powershell
$Link = "J:\Local Sites\my-site\app\public\wp-content\themes\boilerplate-theme"
$Target = "J:\dev\my-project\boilerplate-theme\theme"
New-Item -ItemType SymbolicLink -Path $Link -Target $Target
```

Repeat for each plugin. Set `$Target` to `J:\dev\my-project\boilerplate-theme\plugins\boilerplate-plugin` and `J:\dev\my-project\boilerplate-theme\plugins\scf-boilerplate-plugin`. `mklink /D` takes the same Windows link and target.

`ln -s` from WSL can create that Windows link only when both the link and the target are on the Windows filesystem (`/mnt/c`, `/mnt/j`, and so on) and Windows stores a target Local can open. Afterward, `Get-Item` on the Windows link path must show a Windows target such as `J:\dev\my-project\...`. A target of `/mnt/j/...` or `/home/...` is still a Linux path. Remove that link and create it again with `New-Item` or `mklink /D`.

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

1. Set up WordPress outside this repository (Local WP or a native Linux install) and clone [Wordpress-Dev-Blueprint](https://github.com/jswebschmiede/Wordpress-Dev-Blueprint.git) as its own folder (see [WordPress development environment](#wordpress-development-environment)). On Windows, open that folder with Remote – WSL. When WordPress is Local, create the theme and plugin symlinks from Windows ([Symlink theme and plugins](#symlink-theme-and-plugins)).
2. Follow the [recommended project setup order](boilerplate-theme/README.md#recommended-project-setup-order) in `boilerplate-theme/README.md` — rename the package folder, install pnpm dependencies, run the rename scripts, install Composer dependencies, build assets, symlink the theme and plugins into WordPress, then copy `cursor/` to `.cursor/`.
