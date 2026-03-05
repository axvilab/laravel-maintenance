<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    | The database connection to use for storing maintenance state, IPs and
    | tokens. Set to null to use the application's default connection.
    */
    'connection' => env('MAINTENANCE_DB_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    | Customize the database table names used by the package.
    */
    'tables' => [
        'settings' => 'maintenance_settings',
        'ips' => 'maintenance_ips',
        'tokens' => 'maintenance_tokens',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    | Cache maintenance state to avoid a DB query on every request.
    | Set to null / false to disable caching entirely.
    */
    'cache' => [
        'enabled' => true,
        'store' => null,    // null = default cache store
        'ttl' => 60,        // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Except Paths
    |--------------------------------------------------------------------------
    | URL patterns that should never be blocked by maintenance mode.
    | Supports wildcards: 'api/health', 'webhook/*', 'telescope*'.
    */
    'except' => [
        // 'api/health',
        // 'webhook/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Bypass Route Settings
    |--------------------------------------------------------------------------
    | When enabled, a route is registered at /{prefix}/{token} that allows
    | users to bypass maintenance mode by visiting the URL with a valid token.
    | The token is then stored as a cookie for subsequent requests.
    */
    'bypass_route' => [
        'enabled' => true,
        'prefix' => 'maintenance',  // GET /maintenance/{token}
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Settings
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'cookie_name' => 'laravel_maintenance',
        'cookie_lifetime' => 43200, // minutes (12 hours)
        'header_name' => 'X-Maintenance-Token',
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Settings
    |--------------------------------------------------------------------------
    */
    'response' => [
        'status' => 503,
        'retry_after' => 60,
        'refresh' => null,
        'view' => 'maintenance::503',
    ],

];
