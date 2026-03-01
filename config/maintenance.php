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
    | Middleware Settings
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'cookie_name'     => 'laravel_maintenance',
        'cookie_lifetime' => 43200, // minutes (12 hours)
        'header_name'     => 'X-Maintenance-Token',
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Settings
    |--------------------------------------------------------------------------
    */
    'response' => [
        'status'      => 503,
        'retry_after' => 60,
        'refresh'     => null,
        'view'        => 'maintenance::503',
    ],

];
