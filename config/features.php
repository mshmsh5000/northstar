<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | This file is a custom addition to Northstar for storing feature flags, so
    | features can be conditionally toggled on and off per environment.
    |
    */

    'blink' => env('DS_ENABLE_BLINK'),

    'blink-updates' => env('DS_ENABLE_BLINK_UPDATES'),

    'password-grant' => env('DS_ENABLE_PASSWORD_GRANT', true),

    'rate-limiting' => env('DS_ENABLE_RATE_LIMITING'),

];
