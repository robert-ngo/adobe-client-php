# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Comprehensive Segmentation Service API support
  - `AudiencesClient` for audience management
  - `ExportJobsClient` for export job operations
  - `SegmentDefinitionsClient` for segment definition management
  - `SegmentJobsClient` for segment job processing
- GitHub Actions CI/CD pipeline for automated testing
- Psalm static analysis configuration
- Makefile with 20+ developer commands
- Contributing guidelines (CONTRIBUTING.md)
- Security policy (SECURITY.md)
- Changelog (CHANGELOG.md)
- GitHub issue and PR templates
- Comprehensive project documentation (CLAUDE.md, IMPROVEMENTS.md)

### Changed
- **BREAKING**: Guzzle and PSR implementations now bundled as required dependencies
  - Simplifies installation to single `composer require` command
  - No longer need to manually install `guzzlehttp/psr7` and `http-interop/http-factory-guzzle`
  - Still supports alternative PSR-18 clients for advanced users
- Refactored monolithic SegmentationClient into entity-focused clients
- Improved test coverage across all clients (52 tests, 176 assertions)
- Updated documentation with simplified installation steps

### Fixed
- URL encoding for IDs with special characters
- Error handling consistency across all clients
- Inconsistent dependency configuration in composer.json

## [0.1.0] - 2025-01-10

### Added
- Initial release
- PSR-compliant SDK architecture
- Core HTTP client with PSR-18/PSR-17 support
- Pluggable authentication system
- AEM Sites support
  - SitesClient for page operations
  - ContentFragmentsClient for content fragment management
- AEM Assets support
  - AssetsClient for digital asset management
- Bearer token authentication provider
- Comprehensive unit test suite
- DDEV development environment configuration

### Core Features
- Strict typing throughout the codebase
- PSR-4 autoloading
- PSR-7 HTTP message interfaces
- PSR-12 coding standards
- Dependency injection architecture
- Comprehensive error handling with ApiException

[Unreleased]: https://github.com/robert-ngo/adobe-client-php/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/robert-ngo/adobe-client-php/releases/tag/v0.1.0
