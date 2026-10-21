# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2026.10.21.6] - 2026-10-21

### Fixed

* Fixed Bootstrap 5 loading by using Web Asset Manager for all assets instead of mixing `usePreset('bootstrap')` with direct `$doc->addStyleSheet()` and `$doc->addScript()` calls
* Module CSS and JS now properly depend on Bootstrap assets, ensuring correct loading order

## [2026.10.21.5] - 2026-10-21

### Added

* Add error handling for Web Asset registry loading with manual fallback ([39d983b](https://github.com/N6REJ/mod_curseforge_generator/commit/39d983b))

### Changed

* Update version to 2026.10.21.3 [skip ci] ([b3a056d](https://github.com/N6REJ/mod_curseforge_generator/commit/b3a056d))
* Merge remote-tracking branch 'origin/main' ([1c2a521](https://github.com/N6REJ/mod_curseforge_generator/commit/1c2a521))
* Update version to 2026.10.21.4 [skip ci] ([d7cc5b7](https://github.com/N6REJ/mod_curseforge_generator/commit/d7cc5b7))

### Fixed

* Fix version number to 2026.10.21.5 and add Web Asset fallback ([70de62b](https://github.com/N6REJ/mod_curseforge_generator/commit/70de62b))

