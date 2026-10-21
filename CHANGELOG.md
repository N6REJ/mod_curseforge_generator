# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2026.10.21] - 2026-10-21

### Fixed

* Fixed Bootstrap 5 loading on live site by replacing Web Asset Manager `registerAndUse*()` calls with direct document asset loading using explicit media folder URLs
* Module CSS and JS are now loaded via `$doc->addStyleSheet()` and `$doc->addScript()` after Bootstrap preset is loaded

