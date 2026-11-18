<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo mode
    |--------------------------------------------------------------------------
    |
    | When enabled (the public portfolio deployment), account-changing actions
    | are refused so visitors can't lock each other out, and the database is
    | rebuilt on every boot. The login page never shows the credentials.
    |
    */

    'enabled' => (bool) env('DEMO_MODE', false),

    'admin' => [
        'name' => env('ADMIN_NAME', 'Plug N Paw Admin'),
        'email' => env('ADMIN_EMAIL', 'admin@admin.com'),
        'password' => env('ADMIN_PASSWORD', 'password123'),
    ],

];
