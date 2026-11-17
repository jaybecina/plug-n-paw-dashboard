<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo mode
    |--------------------------------------------------------------------------
    |
    | When enabled (the public portfolio deployment), the login page shows the
    | admin credentials and account-changing actions are refused so visitors
    | can't lock each other out. The database is rebuilt on every boot.
    |
    */

    'enabled' => (bool) env('DEMO_MODE', false),

    'admin' => [
        'name' => env('ADMIN_NAME', 'Plug N Paw Admin'),
        'email' => env('ADMIN_EMAIL', 'admin@plugnpaw.test'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],

];
