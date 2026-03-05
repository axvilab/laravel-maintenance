# axvi/laravel-maintenance

> Advanced maintenance mode management for Laravel 10, 11 and 12.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/axvi/laravel-maintenance.svg?style=flat-square)](https://packagist.org/packages/axvi/laravel-maintenance)
[![License](https://img.shields.io/github/license/axvilab/laravel-maintenance.svg?style=flat-square)](LICENSE.md)

## Features

- IP address whitelisting with optional expiration
- Multiple named bypass tokens (cookie for web, header for APIs, URL bypass)
- Scheduled maintenance windows with auto-disable
- Beautiful, customizable 503 page with countdown timer
- Database-driven — works seamlessly across multiple servers
- Built-in cache layer — avoids DB queries on every request
- Exclude paths from maintenance (health checks, webhooks, etc.)
- Configurable database connection and table names
- Artisan commands for full CLI management
- Events: `MaintenanceModeEnabled`, `MaintenanceModeDisabled`, `MaintenanceBypassGranted`
- Automatically replaces Laravel's built-in maintenance middleware

## Requirements

|                | PHP 8.1 | PHP 8.2 | PHP 8.3 | PHP 8.4 | PHP 8.5 |
|----------------|---------|---------|---------|---------|---------|
| **Laravel 10** | yes     | yes     | yes     | yes     | —       |
| **Laravel 11** | —       | yes     | yes     | yes     | yes     |
| **Laravel 12** | —       | yes     | yes     | yes     | yes     |

## Installation

```bash
composer require axvi/laravel-maintenance
```

Run migrations:

```bash
php artisan migrate
```

Migrations are loaded automatically. If you need to customize them:

```bash
php artisan vendor:publish --tag=maintenance-migrations
```

Optionally publish the config:

```bash
php artisan vendor:publish --tag=maintenance-config
```

Optionally publish the 503 view:

```bash
php artisan vendor:publish --tag=maintenance-views
```

## Configuration

After publishing, the config file is located at `config/maintenance.php`:

```php
return [
    // Database connection (null = default)
    'connection' => env('MAINTENANCE_DB_CONNECTION', null),

    // Customize table names
    'tables' => [
        'settings' => 'maintenance_settings',
        'ips'      => 'maintenance_ips',
        'tokens'   => 'maintenance_tokens',
    ],

    // Cache maintenance state to avoid DB queries on every request
    'cache' => [
        'enabled' => true,
        'store'   => null,  // null = default cache store
        'ttl'     => 60,    // seconds
    ],

    // URL patterns that should never be blocked by maintenance mode
    'except' => [
        // 'api/health',
        // 'webhook/*',
    ],

    // Bypass route settings
    'bypass_route' => [
        'enabled' => true,
        'prefix'  => 'maintenance',  // GET /maintenance/{token}
    ],

    // Middleware settings
    'middleware' => [
        'cookie_name'     => 'laravel_maintenance',
        'cookie_lifetime' => 43200, // minutes (12 hours)
        'header_name'     => 'X-Maintenance-Token',
    ],

    // Response settings
    'response' => [
        'status'      => 503,
        'retry_after' => 60,
        'refresh'     => null,
        'view'        => 'maintenance::503',
    ],
];
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

### Manage IPs

```bash
php artisan maintenance:ip add 192.168.1.50 --label="Dev machine"
php artisan maintenance:ip add 10.0.0.1 --expires-at="2025-06-01 00:00:00"
php artisan maintenance:ip remove 192.168.1.50
php artisan maintenance:ip list
```

### Manage tokens

```bash
# Add with auto-generated UUID token
php artisan maintenance:token add dev-token

# Add with explicit token value
php artisan maintenance:token add dev-token abc123

# Add with expiration
php artisan maintenance:token add temp-token --expires-at="2025-06-01 00:00:00"

php artisan maintenance:token revoke dev-token
php artisan maintenance:token list
```

### Bypass methods

**URL bypass (web)**

Visit `https://yourapp.com/maintenance/{secret-token}` — sets a bypass cookie valid for 12 hours.

The route prefix is configurable via `bypass_route.prefix` in the config. You can also disable URL bypass entirely by setting `bypass_route.enabled` to `false`.

**Header bypass (API)**

```
X-Maintenance-Token: my-secret-token
```

The header name is configurable via `middleware.header_name` in the config.

### Exclude paths

Some URLs should always be accessible, even during maintenance. Add patterns to the `except` array:

```php
'except' => [
    'api/health',
    'webhook/*',
    'telescope*',
],
```

Supports wildcards via Laravel's `Request::is()`.

### Caching

By default, the package caches the maintenance state and IP whitelist to avoid database queries on every HTTP request. The cache is automatically invalidated when you enable/disable maintenance or modify the IP whitelist.

To disable caching:

```php
'cache' => [
    'enabled' => false,
],
```

To use a specific cache store (e.g. Redis):

```php
'cache' => [
    'enabled' => true,
    'store'   => 'redis',
    'ttl'     => 30,
],
```

### Programmatic usage

```php
use Axvi\Maintenance\Facades\Maintenance;

// Check status
Maintenance::isDown();

// Enable / disable
Maintenance::enable([
    'message'     => 'Deploying v2.0',
    'retry_after' => 120,
    'ends_at'     => '2025-01-01 03:00:00',
]);
Maintenance::disable();

// Manage IPs
Maintenance::addIp('192.168.1.1', 'Office', now()->addHours(6));
Maintenance::removeIp('192.168.1.1');

// Manage tokens
Maintenance::addToken('deploy', 'my-secret', now()->addDay());
Maintenance::revokeToken('deploy');

// Full status array
$status = Maintenance::getStatus();

// Flush cache manually
Maintenance::flushCache();
```

### Events

| Event                       | Properties                                               |
|-----------------------------|----------------------------------------------------------|
| `MaintenanceModeEnabled`    | `string $message`, `?string $endsAt`                     |
| `MaintenanceModeDisabled`   | —                                                        |
| `MaintenanceBypassGranted`  | `string $type` (ip/cookie/header/url), `string $value`   |

```php
use Axvi\Maintenance\Events\MaintenanceModeEnabled;

Event::listen(MaintenanceModeEnabled::class, function ($event) {
    // Notify the team via Slack, etc.
});
```

## How it works

This package replaces Laravel's built-in `PreventRequestsDuringMaintenance` middleware with its own `CheckMaintenanceMode` middleware. The bypass check order is:

1. **Excluded paths** — URLs matching `except` patterns are always allowed
2. **IP whitelist** — if the request IP is in the `maintenance_ips` table (cached)
3. **Cookie** — if the request has a valid bypass cookie
4. **Header** — if the request has a valid token in the `X-Maintenance-Token` header
5. **503 response** — HTML page with countdown (or JSON for API requests)

All state is stored in the database, so maintenance mode works consistently across multiple application servers. Lookups are cached to minimize performance impact.

## Testing

```bash
composer test
```

Or directly:

```bash
vendor/bin/phpunit
```

## License

MIT — see [LICENSE.md](LICENSE.md).
