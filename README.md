# axvi/laravel-maintenance

> Advanced maintenance mode management for Laravel 10, 11 and 12.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/axvi/laravel-maintenance.svg?style=flat-square)](https://packagist.org/packages/axvi/laravel-maintenance)
[![License](https://img.shields.io/github/license/axvilab/laravel-maintenance.svg?style=flat-square)](LICENSE.md)

## Features

- ✅ IP address whitelisting (removed from Laravel core since v9)
- ✅ Multiple named bypass tokens (cookie-based for web, header-based for APIs)
- ✅ Scheduled maintenance windows with auto-disable
- ✅ Beautiful, customizable 503 page with countdown timer
- ✅ Database-driven — works seamlessly across multiple servers
- ✅ Artisan commands for full CLI management
- ✅ Events: `MaintenanceModeEnabled`, `MaintenanceModeDisabled`, `MaintenanceBypassGranted`

> **Admin UI packages** coming soon:
>
> - `axvi/laravel-maintenance-nova` — Laravel Nova integration
> - `axvi/laravel-maintenance-filament` — Filament integration

## Requirements

- PHP 8.1+
- Laravel 10 / 11 / 12

## Installation

```bash
composer require axvi/laravel-maintenance
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag=maintenance-migrations
php artisan migrate
```

Optionally publish the config:

```bash
php artisan vendor:publish --tag=maintenance-config
```

## Usage

### Enable maintenance mode

```bash
# Basic
php artisan maintenance:down

# With message, IP whitelist and secret token
php artisan maintenance:down \
  --message="We'll be back in 30 minutes" \
  --allow=192.168.1.1 \
  --secret=my-secret-token \
  --ends-at="2025-01-01 03:00:00"
```

### Disable maintenance mode

```bash
php artisan maintenance:up
```

### Check status

```bash
php artisan maintenance:status
```

### Manage IPs at runtime

```bash
php artisan maintenance:ip add 192.168.1.50 --label="Dev machine"
php artisan maintenance:ip remove 192.168.1.50
php artisan maintenance:ip list
```

### Manage tokens at runtime

```bash
php artisan maintenance:token add dev-token abc123
php artisan maintenance:token revoke dev-token
php artisan maintenance:token list
```

### Bypass via URL (web)

Visit `https://yourapp.com/{secret-token}` — receives a bypass cookie valid for 12 hours.

### Bypass via header (API)

```
X-Maintenance-Token: my-secret-token
```

## License

MIT — see [LICENSE.md](LICENSE.md).
