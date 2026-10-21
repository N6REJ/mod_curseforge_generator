# CurseForge Badge & Chart Generator Module

A Joomla 5/6 site module that provides an interactive generator for CurseForge Downloads Action badges and charts. Configure your repository once, then generate ready-to-use Markdown, HTML, or BBCode snippets for badges and download trend charts.

## Purpose

This module lets Joomla site administrators and content editors generate CurseForge badge and chart URLs without leaving the backend. It connects to the [curseforge-downloads-action](https://github.com/N6REJ/curseforge-downloads-action) GitHub Action's output and produces embeddable badges (via Shields.io) and chart images (via raw.githubusercontent.com).

## Features

- **Interactive form**: Enter owner, repository, and branch to generate badge and chart URLs
- **Badge preview**: Live preview of Shields.io-style download count badges (total, daily, weekly, monthly)
- **Chart preview**: Live preview of download trend charts (daily, weekly, monthly, total)
- **Multiple output formats**: Copy generated code as Markdown, HTML, or BBCode
- **Configurable defaults**: Module parameters set default owner, repository, and branch values
- **Joomla 5/6 compatible**: Uses WebAssetManager for proper CSS/JS loading
- **Zero 3rd-party Joomla extensions required**: Works as a standalone module
- **Auto-updates**: Registered with JED update server for seamless version updates

## How it works

- The module renders a form with fields for GitHub owner, repository name, and branch.
- When the user submits or changes values, JavaScript constructs the raw GitHub URLs for badges and charts.
- Badge URLs point to Shields.io dynamic JSON badges that read download data from the repository.
- Chart URLs point to SVG chart files stored in the repository's stats folder.
- The generated code snippets can be copied and pasted into any Joomla article, forum post, or documentation.

## Installation

### Via Joomla Extension Manager (Recommended)

1. Download the latest ZIP package from the [Releases](https://github.com/Bearsampp/mod_cfdg_generator/releases) page.
2. In Joomla Admin → System → Install → Extensions → Upload Package File and upload the ZIP.
3. Go to Content → Site Modules → + New → "CurseForge Badge & Chart Generator", publish to a position, assign to menu items, and Save.

### Manual Installation

1. Clone or download this repository.
2. ZIP the `mod_cfdg_generator` folder contents (the ZIP root must contain `mod_cfdg_generator.xml`, `mod_cfdg_generator.php`, `helper.php`, `tmpl/`, `media/`, `language/`).
3. Upload the ZIP via Joomla's Extension Manager.
4. Publish the module to a position and assign it to the desired menu items.

## Configuration

| Field | Default | Description |
|---|---|---|
| Default Owner | `N6REJ` | GitHub owner (user or organization) that hosts the repository with CurseForge stats. |
| Default Repository | `curseforge-downloads-action` | Name of the repository containing the stats data. |
| Default Branch | `stats-data` | Git branch where the stats folder with badges and charts is stored. |

Advanced: Module Class Suffix, Alternate Layout.

## Development & Testing

1. Edit files in the module directory.
2. Test locally in a Joomla 5/6 site by copying the folder to `modules/mod_cfdg_generator/` or zipping and reinstalling.
3. Verify the form renders, badge/chart previews load, and copy buttons work.
4. Bump `<version>` in `mod_cfdg_generator.xml` when preparing a new release.
5. Run the **Package and Release Module** workflow manually to create a distributable ZIP.

## Packaging

This module uses the [joomla-packager](https://github.com/N6REJ/joomla-packager) GitHub Action to automate building release packages. Trigger the `.github/workflows/package-packager.yml` workflow manually to create a new release ZIP with updated `updates.xml` and SHA-256 checksums.

## License

MIT License. See [LICENSE](LICENSE) for details.
