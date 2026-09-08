# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2026-09-08

### Fixed

- Bypass cookie is now issued with `SameSite=Lax` instead of `Strict`, so it survives
  the redirect back from an external identity provider (Azure AD, Google, OAuth). With
  `Strict` the browser withheld the cookie on that cross-site navigation and users were
  served the 503 page in the middle of signing in.

### Added

- `middleware.cookie_same_site` config option for the bypass cookie's SameSite policy

## [1.0.0] - 2026-03-05

### Added

- Database-driven maintenance mode (replaces Laravel's file-based approach)
- IP whitelisting with optional labels and expiration
- Named bypass tokens with cookie, header, and URL support
- Scheduled maintenance windows with auto-disable (`ends_at`)
- Configurable bypass route prefix (`/maintenance/{token}` by default)
- Configurable database connection for multi-DB setups
- Configurable table names via `maintenance.tables`
- Built-in cache layer for maintenance state and IP whitelist (`maintenance.cache`)
- Exclude paths from maintenance mode via `maintenance.except` (supports wildcards)
- Beautiful 503 page with animated countdown timer
- Laravel 10, 11, and 12 compatibility
- JSON responses for API requests (`Accept: application/json`)
- Artisan commands: `maintenance:down`, `maintenance:up`, `maintenance:status`, `maintenance:ip`, `maintenance:token`
- Events: `MaintenanceModeEnabled`, `MaintenanceModeDisabled`, `MaintenanceBypassGranted`
- `Maintenance` facade for programmatic access
- Auto-loading migrations (no publish required)
- Publishable config, migrations, and views