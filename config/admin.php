<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bootstrap Admin
    |--------------------------------------------------------------------------
    |
    | These values are only used to create the initial Admin user when no
    | user with the Admin role exists yet. After that, the database is the
    | source of truth and changes to these values have no effect.
    |
    */

    'name' => env('ADMIN_NAME', 'Administrador'),
    'email' => env('ADMIN_EMAIL', 'admin@cifo.com'),
    'password' => env('ADMIN_PASSWORD', 'admin123'),

];
