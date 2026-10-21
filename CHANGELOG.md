# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2026.10.21.7] - 2026-10-21

### Fixed

* Fixed Bootstrap 5 loading on live site by replacing Web Asset Manager `registerAndUse*()` calls with direct document asset loading using explicit media folder URLs
* Module CSS and JS are now loaded via `$doc->addStyleSheet()` and `$doc->addScript()` after Bootstrap preset is loaded

## [2026.10.21.6] - 2026-10-21

### Changed

* Update version to 2026.10.21.4 [skip ci] ([d7cc5b7](https://github.com/N6REJ/mod_curseforge_generator/commit/d7cc5b7))
* Update version to 2026.10.21.5 [skip ci] ([c68e66c](https://github.com/N6REJ/mod_curseforge_generator/commit/c68e66c))
* Refactor asset loading to use Web Asset Manager exclusively with proper Bootstrap dependencies ([4c10513](https://github.com/N6REJ/mod_curseforge_generator/commit/4c10513))

### Fixed

* Fix version number to 2026.10.21.5 and add Web Asset fallback ([70de62b](https://github.com/N6REJ/mod_curseforge_generator/commit/70de62b))

