# CurseForge Badge & Chart Generator Module

A Joomla 5/6 site module that provides an interactive **Generator Lab** for CurseForge Downloads Action badges and charts. Designed to match the look, feel, and behavior of the [GitHub Downloads Action Generator Lab](https://www.justagwas.com/projects/gda/). Uses AJAX for server-side badge and chart generation with live color pickers.

**[Try the live demo](https://hallhome.us/curseforge/dashboard-generator)**

## Purpose

This module lets visitors generate CurseForge badge and chart URLs directly on your Joomla site. It connects to the [mod_curseforge_generator](https://github.com/N6REJ/mod_curseforge_generator) GitHub Action's output and produces embeddable badges (via Shields.io) and chart images (via raw.githubusercontent.com). The UI mirrors the GDA Generator Lab with Basic/Advanced tabs, live previews, and color pickers.

## Features

- **GDA-style Generator Lab UI**: Tabbed interface with Basic and Advanced panels matching the justagwas.com GDA design
- **AJAX-based generation**: All badge and chart URL construction happens server-side via AJAX
- **Color pickers**: Native HTML5 color picker fields for badge and chart colors (Joomla color picker style)
- **Badge preview**: Live preview of Shields.io-style download count badges (total, daily, weekly, monthly, yearly)
- **Chart preview**: Live preview of download trend charts with theme selection
- **Toggle switch**: Primary/matrix chart mode toggle
- **Copy buttons**: One-click copy for Markdown and URL output
- **Configurable defaults**: Module parameters set default owner, repository, and branch values
- **Joomla 5/6 compatible**: Uses WebAssetManager for proper CSS/JS loading
- **Zero 3rd-party Joomla extensions required**: Works as a standalone module
- **Auto-updates**: Registered with update server for seamless version updates

## How it works

- The module renders a Generator Lab form with Basic/Advanced tabs.
- Basic tab shows Owner and Repository fields. Advanced tab adds Branch, JSON path, and chart paths.
- When the user changes any field, a debounced AJAX request is sent to the server.
- The server-side helper sanitizes input, constructs badge and chart URLs, and returns JSON.
- The JavaScript updates the preview images, Markdown output, and URL display.
- Badge URLs point to Shields.io dynamic JSON badges that read download data from the repository.
- Chart URLs point to SVG chart files stored in the repository's stats folder.
- Colors are selected via native color picker inputs synced with hex text fields.

## Installation

### Via Joomla Extension Manager (Recommended)

1. Download the latest ZIP package from the [Releases](https://github.com/N6REJ/mod_curseforge_generator/releases) page.
2. In Joomla Admin → System → Install → Extensions → Upload Package File and upload the ZIP.
3. Go to Content → Site Modules → + New → "CurseForge Badge & Chart Generator", publish to a position, assign to menu items, and Save.

### Manual Installation

1. Clone or download this repository.
2. ZIP the `mod_curseforge_generator` folder contents (the ZIP root must contain `mod_curseforge_generator.xml`, `mod_curseforge_generator.php`, `helper.php`, `tmpl/`, `media/`, `language/`).
3. Upload the ZIP via Joomla's Extension Manager.
4. Publish the module to a position and assign it to the desired menu items.

## Configuration

| Field | Default | Description |
|---|---|---|
| Default Owner | `N6REJ` | GitHub owner (user or organization) that hosts the repository with CurseForge stats. |
| Default Repository | `mod_curseforge_generator` | Name of the repository containing the stats data. |
| Default Branch | `main` | Git branch where the stats folder with badges and charts is stored. |

Advanced: Module Class Suffix, Alternate Layout.

## UI Components

- **Basic tab**: Owner and Repository fields for quick setup
- **Advanced tab**: Branch, JSON path, primary chart path, and chart matrix directory
- **Generate badge section**: Metric selector, label input, and color picker with live preview
- **Generate chart section**: Chart type, theme, color picker, and primary/matrix toggle with live preview
- **Output area**: Displays Badge URL, Markdown, and copy buttons for each

## Development & Testing

1. Edit files in the module directory.
2. Test locally in a Joomla 5/6 site by copying the folder to `modules/mod_curseforge_generator/` or zipping and reinstalling.
3. Verify the form renders, AJAX generation works, badge/chart previews load, and copy buttons work.
4. Bump `<version>` in `mod_curseforge_generator.xml` when preparing a new release.
5. Run the **Package and Release Module** workflow manually to create a distributable ZIP.

## File Structure

```
mod_curseforge_generator/
├── mod_curseforge_generator.php    # Entry point + AJAX routing
├── mod_curseforge_generator.xml    # Manifest
├── helper.php                # Helper with AJAX endpoint & sanitization
├── tmpl/
│   └── default.php           # GDA-style Generator Lab template
├── media/
│   └── js/
│       └── curseforge-generator.js # AJAX-based generator script
├── language/
│   └── en-GB/
│       ├── mod_curseforge_generator.ini
│       └── mod_curseforge_generator.sys.ini
└── README.md
```

## Packaging

This module uses the [joomla-packager](https://github.com/N6REJ/joomla-packager) GitHub Action to automate building release packages. Trigger the `.github/workflows/package-packager.yml` workflow manually to create a new release ZIP with updated `updates.xml` and SHA-256 checksums.

## License

GNU General Public License v3.0 only. See [LICENSE](LICENSE) for details.
